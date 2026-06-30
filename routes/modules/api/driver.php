<?php

use App\Http\Controllers\Api\V1\DriverApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('driver')->name('driver.')->group(function (): void {

    // Profile
    Route::get('profile', [DriverApiController::class, 'profile'])->name('profile');

    // Dashboard
    Route::get('dashboard', [DriverApiController::class, 'dashboard'])->name('dashboard');

    // Trips
    Route::get('trips/today', [DriverApiController::class, 'tripsToday'])->name('trips.today');
    Route::get('trips/{trip}', [DriverApiController::class, 'tripShow'])->name('trips.show');
    Route::post('trips/{trip}/start', [DriverApiController::class, 'tripStart'])->name('trips.start');
    Route::post('trips/{trip}/complete', [DriverApiController::class, 'tripComplete'])->name('trips.complete');
    Route::get('trips/{trip}/students', [DriverApiController::class, 'tripStudents'])->name('trips.students');
    Route::post('trips/{trip}/pickup', [DriverApiController::class, 'pickup'])->name('trips.pickup');
    Route::post('trips/{trip}/drop', [DriverApiController::class, 'drop'])->name('trips.drop');
    Route::get('trips/{trip}/eta', [DriverApiController::class, 'eta'])->name('trips.eta');

    // Location
    Route::post('location', [DriverApiController::class, 'updateLocation'])->name('location.update');

    // SOS
    Route::post('sos', [DriverApiController::class, 'sos'])->name('sos');
});
