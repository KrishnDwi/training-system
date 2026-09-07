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

// Upload/hapus materi (sisi HR — di halaman Edit Master Training)
Route::post('training-modules/{training_module}/materials', [TrainingMaterialController::class, 'store'])
    ->name('training-modules.materials.store');
Route::delete('training-modules/{training_module}/materials/{material}', [TrainingMaterialController::class, 'destroy'])
    ->name('training-modules.materials.destroy');