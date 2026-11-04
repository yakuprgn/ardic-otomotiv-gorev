<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureAdminAccess;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});
Route::middleware(['throttle:120,1', EnsureAdminAccess::class])->group(function (): void {
    Route::redirect('/', '/employees');
    Route::resource('employees', EmployeeController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('/employees-export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
