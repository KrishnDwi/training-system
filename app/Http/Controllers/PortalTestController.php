<?php

namespace App\Http\Controllers;

use App\Models\EmployeeModuleProgress;
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

        return match ($progress->stage) {
            'pretest' => view('portal.test.pretest', compact('trainingModule', 'progress')),
            'material' => view('portal.test.material', compact('trainingModule', 'progress')),
            'posttest' => view('portal.test.posttest', compact('trainingModule', 'progress')),
            'completed' => view('portal.test.completed', compact('trainingModule', 'progress')),
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

        return redirect()
            ->route('portal.modules.show', $trainingModule)
            ->with('success', "Pre-test selesai. Skor Anda: {$score}. Silakan lanjut membaca materi.");
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

        $progress->update([
            'posttest_score' => $score,
            'posttest_answers' => $answers,
            'posttest_passed' => $passed,
            'posttest_completed_at' => now(),
        ]);

        if ($passed) {
            $this->recordTrainingHistory($employee, $trainingModule);

            return redirect()
                ->route('portal.modules.show', $trainingModule)
                ->with('success', "Selamat! Post-test lulus dengan skor {$score}. Training ini sudah tercatat otomatis.");
        }

        return redirect()
            ->route('portal.modules.show', $trainingModule)
            ->with('warning', "Skor Anda {$score}, belum mencapai nilai minimum {$trainingModule->passing_score}. Silakan coba post-test lagi.");
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
