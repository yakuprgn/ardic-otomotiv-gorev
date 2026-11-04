<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrainingController;
use App\Http\Middleware\EnsureAdminAccess;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::middleware(['throttle:120,1', EnsureAdminAccess::class])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('trainings', TrainingController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('/assignments/{assignment}/complete', [AssignmentController::class, 'complete'])
        ->name('assignments.complete');
    Route::get('/trainings-export', [TrainingController::class, 'export'])->name('trainings.export');
    Route::get('/assignments-export', [AssignmentController::class, 'export'])->name('assignments.export');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
