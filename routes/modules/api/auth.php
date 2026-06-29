<?php

// ────────────────────────────────────────────────────────────────────────────
// Auth (authenticated routes) — api.v1.*
// ────────────────────────────────────────────────────────────────────────────

use App\Modules\Auth\Controllers\ApiAuthController;
use Illuminate\Support\Facades\Route;

Route::get('me', [ApiAuthController::class, 'me'])->name('me');
Route::post('auth/refresh', [ApiAuthController::class, 'refreshToken'])->name('auth.refresh');
Route::post('auth/logout', [ApiAuthController::class, 'logout'])->name('auth.logout');
