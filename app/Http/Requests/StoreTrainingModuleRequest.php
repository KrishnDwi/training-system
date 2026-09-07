<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:training_modules,code'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_mandatory' => ['required', 'boolean'],
            'standard_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'validity_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],

            // Materi training (opsional, bisa lebih dari satu file sekaligus)
            'materials' => ['nullable', 'array'],
            'materials.*' => ['file', 'max:51200'], // maks 50MB per file
        ];
    }
}
