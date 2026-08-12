<?php

use App\Modules\MobileApps\Controllers\MobileAppsController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile-apps')
    ->name('mobile-apps.')
    ->middleware('role:Super Admin|School Admin|Principal|Teacher|Accountant|Librarian|Payroll Manager|Receptionist|HR|Staff')
    ->group(function (): void {
        Route::get('/', [MobileAppsController::class, 'index'])->name('index');
    });
