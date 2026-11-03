
<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrainingController;
use App\Http\Middleware\EnsureAdminAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', EnsureAdminAccess::class])->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('trainings', TrainingController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('/assignments/{assignment}/complete', [AssignmentController::class, 'complete'])
        ->name('assignments.complete');
});
