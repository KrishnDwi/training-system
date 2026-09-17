<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SATU baris = SATU percobaan post-test (immutable — tidak pernah
     * di-update atau dihapus setelah dibuat). Ini melengkapi
     * `employee_module_progress` yang tetap dipertahankan sebagai
     * "status/posisi terkini" (dipakai untuk logic tahap alur & gating),
     * sementara tabel ini adalah sumber kebenaran untuk RIWAYAT LENGKAP
     * semua percobaan — sesuai permintaan Anda supaya percobaan lama
     * tidak hilang/tertimpa saat karyawan mengulang post-test.
     */
    public function up(): void
    {
        Schema::create('employee_posttest_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('training_module_id')->constrained('training_modules')->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_number'); // 1, 2, 3, ... per (employee, module)
            $table->unsignedTinyInteger('score');
            $table->boolean('passed');
            $table->json('answers')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index(['employee_id', 'training_module_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_posttest_attempts');
    }
};
