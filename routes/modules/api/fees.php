<?php

// ────────────────────────────────────────────────────────────────────────────
// Fees — api.v1.fees.*
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\FeeApiController;
use Illuminate\Support\Facades\Route;

Route::get('fees', [FeeApiController::class, 'studentFees'])
    ->middleware('permission:fees.view')->name('fees.index');
Route::get('fees/pending', [FeeApiController::class, 'pendingFees'])
    ->middleware('permission:fees.view')->name('fees.pending');
Route::get('fees/payments', [FeeApiController::class, 'payments'])
    ->middleware('permission:fees.view')->name('fees.payments');
Route::get('fees/payments/{paymentId}/receipt', [FeeApiController::class, 'paymentReceipt'])
    ->middleware('permission:fees.view')->name('fees.receipt');
Route::get('fees/dashboard-stats', [FeeApiController::class, 'dashboardStats'])
    ->middleware('permission:fees.view')->name('fees.dashboard');
