<?php

namespace App\Http\Controllers;

use App\Models\TrainingMaterial;
use App\Models\TrainingModule;
use App\Services\TrainingMaterialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingMaterialController extends Controller
{
    public function __construct(protected TrainingMaterialService $trainingMaterialService)
    {
    }

    /**
     * File disimpan di disk 'local' (storage/app/private secara default di
     * Laravel 12) — SENGAJA BUKAN disk 'public', supaya tidak ada URL publik
     * langsung ke file. Karyawan hanya bisa download lewat route yang
     * dilindungi middleware auth:employee (EmployeePortalController::download).
     */
    public function store(Request $request, TrainingModule $trainingModule)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'file' => ['required', 'file', 'max:51200'], // maks 50MB
        ]);

        $this->trainingMaterialService->store(
            $trainingModule,
            $request->title,
            $request->file('file')
        );

        return back()->with('success', 'Materi berhasil diupload.');
    }

    public function destroy(TrainingModule $trainingModule, TrainingMaterial $material)
    {
        abort_unless($material->training_module_id === $trainingModule->id, 404);

        Storage::disk('local')->delete($material->file_path);
        $material->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
