<?php

use App\Modules\Notifications\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Bell & mark-read — available to all authenticated users (no permission gate)
Route::get('notifications/bell', [NotificationController::class, 'bell'])->name('notifications.bell');
Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

Route::prefix('notifications')
    ->as('notifications.')
    ->middleware('permission:notifications.view')
    ->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('dashboard', [NotificationController::class, 'dashboard'])->name('dashboard');
        Route::get('data', [NotificationController::class, 'data'])->name('data');
        Route::get('stats', [NotificationController::class, 'stats'])->name('stats');

        Route::post('/', [NotificationController::class, 'store'])
            ->middleware('permission:notifications.create')
            ->name('store');

        Route::get('{notification}', [NotificationController::class, 'show'])->name('show');
        Route::put('{notification}', [NotificationController::class, 'update'])
            ->middleware('permission:notifications.update')
            ->name('update');

        Route::delete('{notification}', [NotificationController::class, 'destroy'])
            ->middleware('permission:notifications.delete')
            ->name('destroy');

        Route::post('{notification}/send', [NotificationController::class, 'send'])
            ->middleware('permission:notifications.send')
            ->name('send');
    });