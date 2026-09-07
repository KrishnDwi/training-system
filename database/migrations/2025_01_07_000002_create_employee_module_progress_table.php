<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SATU baris = progres satu karyawan pada satu modul training (alur
     * pre-test -> baca materi -> post-test). Dibuat sebagai 1 tabel progres
     * (bukan tabel "attempts" terpisah) supaya query status gampang & cepat —
     * kalau post-test diulang (tidak lulus), field posttest_* di baris yang
     * SAMA ditimpa (bukan bikin baris baru), karena requirement-nya tidak
     * minta riwayat semua percobaan, cukup status/skor terkini.
     */
    public function up(): void
    {
        Schema::create('employee_module_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('training_module_id')->constrained('training_modules')->cascadeOnDelete();

            $table->unsignedTinyInteger('pretest_score')->nullable();
            $table->json('pretest_answers')->nullable();
            $table->timestamp('pretest_completed_at')->nullable();

            $table->timestamp('material_confirmed_at')->nullable();

            $table->unsignedTinyInteger('posttest_score')->nullable();
            $table->json('posttest_answers')->nullable();
            $table->boolean('posttest_passed')->nullable();
            $table->timestamp('posttest_completed_at')->nullable();

            $table->timestamps();

            $table->unique(['employee_id', 'training_module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_module_progress');
    }
};
