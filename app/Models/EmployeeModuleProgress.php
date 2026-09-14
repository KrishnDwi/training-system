<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeModuleProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'training_module_id',
        'pretest_score',
        'pretest_answers',
        'pretest_started_at',
        'pretest_completed_at',
        'material_confirmed_at',
        'posttest_score',
        'posttest_answers',
        'posttest_passed',
        'posttest_started_at',
        'posttest_completed_at',
    ];

    protected $casts = [
        'pretest_answers' => 'array',
        'posttest_answers' => 'array',
        'pretest_started_at' => 'datetime',
        'pretest_completed_at' => 'datetime',
        'material_confirmed_at' => 'datetime',
        'posttest_started_at' => 'datetime',
        'posttest_completed_at' => 'datetime',
        'posttest_passed' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function trainingModule(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class);
    }

    /**
     * Tahap alur saat ini: 'pretest' | 'material' | 'posttest' | 'completed'.
     * Post-test yang belum lulus otomatis kembali ke tahap 'posttest' (retry),
     * BUKAN 'completed' — sesuai keputusan: harus lulus nilai minimum dulu.
     */
    public function getStageAttribute(): string
    {
        if (!$this->pretest_completed_at) {
            return 'pretest';
        }

        if (!$this->material_confirmed_at) {
            return 'material';
        }

        if (!$this->posttest_completed_at || !$this->posttest_passed) {
            return 'posttest';
        }

        return 'completed';
    }

    /**
     * Durasi pengerjaan dalam format singkat (mis. "3 menit 12 detik").
     * Dihitung dinamis dari selisih started_at & completed_at — tidak
     * disimpan sebagai angka statis, supaya tidak perlu kolom tambahan.
     */
    public function getPretestDurationAttribute(): ?string
    {
        return $this->formatDuration($this->pretest_started_at, $this->pretest_completed_at);
    }

    public function getPosttestDurationAttribute(): ?string
    {
        return $this->formatDuration($this->posttest_started_at, $this->posttest_completed_at);
    }

    protected function formatDuration($start, $end): ?string
    {
        if (!$start || !$end) {
            return null;
        }

        $seconds = $start->diffInSeconds($end);
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes === 0) {
            return "{$remainingSeconds} detik";
        }

        return "{$minutes} menit {$remainingSeconds} detik";
    }
}
