<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_module_questions', function (Blueprint $table) {
            // Semua nullable — gambar bersifat OPSIONAL, soal teks-saja
            // tetap berfungsi persis seperti sebelumnya.
            $table->string('question_image_path')->nullable()->after('question_text');
            $table->string('option_a_image_path')->nullable()->after('option_a');
            $table->string('option_b_image_path')->nullable()->after('option_b');
            $table->string('option_c_image_path')->nullable()->after('option_c');
            $table->string('option_d_image_path')->nullable()->after('option_d');
        });

        // Teks pertanyaan & opsi dijadikan NULLABLE supaya HR bisa membuat
        // soal yang isinya GAMBAR SAJA tanpa teks (mis. "pilih rambu yang
        // benar" dengan 4 gambar rambu). Validasi di Form Request memastikan
        // tiap elemen tetap punya minimal salah satu: teks ATAU gambar.
        //
        // Pola tambah-kolom-baru -> salin -> hapus-kolom-lama dipakai
        // (bukan ->change()) supaya tidak butuh package doctrine/dbal dan
        // tetap portable di SQLite maupun MySQL.
        Schema::table('training_module_questions', function (Blueprint $table) {
            $table->text('question_text_new')->nullable();
            $table->string('option_a_new', 255)->nullable();
            $table->string('option_b_new', 255)->nullable();
            $table->string('option_c_new', 255)->nullable();
            $table->string('option_d_new', 255)->nullable();
        });

        foreach (DB::table('training_module_questions')->cursor() as $row) {
            DB::table('training_module_questions')->where('id', $row->id)->update([
                'question_text_new' => $row->question_text,
                'option_a_new' => $row->option_a,
                'option_b_new' => $row->option_b,
                'option_c_new' => $row->option_c,
                'option_d_new' => $row->option_d,
            ]);
        }

        Schema::table('training_module_questions', function (Blueprint $table) {
            $table->dropColumn(['question_text', 'option_a', 'option_b', 'option_c', 'option_d']);
        });

        Schema::table('training_module_questions', function (Blueprint $table) {
            $table->renameColumn('question_text_new', 'question_text');
            $table->renameColumn('option_a_new', 'option_a');
            $table->renameColumn('option_b_new', 'option_b');
            $table->renameColumn('option_c_new', 'option_c');
            $table->renameColumn('option_d_new', 'option_d');
        });
    }

    public function down(): void
    {
        Schema::table('training_module_questions', function (Blueprint $table) {
            $table->dropColumn([
                'question_image_path',
                'option_a_image_path',
                'option_b_image_path',
                'option_c_image_path',
                'option_d_image_path',
            ]);
        });
        // Catatan: kolom teks sengaja DIBIARKAN nullable saat rollback —
        // mengembalikannya jadi NOT NULL berisiko gagal kalau sudah ada
        // soal gambar-saja yang teksnya kosong.
    }
};
