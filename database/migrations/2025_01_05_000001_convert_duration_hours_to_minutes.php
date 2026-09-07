<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom baru (menit)
        Schema::table('training_modules', function (Blueprint $table) {
            $table->unsignedInteger('standard_duration_minutes')->nullable()->after('standard_duration_hours');
        });
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->unsignedInteger('actual_duration_minutes')->nullable()->after('actual_duration_hours');
        });
        Schema::table('training_histories', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes_snapshot')->nullable()->after('duration_hours_snapshot');
        });

        // 2. Backfill: konversi nilai lama (jam) ke menit, dilakukan di PHP
        //    (bukan raw SQL "col * 60") supaya portable di semua driver database.
        foreach (DB::table('training_modules')->whereNotNull('standard_duration_hours')->cursor() as $row) {
            DB::table('training_modules')->where('id', $row->id)->update([
                'standard_duration_minutes' => (int) round($row->standard_duration_hours * 60),
            ]);
        }
        foreach (DB::table('training_sessions')->whereNotNull('actual_duration_hours')->cursor() as $row) {
            DB::table('training_sessions')->where('id', $row->id)->update([
                'actual_duration_minutes' => (int) round($row->actual_duration_hours * 60),
            ]);
        }
        foreach (DB::table('training_histories')->whereNotNull('duration_hours_snapshot')->cursor() as $row) {
            DB::table('training_histories')->where('id', $row->id)->update([
                'duration_minutes_snapshot' => (int) round($row->duration_hours_snapshot * 60),
            ]);
        }

        // 3. Hapus kolom lama (jam)
        Schema::table('training_modules', function (Blueprint $table) {
            $table->dropColumn('standard_duration_hours');
        });
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('actual_duration_hours');
        });
        Schema::table('training_histories', function (Blueprint $table) {
            $table->dropColumn('duration_hours_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->decimal('standard_duration_hours', 4, 1)->nullable();
        });
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->decimal('actual_duration_hours', 4, 1)->nullable();
        });
        Schema::table('training_histories', function (Blueprint $table) {
            $table->decimal('duration_hours_snapshot', 4, 1)->nullable();
        });

        foreach (DB::table('training_modules')->whereNotNull('standard_duration_minutes')->cursor() as $row) {
            DB::table('training_modules')->where('id', $row->id)->update([
                'standard_duration_hours' => round($row->standard_duration_minutes / 60, 1),
            ]);
        }
        foreach (DB::table('training_sessions')->whereNotNull('actual_duration_minutes')->cursor() as $row) {
            DB::table('training_sessions')->where('id', $row->id)->update([
                'actual_duration_hours' => round($row->actual_duration_minutes / 60, 1),
            ]);
        }
        foreach (DB::table('training_histories')->whereNotNull('duration_minutes_snapshot')->cursor() as $row) {
            DB::table('training_histories')->where('id', $row->id)->update([
                'duration_hours_snapshot' => round($row->duration_minutes_snapshot / 60, 1),
            ]);
        }

        Schema::table('training_modules', function (Blueprint $table) {
            $table->dropColumn('standard_duration_minutes');
        });
        Schema::table('training_sessions', function (Blueprint $table) {
            $table->dropColumn('actual_duration_minutes');
        });
        Schema::table('training_histories', function (Blueprint $table) {
            $table->dropColumn('duration_minutes_snapshot');
        });
    }
};
