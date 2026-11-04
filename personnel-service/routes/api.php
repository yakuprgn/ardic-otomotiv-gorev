<?php

use App\Http\Controllers\Api\EmployeeApiController;
use App\Http\Middleware\EnsureServiceToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['throttle:60,1', EnsureServiceToken::class])
    ->group(function (): void {
        Route::get('/employees', [EmployeeApiController::class, 'index'])
            ->name('api.employees.index');

        Route::get('/employees/{employee}', [EmployeeApiController::class, 'show'])
            ->name('api.employees.show');
    });
