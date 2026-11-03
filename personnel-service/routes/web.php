<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Middleware\EnsureAdminAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1', EnsureAdminAccess::class])->group(function (): void {
    Route::redirect('/', '/employees');
    Route::resource('employees', EmployeeController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
