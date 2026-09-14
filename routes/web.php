<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingModuleController;
use App\Http\Controllers\TrainingMaterialController;
use App\Http\Controllers\TrainingModuleQuestionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\PortalTestController;
use App\Http\Controllers\CertificateTemplateController;

/*
|--------------------------------------------------------------------------
| Root ("/") — langsung ke halaman login karyawan
|--------------------------------------------------------------------------
| Kalau karyawan sudah login, EmployeeAuthController::showLoginForm()
| otomatis redirect ke portal.index — jadi ini aman dipanggil berulang.
*/
Route::get('/', function () {
    return redirect()->route('portal.login');
});

/*
|--------------------------------------------------------------------------
| Portal Karyawan — Login (guest) & Halaman Utama (wajib login)
|--------------------------------------------------------------------------
| SENGAJA di luar prefix /admin — ini yang diakses karyawan biasa.
*/
Route::middleware('guest:employee')->group(function () {
    Route::get('/portal/login', [EmployeeAuthController::class, 'showLoginForm'])->name('portal.login');
    Route::post('/portal/login', [EmployeeAuthController::class, 'login'])->name('portal.login.submit');
});

Route::middleware('auth:employee')->group(function () {
    Route::get('/portal', [EmployeePortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/materials/{material}/download', [EmployeePortalController::class, 'download'])
        ->name('portal.materials.download');
    Route::post('/portal/logout', [EmployeeAuthController::class, 'logout'])->name('portal.logout');

    // Alur Pre-Test -> Materi -> Post-Test
    Route::get('/portal/modules/{training_module}', [PortalTestController::class, 'show'])
        ->name('portal.modules.show');
    Route::post('/portal/modules/{training_module}/pretest', [PortalTestController::class, 'submitPretest'])
        ->name('portal.modules.pretest');
    Route::post('/portal/modules/{training_module}/material-confirm', [PortalTestController::class, 'confirmMaterial'])
        ->name('portal.modules.material-confirm');
    Route::post('/portal/modules/{training_module}/posttest', [PortalTestController::class, 'submitPosttest'])
        ->name('portal.modules.posttest');
    Route::get('/portal/modules/{training_module}/certificate', [PortalTestController::class, 'downloadCertificate'])
        ->name('portal.modules.certificate');
});

/*
|--------------------------------------------------------------------------
| ADMIN (HRD) — semuanya di bawah prefix /admin
|--------------------------------------------------------------------------
| Nama route (dashboard, training-modules.index, dst) TIDAK berubah —
| cuma URI-nya yang sekarang berawalan /admin. Semua pemanggilan route()
| di view/controller otomatis tetap benar tanpa perlu diubah.
*/
Route::prefix('admin')->group(function () {

    // Dashboard — otomatis jadi URI persis "/admin"
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Master Training + Materi + Bank Soal
    // PENTING: route 'data' harus didaftarkan SEBELUM Route::resource,
    // supaya 'data' tidak tertangkap sebagai {training_module} (route model binding).
    Route::get('training-modules/data', [TrainingModuleController::class, 'data'])
        ->name('training-modules.data');

    Route::resource('training-modules', TrainingModuleController::class)
        ->except(['show']); // tidak perlu halaman detail terpisah untuk master data sederhana ini

    Route::post('training-modules/{training_module}/materials', [TrainingMaterialController::class, 'store'])
        ->name('training-modules.materials.store');
    Route::delete('training-modules/{training_module}/materials/{material}', [TrainingMaterialController::class, 'destroy'])
        ->name('training-modules.materials.destroy');

    Route::post('training-modules/{training_module}/questions', [TrainingModuleQuestionController::class, 'store'])
        ->name('training-modules.questions.store');
    Route::put('training-modules/{training_module}/questions/{question}', [TrainingModuleQuestionController::class, 'update'])
        ->name('training-modules.questions.update');
    Route::delete('training-modules/{training_module}/questions/{question}', [TrainingModuleQuestionController::class, 'destroy'])
        ->name('training-modules.questions.destroy');

    // Training Session
    Route::resource('training-sessions', TrainingSessionController::class)
        ->only(['index', 'create', 'store', 'show']);

    // Data Karyawan + Kontrak
    Route::get('employees/data', [EmployeeController::class, 'data'])
        ->name('employees.data'); // didaftarkan sebelum resource, sama seperti training-modules/data

    Route::get('employees/import', [EmployeeController::class, 'showImportForm'])
        ->name('employees.import.form');
    Route::post('employees/import', [EmployeeController::class, 'import'])
        ->name('employees.import');

    Route::get('employees/export/master', [EmployeeController::class, 'exportMaster'])
        ->name('employees.export.master');

    // 'show' TIDAK di-except — dipakai untuk halaman Detail Karyawan
    Route::resource('employees', EmployeeController::class);

    Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])
        ->name('employees.contracts.store');
    Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])
        ->name('employees.contracts.destroy');

    // Report
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Template Sertifikat (singleton — 1 desain dipakai semua training)
    Route::get('certificate-template', [CertificateTemplateController::class, 'edit'])
        ->name('certificate-template.edit');
    Route::post('certificate-template', [CertificateTemplateController::class, 'update'])
        ->name('certificate-template.update');
    Route::get('certificate-template/preview', [CertificateTemplateController::class, 'preview'])
        ->name('certificate-template.preview');
});
