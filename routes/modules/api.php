<?php

use App\Modules\Auth\Controllers\ApiAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes — School ERP (Sanctum)
|--------------------------------------------------------------------------
|
| Base: /api/v1/*
| Auth: Sanctum token-based (Bearer token)
| School context: X-School-Id header or school_id param
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {

    // ───────────────────────────────────────────────────────────────────
    // Authentication (public + throttled)
    // ───────────────────────────────────────────────────────────────────
    Route::post('auth/login', [ApiAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('auth.login');

    // Phase 5.2 — Teacher App Login (public, throttled)
    Route::post('teacher/login', [\App\Http\Controllers\Api\V1\TeacherAppController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('teacher.login');

    // Phase 5.3 — Student App Login (public, throttled)
    Route::post('student/login', [\App\Http\Controllers\Api\V1\StudentAppController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('student.login');

    // Phase 5.6 — Driver App Login (public, throttled)
    Route::post('driver/login', [\App\Http\Controllers\Api\V1\DriverApiController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('driver.login');

    // ───────────────────────────────────────────────────────────────────
    // Authenticated routes (Sanctum + School context + throttle)
    // ───────────────────────────────────────────────────────────────────
    Route::middleware(['auth:sanctum', 'school', 'throttle:60,1'])->group(function (): void {

        // Auth (authenticated)
        require __DIR__.'/api/auth.php';

        // Feature modules
        require __DIR__.'/api/dashboard.php';
        require __DIR__.'/api/students.php';
        require __DIR__.'/api/parents.php';
        require __DIR__.'/api/teachers.php';
        require __DIR__.'/api/attendance.php';
        require __DIR__.'/api/fees.php';
        require __DIR__.'/api/exams.php';
        require __DIR__.'/api/notifications.php';

        // Phase 5.5 — Transport API (Live Tracking)
        require __DIR__.'/api/transport.php';

        // Phase 5.2 — Teacher App API
        require __DIR__.'/api/teacher-app.php';

        // Phase 5.3 — Student App API
        require __DIR__.'/api/student-app.php';

        // Phase 5.6 — Driver App API
        require __DIR__.'/api/driver.php';
    });
});
