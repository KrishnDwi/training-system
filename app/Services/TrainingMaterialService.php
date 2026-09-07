<?php

namespace App\Services;

use App\Models\TrainingMaterial;
use App\Models\TrainingModule;
use Illuminate\Http\UploadedFile;

class TrainingMaterialService
{
    public function store(TrainingModule $module, string $title, UploadedFile $file): TrainingMaterial
    {
        $path = $file->store('training-materials', 'local');

        return $module->materials()->create([
            'title' => $title,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * @param  array<int, array{title?: string|null, file?: UploadedFile|null}>  $materials
     * @return TrainingMaterial[]
     */
    public function storeMany(TrainingModule $module, array $materials): array
    {
        $stored = [];

        foreach ($materials as $item) {
            if (empty($item['file'])) {
                continue;
            }

            $title = trim($item['title'] ?? '');
            if ($title === '') {
                $title = pathinfo($item['file']->getClientOriginalName(), PATHINFO_FILENAME);
            }

            $stored[] = $this->store($module, $title, $item['file']);
        }

        return $stored;
    }
}
