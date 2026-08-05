<?php

use App\Modules\Settings\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')
    ->name('settings.')
    ->middleware('permission:settings.view')
    ->group(function (): void {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/', [SettingsController::class, 'update'])->middleware('permission:settings.update')->name('update');

        // Mobile Branding Configuration
        Route::get('mobile/branding', [SettingsController::class, 'mobileBranding'])->name('mobile.branding');
        Route::post('mobile/branding', [SettingsController::class, 'updateMobileBranding'])
            ->middleware('permission:settings.update')
            ->name('mobile.branding.update');
    });
