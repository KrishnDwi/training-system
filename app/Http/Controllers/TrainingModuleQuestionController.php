<?php

namespace App\Http\Controllers;

use App\Models\TrainingModule;
use App\Models\TrainingModuleQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class TrainingModuleQuestionController extends Controller
{
    protected function validateRequest(Request $request, ?TrainingModuleQuestion $question = null): array
    {
        $validated = $request->validate([
            'question_text' => ['nullable', 'string'],
            'question_image' => ['nullable', 'image', 'max:5120'], // maks 5MB
            'option_a' => ['nullable', 'string', 'max:255'],
            'option_a_image' => ['nullable', 'image', 'max:5120'],
            'option_b' => ['nullable', 'string', 'max:255'],
            'option_b_image' => ['nullable', 'image', 'max:5120'],
            'option_c' => ['nullable', 'string', 'max:255'],
            'option_c_image' => ['nullable', 'image', 'max:5120'],
            'option_d' => ['nullable', 'string', 'max:255'],
            'option_d_image' => ['nullable', 'image', 'max:5120'],
            'correct_option' => ['required', 'in:a,b,c,d'],
        ]);

        // Aturan yang tidak bisa diekspresikan lewat rules biasa: tiap elemen
        // (pertanyaan & tiap opsi) WAJIB punya minimal salah satu — teks ATAU
        // gambar. Boleh dua-duanya, tapi tidak boleh kosong sama sekali.
        $errors = [];

        $hasQuestionContent = filled($validated['question_text'] ?? null)
            || $request->hasFile('question_image')
            || $question?->question_image_path;

        if (!$hasQuestionContent) {
            $errors['question_text'] = 'Pertanyaan harus diisi teks atau gambar (boleh keduanya).';
        }

        foreach (['a', 'b', 'c', 'd'] as $key) {
            $hasOptionContent = filled($validated["option_{$key}"] ?? null)
                || $request->hasFile("option_{$key}_image")
                || $question?->{"option_{$key}_image_path"};

            if (!$hasOptionContent) {
                $errors["option_{$key}"] = 'Opsi ' . strtoupper($key) . ' harus diisi teks atau gambar.';
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }

        return $validated;
    }

    /**
     * Simpan gambar yang diupload ke disk 'local' (privat) dan kembalikan
     * path-nya. Gambar lama dihapus supaya tidak menumpuk di storage.
     */
    protected function handleImageUploads(Request $request, array $data, ?TrainingModuleQuestion $question = null): array
    {
        $imageFields = [
            'question_image' => 'question_image_path',
            'option_a_image' => 'option_a_image_path',
            'option_b_image' => 'option_b_image_path',
            'option_c_image' => 'option_c_image_path',
            'option_d_image' => 'option_d_image_path',
        ];

        foreach ($imageFields as $inputName => $column) {
            // Checkbox "hapus gambar" — dicentang berarti gambar dibuang
            // tanpa harus upload pengganti.
            if ($question && $request->boolean("remove_{$inputName}")) {
                $this->deleteImage($question->{$column});
                $data[$column] = null;
                continue;
            }

            if ($request->hasFile($inputName)) {
                if ($question) {
                    $this->deleteImage($question->{$column});
                }

                $data[$column] = $request->file($inputName)->store('question-images', 'local');
            }
        }

        return $data;
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    public function store(Request $request, TrainingModule $trainingModule)
    {
        $validated = $this->validateRequest($request);

        $data = $this->handleImageUploads($request, $validated);
        $data['order_index'] = ($trainingModule->questions()->max('order_index') ?? 0) + 1;

        // Field file tidak boleh ikut mass-assignment ke kolom DB
        unset($data['question_image'], $data['option_a_image'], $data['option_b_image'], $data['option_c_image'], $data['option_d_image']);

        $trainingModule->questions()->create($data);

        return back()->with('success', 'Soal berhasil ditambahkan.');
    }

    public function update(Request $request, TrainingModule $trainingModule, TrainingModuleQuestion $question)
    {
        abort_unless($question->training_module_id === $trainingModule->id, 404);

        $validated = $this->validateRequest($request, $question);
        $data = $this->handleImageUploads($request, $validated, $question);

        unset($data['question_image'], $data['option_a_image'], $data['option_b_image'], $data['option_c_image'], $data['option_d_image']);

        $question->update($data);

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(TrainingModule $trainingModule, TrainingModuleQuestion $question)
    {
        abort_unless($question->training_module_id === $trainingModule->id, 404);

        // Hapus juga file gambarnya supaya tidak jadi sampah di storage
        foreach ($question->allImagePaths() as $path) {
            $this->deleteImage($path);
        }

        $question->delete();

        return back()->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Serve gambar soal. File disimpan di disk privat, jadi tidak ada URL
     * langsung — akses harus lewat route ini. Sengaja bisa diakses baik oleh
     * HR (halaman Edit Master Training) maupun karyawan yang sedang
     * mengerjakan test di Portal.
     */
    public function showImage(TrainingModuleQuestion $question, string $field)
    {
        $allowed = ['question', 'option_a', 'option_b', 'option_c', 'option_d'];
        abort_unless(in_array($field, $allowed, true), 404);

        $path = $question->{"{$field}_image_path"};

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }
}
