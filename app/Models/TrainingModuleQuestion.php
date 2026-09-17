<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingModuleQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'training_module_id',
        'question_text',
        'question_image_path',
        'option_a',
        'option_a_image_path',
        'option_b',
        'option_b_image_path',
        'option_c',
        'option_c_image_path',
        'option_d',
        'option_d_image_path',
        'correct_option',
        'order_index',
    ];

    public function trainingModule(): BelongsTo
    {
        return $this->belongsTo(TrainingModule::class);
    }

    /**
     * Daftar opsi beserta gambarnya — dipakai view pretest/posttest.
     *
     * @return array<string, array{text: ?string, image: ?string}>
     */
    public function optionsList(): array
    {
        $options = [];

        foreach (['a', 'b', 'c', 'd'] as $key) {
            $options[$key] = [
                'text' => $this->{"option_{$key}"},
                'image' => $this->{"option_{$key}_image_path"},
            ];
        }

        return $options;
    }

    /**
     * Semua path gambar milik soal ini (pertanyaan + opsi) — dipakai saat
     * menghapus soal supaya file tidak menumpuk di storage.
     */
    public function allImagePaths(): array
    {
        return array_values(array_filter([
            $this->question_image_path,
            $this->option_a_image_path,
            $this->option_b_image_path,
            $this->option_c_image_path,
            $this->option_d_image_path,
        ]));
    }
}
