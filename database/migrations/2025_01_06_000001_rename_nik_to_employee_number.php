<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pola yang sama seperti migration konversi durasi jam->menit sebelumnya
        // (tambah kolom baru -> salin data -> hapus kolom lama) — supaya tidak
        // bergantung ke doctrine/dbal untuk rename/alter kolom, lebih portable.
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_number', 30)->nullable()->unique()->after('id');
        });

        foreach (DB::table('employees')->select('id', 'nik')->cursor() as $row) {
            DB::table('employees')->where('id', $row->id)->update([
                'employee_number' => $row->nik,
            ]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['nik']);
            $table->dropColumn('nik');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->unique()->after('id');
        });

        foreach (DB::table('employees')->select('id', 'employee_number')->cursor() as $row) {
            DB::table('employees')->where('id', $row->id)->update([
                'nik' => $row->employee_number,
            ]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['employee_number']);
            $table->dropColumn('employee_number');
        });
    }
};
