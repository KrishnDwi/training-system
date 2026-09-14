<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            // Nullable = tanpa batas waktu (perilaku lama tetap jalan kalau
            // HR tidak mengisi). Dipisah pretest/posttest karena durasi yang
            // wajar bisa berbeda (mis. post-test sengaja dikasih waktu lebih
            // ketat untuk menguji pemahaman, bukan menghafal).
            $table->unsignedSmallInteger('pretest_time_limit_minutes')->nullable()->after('passing_score');
            $table->unsignedSmallInteger('posttest_time_limit_minutes')->nullable()->after('pretest_time_limit_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->dropColumn(['pretest_time_limit_minutes', 'posttest_time_limit_minutes']);
        });
    }
};
