<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat SATU percobaan post-test. Bersifat immutable — setelah dibuat,
 * tidak pernah di-update atau dihapus, supaya riwayat percobaan lama tetap
 * utuh saat karyawan mengulang post-test.
 */
class EmployeePosttestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'training_module_id',
        'attempt_number',
        'score',
        'passed',
        'answers',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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
     * Durasi pengerjaan percobaan ini, format singkat (mis. "3 menit 12 detik").
     */
    public function getDurationAttribute(): ?string
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        $seconds = $this->started_at->diffInSeconds($this->completed_at);
        $minutes = intdiv($seconds, 60);
        $remainingSeconds = $seconds % 60;

        return $minutes === 0
            ? "{$remainingSeconds} detik"
            : "{$minutes} menit {$remainingSeconds} detik";
    }
}
