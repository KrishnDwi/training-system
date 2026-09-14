<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_module_progress', function (Blueprint $table) {
            // Waktu SELESAI (pretest_completed_at, posttest_completed_at) sudah
            // ada sejak awal — kolom baru ini menandai waktu MULAI, supaya durasi
            // pengerjaan bisa dihitung (selesai - mulai).
            $table->timestamp('pretest_started_at')->nullable()->after('training_module_id');
            $table->timestamp('posttest_started_at')->nullable()->after('material_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('employee_module_progress', function (Blueprint $table) {
            $table->dropColumn(['pretest_started_at', 'posttest_started_at']);
        });
    }
};
