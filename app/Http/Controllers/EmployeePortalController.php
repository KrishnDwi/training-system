<?php

namespace App\Http\Controllers;

use App\Models\EmployeeModuleProgress;
use App\Models\TrainingMaterial;
use App\Models\TrainingModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeePortalController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();

        $modules = TrainingModule::active()
            ->with('materials')
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        // Progress karyawan ini untuk semua modul yang punya test, di-index
        // by training_module_id supaya gampang dicocokkan di view (bukan query
        // N+1 per kartu modul).
        $progressByModule = EmployeeModuleProgress::where('employee_id', $employee->id)
            ->get()
            ->keyBy('training_module_id');

        $missingMandatoryModules = $employee->missingMandatoryModules();

        return view('portal.index', compact('employee', 'modules', 'missingMandatoryModules', 'progressByModule'));
    }

    /**
     * Download materi — WAJIB login (route ini di-guard middleware
     * auth:employee), file disimpan di disk 'local' (privat) sehingga
     * tidak bisa diakses lewat URL langsung tanpa lewat sini.
     */
    public function download(TrainingMaterial $material)
    {
        abort_unless(Storage::disk('local')->exists($material->file_path), 404);

        return Storage::disk('local')->download($material->file_path, $material->original_filename);
    }
}
