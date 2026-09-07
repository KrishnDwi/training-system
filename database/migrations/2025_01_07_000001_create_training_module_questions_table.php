<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_module_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_module_id')->constrained('training_modules')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('option_a', 255);
            $table->string('option_b', 255);
            $table->string('option_c', 255);
            $table->string('option_d', 255);
            $table->enum('correct_option', ['a', 'b', 'c', 'd']);
            $table->unsignedSmallInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['training_module_id', 'order_index']);
        });

        // Nilai minimum kelulusan post-test, per modul (default 70).
        Schema::table('training_modules', function (Blueprint $table) {
            $table->unsignedTinyInteger('passing_score')->default(70)->after('validity_months');
        });
    }

    public function down(): void
    {
        Schema::table('training_modules', function (Blueprint $table) {
            $table->dropColumn('passing_score');
        });
        Schema::dropIfExists('training_module_questions');
    }
};
