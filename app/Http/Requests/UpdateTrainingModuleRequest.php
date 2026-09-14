<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrainingModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $moduleId = $this->route('training_module')->id;

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('training_modules', 'code')->ignore($moduleId)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_mandatory' => ['required', 'boolean'],
            'standard_duration_minutes' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'validity_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'pretest_time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'posttest_time_limit_minutes' => ['nullable', 'integer', 'min:1', 'max:600'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
