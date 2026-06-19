<?php

use App\Modules\Transport\Controllers\TransportController;
use Illuminate\Support\Facades\Route;

Route::prefix('transport')
    ->name('transport.')
    ->middleware('permission:transport.view')
    ->group(function (): void {
        Route::get('/', [TransportController::class, 'index'])->name('index');

        // Vehicles
        Route::get('vehicles/data', [TransportController::class, 'vehiclesData'])->name('vehicles.data');
        Route::post('vehicles', [TransportController::class, 'storeVehicle'])->middleware('permission:transport.create')->name('vehicles.store');
        Route::get('vehicles/{vehicle}', [TransportController::class, 'showVehicle'])->name('vehicles.show');
        Route::put('vehicles/{vehicle}', [TransportController::class, 'updateVehicle'])->middleware('permission:transport.update')->name('vehicles.update');
        Route::delete('vehicles/{vehicle}', [TransportController::class, 'destroyVehicle'])->middleware('permission:transport.delete')->name('vehicles.destroy');

        // Drivers
        Route::get('drivers/data', [TransportController::class, 'driversData'])->name('drivers.data');
        Route::post('drivers', [TransportController::class, 'storeDriver'])->middleware('permission:transport.create')->name('drivers.store');
        Route::get('drivers/{driver}', [TransportController::class, 'showDriver'])->name('drivers.show');
        Route::put('drivers/{driver}', [TransportController::class, 'updateDriver'])->middleware('permission:transport.update')->name('drivers.update');
        Route::delete('drivers/{driver}', [TransportController::class, 'destroyDriver'])->middleware('permission:transport.delete')->name('drivers.destroy');

        // Routes
        Route::get('routes/data', [TransportController::class, 'routesData'])->name('routes.data');
        Route::post('routes', [TransportController::class, 'storeRoute'])->middleware('permission:transport.create')->name('routes.store');
        Route::get('routes/{route}', [TransportController::class, 'showRoute'])->name('routes.show');
        Route::put('routes/{route}', [TransportController::class, 'updateRoute'])->middleware('permission:transport.update')->name('routes.update');
        Route::delete('routes/{route}', [TransportController::class, 'destroyRoute'])->middleware('permission:transport.delete')->name('routes.destroy');

        // Route Stops
        Route::get('route-stops/data', [TransportController::class, 'routeStopsData'])->name('route-stops.data');
        Route::post('route-stops', [TransportController::class, 'storeRouteStop'])->middleware('permission:transport.create')->name('route-stops.store');
        Route::get('route-stops/{routeStop}', [TransportController::class, 'showRouteStop'])->name('route-stops.show');
        Route::put('route-stops/{routeStop}', [TransportController::class, 'updateRouteStop'])->middleware('permission:transport.update')->name('route-stops.update');
        Route::delete('route-stops/{routeStop}', [TransportController::class, 'destroyRouteStop'])->middleware('permission:transport.delete')->name('route-stops.destroy');

        // Transport Assignments
        Route::get('assignments/data', [TransportController::class, 'assignmentsData'])->name('assignments.data');
        Route::post('assignments', [TransportController::class, 'storeAssignment'])->middleware('permission:transport.create')->name('assignments.store');
        Route::get('assignments/{assignment}', [TransportController::class, 'showAssignment'])->name('assignments.show');
        Route::put('assignments/{assignment}', [TransportController::class, 'updateAssignment'])->middleware('permission:transport.update')->name('assignments.update');
        Route::delete('assignments/{assignment}', [TransportController::class, 'destroyAssignment'])->middleware('permission:transport.delete')->name('assignments.destroy');

        // Search (Select2 AJAX)
        Route::get('search/students', [TransportController::class, 'searchStudents'])->name('search.students');
        Route::get('search/routes', [TransportController::class, 'searchRoutes'])->name('search.routes');

        // Reports
        // Route detail (stops in pickup/drop order)
        Route::get('routes/{route}/detail', [TransportController::class, 'routeDetail'])->name('routes.detail');

        Route::get('reports', [TransportController::class, 'reports'])->name('reports.index');
        Route::get('reports/vehicles/data', [TransportController::class, 'vehicleReportData'])->name('reports.vehicles.data');
        Route::get('reports/drivers/data', [TransportController::class, 'driverReportData'])->name('reports.drivers.data');
        Route::get('reports/routes/data', [TransportController::class, 'routeReportData'])->name('reports.routes.data');
        Route::get('reports/route-students/data', [TransportController::class, 'routeStudentsData'])->name('reports.route-students.data');
        Route::get('reports/vehicle-occupancy/data', [TransportController::class, 'vehicleOccupancyData'])->name('reports.vehicle-occupancy.data');
        Route::get('reports/fee/data', [TransportController::class, 'feeReportData'])->name('reports.fee.data');

        Route::get('reports/{report}/export/excel', [TransportController::class, 'exportExcel'])->middleware('permission:transport.view')->name('reports.export.excel');
        Route::get('reports/{report}/export/pdf', [TransportController::class, 'exportPdf'])->middleware('permission:transport.view')->name('reports.export.pdf');
        Route::get('reports/{report}/print', [TransportController::class, 'printReport'])->middleware('permission:transport.view')->name('reports.print');
    });
