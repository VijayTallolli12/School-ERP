<?php

use App\Http\Controllers\Api\V1\DriverApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('driver')->name('driver.')->group(function (): void {

    // ─── AUTH ────────────────────────────────────────────────────────
    Route::get('me', [DriverApiController::class, 'me'])->name('me');
    Route::post('logout', [DriverApiController::class, 'logout'])->name('logout');

    // ─── DASHBOARD ───────────────────────────────────────────────────
    Route::get('dashboard', [DriverApiController::class, 'dashboard'])->name('dashboard');

    // ─── PROFILE (legacy) ────────────────────────────────────────────
    Route::get('profile', [DriverApiController::class, 'profile'])->name('profile');

    // ─── ROUTE ───────────────────────────────────────────────────────
    Route::get('routes/today', [DriverApiController::class, 'routesToday'])->name('routes.today');
    Route::get('routes/{route}', [DriverApiController::class, 'routeShow'])->name('routes.show');
    Route::get('routes/{route}/stops', [DriverApiController::class, 'routeStops'])->name('routes.stops');
    Route::get('routes/{route}/students', [DriverApiController::class, 'routeStudents'])->name('routes.students');

    // ─── TRIP CONTROL ────────────────────────────────────────────────
    Route::post('trips/start', [DriverApiController::class, 'tripStartById'])->name('trips.start-by-id');
    Route::post('trips/{trip}/start', [DriverApiController::class, 'tripStart'])->name('trips.start');
    Route::post('trips/{trip}/end', [DriverApiController::class, 'tripEnd'])->name('trips.end');
    Route::post('trips/{trip}/complete', [DriverApiController::class, 'tripComplete'])->name('trips.complete');
    Route::get('trips/current', [DriverApiController::class, 'tripCurrent'])->name('trips.current');
    Route::get('trips/today', [DriverApiController::class, 'tripsToday'])->name('trips.today');
    Route::get('trips/history', [DriverApiController::class, 'tripsHistory'])->name('trips.history');
    Route::get('trips/{trip}', [DriverApiController::class, 'tripShow'])->name('trips.show');
    Route::get('trips/{trip}/students', [DriverApiController::class, 'tripStudents'])->name('trips.students');
    Route::get('trips/{trip}/eta', [DriverApiController::class, 'eta'])->name('trips.eta');

    // ─── ATTENDANCE ──────────────────────────────────────────────────
    Route::post('trips/{trip}/attendance', [DriverApiController::class, 'markAttendance'])->name('attendance.store');
    Route::put('trips/{trip}/attendance/{tripStudent}', [DriverApiController::class, 'updateAttendance'])->name('attendance.update');
    Route::post('trips/{trip}/pickup', [DriverApiController::class, 'pickup'])->name('trips.pickup');
    Route::post('trips/{trip}/drop', [DriverApiController::class, 'drop'])->name('trips.drop');
    Route::post('trips/{trip}/mark-missed', [DriverApiController::class, 'markMissed'])->name('trips.mark-missed');

    // ─── STOP FLOW ───────────────────────────────────────────────────
    Route::post('trips/{trip}/arrive-stop', [DriverApiController::class, 'arriveStop'])->name('trips.arrive-stop');
    Route::post('trips/{trip}/leave-stop', [DriverApiController::class, 'leaveStop'])->name('trips.leave-stop');

    // ─── NOTIFICATIONS ───────────────────────────────────────────────
    Route::get('notifications', [DriverApiController::class, 'notifications'])->name('notifications');
    Route::post('notifications/read', [DriverApiController::class, 'markNotificationsRead'])->name('notifications.read');

    // ─── LOCATION / SOS ──────────────────────────────────────────────
    Route::post('location', [DriverApiController::class, 'updateLocation'])->name('location.update');
    Route::post('trips/{trip}/location', [DriverApiController::class, 'tripLocation'])->name('trips.location');
    Route::post('sos', [DriverApiController::class, 'sos'])->name('sos');
});