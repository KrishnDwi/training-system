<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingModuleController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\TrainingMaterialController;

// === Login HRD (guest — belum login) ===
Route::middleware(['guest', 'throttle:5,1'])->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
});

// === Portal Karyawan (terpisah dari auth HRD) ===
Route::middleware('guest:employee')->group(function () {
    Route::get('/portal/login', [EmployeeAuthController::class, 'showLoginForm'])->name('portal.login');
    Route::post('/portal/login', [EmployeeAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('portal.login.submit');
});

Route::middleware('auth:employee')->group(function () {
    Route::get('/portal', [EmployeePortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/materials/{material}/download', [EmployeePortalController::class, 'download'])
        ->name('portal.materials.download');
    Route::post('/portal/logout', [EmployeeAuthController::class, 'logout'])->name('portal.logout');
});

// === Area Admin HRD (WAJIB login) ===
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::resource('training-sessions', TrainingSessionController::class)
        ->only(['index', 'create', 'store', 'show']);

    Route::get('training-modules/data', [TrainingModuleController::class, 'data'])
        ->name('training-modules.data');

    Route::resource('training-modules', TrainingModuleController::class)
        ->except(['show']);

    Route::get('employees/data', [EmployeeController::class, 'data'])
        ->name('employees.data');

    Route::get('employees/import', [EmployeeController::class, 'showImportForm'])
        ->name('employees.import.form');
    Route::post('employees/import', [EmployeeController::class, 'import'])
        ->name('employees.import');

    Route::get('employees/export/master', [EmployeeController::class, 'exportMaster'])
        ->name('employees.export.master');

    Route::resource('employees', EmployeeController::class);

    Route::post('employees/{employee}/contracts', [EmployeeContractController::class, 'store'])
        ->name('employees.contracts.store');
    Route::delete('employees/{employee}/contracts/{contract}', [EmployeeContractController::class, 'destroy'])
        ->name('employees.contracts.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::post('training-modules/{training_module}/materials', [TrainingMaterialController::class, 'store'])
        ->name('training-modules.materials.store');
    Route::delete('training-modules/{training_module}/materials/{material}', [TrainingMaterialController::class, 'destroy'])
        ->name('training-modules.materials.destroy');
});
