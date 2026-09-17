<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\EmployeeModuleProgress;
use App\Models\EmployeePosttestAttempt;
use App\Models\TrainingHistory;
use App\Models\TrainingModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PortalTestController extends Controller
{
    /**
     * Tampilkan tahap yang sesuai untuk karyawan ini pada modul tsb —
     * pretest / material / posttest / completed. Progress row dibuat
     * otomatis (firstOrCreate) saat pertama kali dibuka.
     */
    public function show(TrainingModule $trainingModule)
    {
        $employee = Auth::guard('employee')->user();

        abort_unless($trainingModule->questions->count() > 0, 404, 'Modul ini tidak memakai alur test.');

        $progress = EmployeeModuleProgress::firstOrCreate([
            'employee_id' => $employee->id,
            'training_module_id' => $trainingModule->id,
        ]);

        // Catat waktu MULAI saat halaman soal dibuka (bukan saat submit) —
        // supaya durasi pengerjaan yang tercatat akurat mencerminkan waktu
        // karyawan benar-benar mengerjakan, bukan cuma waktu klik submit.
        if ($progress->stage === 'pretest' && !$progress->pretest_started_at) {
            $progress->update(['pretest_started_at' => now()]);
        }

        // Untuk post-test: reset waktu mulai setiap kali masuk ulang ke tahap
        // ini SELAMA belum lulus — mencakup percobaan pertama maupun retry
        // setelah gagal, supaya durasi yang tercatat selalu punya acuan yang benar.
        if ($progress->stage === 'posttest' && (!$progress->posttest_started_at || $progress->posttest_completed_at)) {
            $progress->update(['posttest_started_at' => now()]);
        }

        $progress = $progress->fresh();

        // Deadline dikirim ke view sebagai epoch milidetik (dipakai JS
        // countdown) — null kalau modul ini tidak diberi batas waktu.
        $deadlineAt = match ($progress->stage) {
            'pretest' => $trainingModule->pretest_time_limit_minutes
                ? $progress->pretest_started_at->copy()->addMinutes($trainingModule->pretest_time_limit_minutes)
                : null,
            'posttest' => $trainingModule->posttest_time_limit_minutes
                ? $progress->posttest_started_at->copy()->addMinutes($trainingModule->posttest_time_limit_minutes)
                : null,
            default => null,
        };
        $deadlineAtMs = $deadlineAt?->getTimestamp() * 1000;

        // Riwayat semua percobaan post-test — ditampilkan ke karyawan supaya
        // mereka bisa melihat progres skor tiap percobaan.
        $posttestAttempts = EmployeePosttestAttempt::where('employee_id', $employee->id)
            ->where('training_module_id', $trainingModule->id)
            ->orderBy('attempt_number')
            ->get();

        return match ($progress->stage) {
            'pretest' => view('portal.test.pretest', compact('trainingModule', 'progress', 'deadlineAtMs')),
            'material' => view('portal.test.material', compact('trainingModule', 'progress')),
            'posttest' => view('portal.test.posttest', compact('trainingModule', 'progress', 'deadlineAtMs', 'posttestAttempts')),
            'completed' => view('portal.test.completed', compact('trainingModule', 'progress', 'posttestAttempts')),
        };
    }

    public function submitPretest(Request $request, TrainingModule $trainingModule)
    {
        $employee = Auth::guard('employee')->user();
        $progress = EmployeeModuleProgress::where('employee_id', $employee->id)
            ->where('training_module_id', $trainingModule->id)
            ->firstOrFail();

        abort_unless($progress->stage === 'pretest', 403);

        [$score, $answers] = $this->scoreAnswers($trainingModule, $request->input('answers', []));

        $progress->update([
            'pretest_score' => $score,
            'pretest_answers' => $answers,
            'pretest_completed_at' => now(),
        ]);

        $duration = $progress->fresh()->pretest_duration;

        return redirect()
            ->route('portal.modules.show', $trainingModule)
            ->with('success', "Pre-test selesai dalam {$duration}. Skor Anda: {$score}. Silakan lanjut membaca materi.");
    }

    public function confirmMaterial(TrainingModule $trainingModule)
    {
        $employee = Auth::guard('employee')->user();
        $progress = EmployeeModuleProgress::where('employee_id', $employee->id)
            ->where('training_module_id', $trainingModule->id)
            ->firstOrFail();

        abort_unless($progress->stage === 'material', 403);

        $progress->update(['material_confirmed_at' => now()]);

        return redirect()
            ->route('portal.modules.show', $trainingModule)
            ->with('success', 'Materi ditandai sudah dibaca. Silakan lanjut ke post-test.');
    }

    public function submitPosttest(Request $request, TrainingModule $trainingModule)
    {
        $employee = Auth::guard('employee')->user();
        $progress = EmployeeModuleProgress::where('employee_id', $employee->id)
            ->where('training_module_id', $trainingModule->id)
            ->firstOrFail();

        abort_unless($progress->stage === 'posttest', 403);

        [$score, $answers] = $this->scoreAnswers($trainingModule, $request->input('answers', []));
        $passed = $score >= $trainingModule->passing_score;
        $completedAt = now();

        // 1. Simpan percobaan ini sebagai record BARU yang permanen —
        //    percobaan sebelumnya TIDAK ditimpa maupun dihapus.
        $attemptNumber = EmployeePosttestAttempt::where('employee_id', $employee->id)
            ->where('training_module_id', $trainingModule->id)
            ->max('attempt_number') + 1;

        EmployeePosttestAttempt::create([
            'employee_id' => $employee->id,
            'training_module_id' => $trainingModule->id,
            'attempt_number' => $attemptNumber,
            'score' => $score,
            'passed' => $passed,
            'answers' => $answers,
            'started_at' => $progress->posttest_started_at,
            'completed_at' => $completedAt,
        ]);

        // 2. Update status TERKINI di tabel progress (dipakai untuk logic
        //    tahap alur/gating) — ini memang sengaja ditimpa tiap percobaan,
        //    karena riwayat lengkapnya sudah aman tersimpan di langkah 1.
        $progress->update([
            'posttest_score' => $score,
            'posttest_answers' => $answers,
            'posttest_passed' => $passed,
            'posttest_completed_at' => $completedAt,
        ]);

        $duration = $progress->fresh()->posttest_duration;

        if ($passed) {
            $this->recordTrainingHistory($employee, $trainingModule);

            return redirect()
                ->route('portal.modules.show', $trainingModule)
                ->with('success', "Selamat! Post-test lulus pada percobaan ke-{$attemptNumber} dalam {$duration} dengan skor {$score}. Training ini sudah tercatat otomatis.");
        }

        return redirect()
            ->route('portal.modules.show', $trainingModule)
            ->with('warning', "Percobaan ke-{$attemptNumber} selesai dalam {$duration} dengan skor {$score}, belum mencapai nilai minimum {$trainingModule->passing_score}. Silakan coba post-test lagi.");
    }

    /**
     * Download sertifikat kelulusan — hanya untuk training yang sudah
     * benar-benar SELESAI (lulus post-test). Diberikan HANYA kalau HR sudah
     * upload Template Sertifikat; kalau belum, karyawan diberi pesan yang
     * jelas (bukan error mentah).
     */
    public function downloadCertificate(TrainingModule $trainingModule)
    {
        $employee = Auth::guard('employee')->user();
        $progress = EmployeeModuleProgress::where('employee_id', $employee->id)
            ->where('training_module_id', $trainingModule->id)
            ->firstOrFail();

        abort_unless($progress->stage === 'completed', 403, 'Anda belum menyelesaikan training ini.');

        $template = CertificateTemplate::current();

        if (!$template) {
            return back()->with('warning', 'Template sertifikat belum tersedia. Hubungi HRD.');
        }

        $pdf = $template->renderPdf([
            'name' => $employee->name,
            'module' => $trainingModule->name,
            'date' => $progress->posttest_completed_at->translatedFormat('d F Y'),
            'score' => (string) $progress->posttest_score,
        ]);

        $fileName = 'Sertifikat - ' . $trainingModule->name . ' - ' . $employee->name . '.pdf';

        return $pdf->download($fileName);
    }

    /**
     * @return array{0: int, 1: array} [skor 0-100, jawaban yang disimpan]
     */
    protected function scoreAnswers(TrainingModule $trainingModule, array $submittedAnswers): array
    {
        $questions = $trainingModule->questions;

        if ($questions->isEmpty()) {
            return [0, []];
        }

        $correctCount = 0;
        $answers = [];

        foreach ($questions as $question) {
            $selected = $submittedAnswers[$question->id] ?? null;
            $answers[$question->id] = $selected;

            if ($selected === $question->correct_option) {
                $correctCount++;
            }
        }

        $score = (int) round(($correctCount / $questions->count()) * 100);

        return [$score, $answers];
    }

    /**
     * Post-test lulus = training dianggap selesai. Otomatis tercatat sebagai
     * TrainingHistory (training_participant_id NULL, sama seperti pola
     * sertifikasi eksternal dari import Excel) — supaya terintegrasi dengan
     * Dashboard/Report/pengingat mandatory training yang sudah ada, tanpa
     * perlu HR bikin Training Session manual untuk training self-paced ini.
     */
    protected function recordTrainingHistory($employee, TrainingModule $trainingModule): void
    {
        $today = Carbon::today();
        $expiredAt = $trainingModule->validity_months
            ? $today->copy()->addMonths($trainingModule->validity_months)
            : null;

        TrainingHistory::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'training_module_id' => $trainingModule->id,
                'training_date' => $today,
            ],
            [
                'training_participant_id' => null,
                'training_code_snapshot' => $trainingModule->code,
                'training_name_snapshot' => $trainingModule->name,
                'is_mandatory_snapshot' => $trainingModule->is_mandatory,
                'trainer_name_snapshot' => 'Self-Paced (Portal Karyawan)',
                'duration_minutes_snapshot' => $trainingModule->standard_duration_minutes,
                'validity_months_snapshot' => $trainingModule->validity_months,
                'expired_at' => $expiredAt,
            ]
        );
    }
}
