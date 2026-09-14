<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton table — cuma akan ada 1 baris (dikelola lewat
     * CertificateTemplate::current()). Posisi/gaya tiap elemen teks
     * (nama, nama training, tanggal, skor) disimpan sebagai JSON
     * (`fields_config`) supaya fleksibel — tidak perlu 15+ kolom pipih
     * untuk 4 elemen x (x, y, ukuran font, warna, aktif/tidak).
     */
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('background_image_path');
            $table->string('original_filename');
            $table->unsignedSmallInteger('page_width_mm')->default(297); // A4 landscape
            $table->unsignedSmallInteger('page_height_mm')->default(210);
            $table->json('fields_config');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
