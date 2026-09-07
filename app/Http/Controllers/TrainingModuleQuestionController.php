<?php

namespace App\Http\Controllers;

use App\Models\TrainingModule;
use App\Models\TrainingModuleQuestion;
use Illuminate\Http\Request;

class TrainingModuleQuestionController extends Controller
{
    protected function rules(): array
    {
        return [
            'question_text' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:255'],
            'option_b' => ['required', 'string', 'max:255'],
            'option_c' => ['required', 'string', 'max:255'],
            'option_d' => ['required', 'string', 'max:255'],
            'correct_option' => ['required', 'in:a,b,c,d'],
        ];
    }

    public function store(Request $request, TrainingModule $trainingModule)
    {
        $validated = $request->validate($this->rules());

        $nextOrder = ($trainingModule->questions()->max('order_index') ?? 0) + 1;

        $trainingModule->questions()->create([
            ...$validated,
            'order_index' => $nextOrder,
        ]);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function update(Request $request, TrainingModule $trainingModule, TrainingModuleQuestion $question)
    {
        abort_unless($question->training_module_id === $trainingModule->id, 404);

        $validated = $request->validate($this->rules());
        $question->update($validated);

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(TrainingModule $trainingModule, TrainingModuleQuestion $question)
    {
        abort_unless($question->training_module_id === $trainingModule->id, 404);

        $question->delete();

        return back()->with('success', 'Soal berhasil dihapus.');
    }
}
