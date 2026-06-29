<?php

// ────────────────────────────────────────────────────────────────────────────
// Transport API — api.v1.transport.*
// Phase 5.5 — Live Transportation Tracking
// ────────────────────────────────────────────────────────────────────────────

use App\Http\Controllers\Api\V1\TransportRealtimeController;
use Illuminate\Support\Facades\Route;

Route::prefix('transport')->name('transport.')->group(function (): void {

    // Location updates
    Route::post('location', [TransportRealtimeController::class, 'updateLocation'])
        ->name('location.update');

    // Live status dashboard
    Route::get('live', [TransportRealtimeController::class, 'liveStatus'])
        ->name('live');

    // Vehicle-specific location history
    Route::get('vehicle/{id}/location', [TransportRealtimeController::class, 'vehicleLocation'])
        ->name('vehicle.location');
});
