<?php

namespace App\Modules\Transport\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Students\Models\Student;
use App\Modules\Transport\Exports\TransportReportExport;
use App\Modules\Transport\Models\Driver;
use App\Modules\Transport\Models\Route;
use App\Modules\Transport\Models\RouteStop;
use App\Modules\Transport\Models\TransportAssignment;
use App\Modules\Transport\Models\Vehicle;
use App\Modules\Transport\Repositories\TransportRepositoryInterface;
use App\Modules\Transport\Requests\StoreAssignmentRequest;
use App\Modules\Transport\Requests\StoreDriverRequest;
use App\Modules\Transport\Requests\StoreRouteRequest;
use App\Modules\Transport\Requests\StoreRouteStopRequest;
use App\Modules\Transport\Requests\StoreVehicleRequest;
use App\Modules\Transport\Requests\UpdateAssignmentRequest;
use App\Modules\Transport\Requests\UpdateDriverRequest;
use App\Modules\Transport\Requests\UpdateRouteRequest;
use App\Modules\Transport\Requests\UpdateRouteStopRequest;
use App\Modules\Transport\Requests\UpdateVehicleRequest;
use App\Modules\Transport\Services\TransportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class TransportController extends Controller
{
    public function __construct(
        private readonly TransportRepositoryInterface $transport,
        private readonly TransportService $service,
    ) {
    }

    public function index()
    {
        $activeAssignments = TransportAssignment::query()->where('status', 'active');
        $totalStudents = (clone $activeAssignments)->count();
        $vehicles = Vehicle::query()->withCount(['assignments' => fn ($q) => $q->where('status', 'active')])->get();
        $occupancyPct = $vehicles->reduce(fn (?float $carry, Vehicle $v) => $v->capacity > 0
            ? ($carry ?? 0) + (($v->assignments_count / $v->capacity) * 100)
            : $carry, null);
        $avgOccupancy = $occupancyPct !== null
            ? round($occupancyPct / max($vehicles->count(), 1))
            : null;

        $routeStops = RouteStop::query()->orderBy('route_id')->orderBy('sequence')->get();

        return view('modules.transport.index', [
            'vehicles' => Vehicle::query()->with('driver')->get(),
            'drivers' => Driver::query()->get(),
            'routes' => Route::query()->with('vehicle:id,vehicle_number')->get(),
            'routeStops' => $routeStops,
            'routeStopsJson' => $routeStops->map(fn (RouteStop $s) => [
                'id' => $s->id,
                'route_id' => $s->route_id,
                'stop_name' => $s->stop_name,
                'pickup_time' => $s->pickup_time?->format('H:i'),
                'drop_time' => $s->drop_time?->format('H:i'),
                'sequence' => $s->sequence,
            ]),
            'routesJson' => Route::query()->get(['id', 'vehicle_id'])->map(fn (Route $r) => [
                'id' => $r->id,
                'vehicle_id' => $r->vehicle_id,
            ]),
            'students' => Student::query()->orderBy('first_name')->get(),
            'stats' => [
                'routes' => Route::query()->count(),
                'vehicles' => Vehicle::query()->count(),
                'drivers' => Driver::query()->count(),
                'assigned_students' => $totalStudents,
                'avg_occupancy' => $avgOccupancy,
            ],
        ]);
    }

    // ─── Vehicles ───────────────────────────────────────────────────────────────

    public function vehiclesData(): JsonResponse
    {
        return DataTables::of($this->transport->vehicles())
            ->addColumn('driver_name', fn (Vehicle $v) => $v->driver?->name ?? '-')
            ->editColumn('vehicle_type', fn (Vehicle $v) => str($v->vehicle_type)->replace('_', ' ')->headline())
            ->editColumn('status', fn (Vehicle $v) => '<span class="badge bg-'.($v->status === 'active' ? 'success' : 'secondary').'">'.$v->status.'</span>')
            ->addColumn('actions', fn (Vehicle $v) => view('modules.transport._actions', ['type' => 'vehicle', 'model' => $v])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeVehicle(StoreVehicleRequest $request): JsonResponse
    {
        return $this->jsonCreated('Vehicle created successfully.', $this->service->createVehicle($request->validated()));
    }

    public function showVehicle(Vehicle $vehicle): JsonResponse
    {
        return $this->jsonData($vehicle->load('driver'));
    }

    public function updateVehicle(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        return $this->jsonCreated('Vehicle updated successfully.', $this->service->updateVehicle($vehicle, $request->validated()));
    }

    public function destroyVehicle(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);
        $vehicle->delete();

        return $this->jsonMessage('Vehicle deleted successfully.');
    }

    // ─── Drivers ────────────────────────────────────────────────────────────────

    public function driversData(): JsonResponse
    {
        return DataTables::of($this->transport->drivers())
            ->editColumn('license_expiry_date', fn (Driver $d) => $d->license_expiry_date?->format('d M Y'))
            ->editColumn('status', fn (Driver $d) => '<span class="badge bg-'.($d->status === 'active' ? 'success' : 'secondary').'">'.$d->status.'</span>')
            ->addColumn('actions', fn (Driver $d) => view('modules.transport._actions', ['type' => 'driver', 'model' => $d])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeDriver(StoreDriverRequest $request): JsonResponse
    {
        return $this->jsonCreated('Driver created successfully.', $this->service->createDriver($request->validated()));
    }

    public function showDriver(Driver $driver): JsonResponse
    {
        return $this->jsonData($driver);
    }

    public function updateDriver(UpdateDriverRequest $request, Driver $driver): JsonResponse
    {
        return $this->jsonCreated('Driver updated successfully.', $this->service->updateDriver($driver, $request->validated()));
    }

    public function destroyDriver(Driver $driver): JsonResponse
    {
        $this->authorize('delete', $driver);
        $driver->delete();

        return $this->jsonMessage('Driver deleted successfully.');
    }

    // ─── Routes ─────────────────────────────────────────────────────────────────

    public function routesData(): JsonResponse
    {
        return DataTables::of($this->transport->routes())
            ->addColumn('vehicle_name', fn (Route $r) => $r->vehicle?->vehicle_name ?? '-')
            ->addColumn('driver_name', fn (Route $r) => $r->driver?->name ?? '-')
            ->editColumn('status', fn (Route $r) => '<span class="badge bg-'.($r->status === 'active' ? 'success' : 'secondary').'">'.$r->status.'</span>')
            ->addColumn('actions', fn (Route $r) => view('modules.transport._actions', ['type' => 'route', 'model' => $r])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function storeRoute(StoreRouteRequest $request): JsonResponse
    {
        return $this->jsonCreated('Route created successfully.', $this->service->createRoute($request->validated()));
    }

    public function showRoute(Route $route): JsonResponse
    {
        return $this->jsonData($route->load(['vehicle', 'driver']));
    }

    public function routeDetail(Route $route): JsonResponse
    {
        $route->load(['vehicle', 'driver', 'stops']);

        return response()->json([
            'success' => true,
            'data' => [
                'route' => $route,
                'pickup_order' => $route->stops,
                'drop_order' => $route->stops->sortByDesc('sequence')->values(),
            ],
        ]);
    }

    public function updateRoute(UpdateRouteRequest $request, Route $route): JsonResponse
    {
        return $this->jsonCreated('Route updated successfully.', $this->service->updateRoute($route, $request->validated()));
    }

    public function destroyRoute(Route $route): JsonResponse
    {
        $this->authorize('delete', $route);
        $route->delete();

        return $this->jsonMessage('Route deleted successfully.');
    }

    // ─── Route Stops ────────────────────────────────────────────────────────────

    public function routeStopsData(): JsonResponse
    {
        return DataTables::of($this->transport->routeStops())
            ->addColumn('route_name', fn (RouteStop $rs) => $rs->route?->route_name ?? '-')
            ->editColumn('pickup_time', fn (RouteStop $rs) => $rs->pickup_time ? date('H:i', strtotime($rs->pickup_time)) : '-')
            ->editColumn('drop_time', fn (RouteStop $rs) => $rs->drop_time ? date('H:i', strtotime($rs->drop_time)) : '-')
            ->addColumn('actions', fn (RouteStop $rs) => view('modules.transport._actions', ['type' => 'route-stop', 'model' => $rs])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function storeRouteStop(StoreRouteStopRequest $request): JsonResponse
    {
        return $this->jsonCreated('Route stop created successfully.', $this->service->createRouteStop($request->validated()));
    }

    public function showRouteStop(RouteStop $routeStop): JsonResponse
    {
        return $this->jsonData($routeStop->load('route'));
    }

    public function updateRouteStop(UpdateRouteStopRequest $request, RouteStop $routeStop): JsonResponse
    {
        return $this->jsonCreated('Route stop updated successfully.', $this->service->updateRouteStop($routeStop, $request->validated()));
    }

    public function destroyRouteStop(RouteStop $routeStop): JsonResponse
    {
        $this->authorize('delete', $routeStop);
        $routeStop->delete();

        return $this->jsonMessage('Route stop deleted successfully.');
    }

    // ─── Transport Assignments ──────────────────────────────────────────────────

    public function assignmentsData(): JsonResponse
    {
        return DataTables::of($this->transport->assignments())
            ->addColumn('student_name', fn (TransportAssignment $a) => $a->student?->full_name ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('route_name', fn (TransportAssignment $a) => $a->route?->route_name ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('stop_name', fn (TransportAssignment $a) => $a->stop?->stop_name ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('pickup_time', fn (TransportAssignment $a) => $a->stop?->pickup_time?->format('H:i') ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('drop_time', fn (TransportAssignment $a) => $a->stop?->drop_time?->format('H:i') ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('vehicle_name', fn (TransportAssignment $a) => $a->vehicle?->vehicle_number ?? '<span class="text-secondary">Not Assigned</span>')
            ->editColumn('monthly_fee', fn (TransportAssignment $a) => '<span class="text-end d-block">'.number_format((float) $a->monthly_fee, 2).'</span>')
            ->editColumn('status', fn (TransportAssignment $a) => '<span class="badge bg-'.($a->status === 'active' ? 'success' : 'secondary').'">'.$a->status.'</span>')
            ->addColumn('actions', fn (TransportAssignment $a) => view('modules.transport._actions', ['type' => 'assignment', 'model' => $a])->render())
            ->rawColumns(['student_name', 'route_name', 'stop_name', 'pickup_time', 'drop_time', 'vehicle_name', 'monthly_fee', 'status', 'actions'])
            ->toJson();
    }

    public function storeAssignment(StoreAssignmentRequest $request): JsonResponse
    {
        return $this->jsonCreated('Transport assignment created successfully.', $this->service->createAssignment($request->validated()));
    }

    public function showAssignment(TransportAssignment $assignment): JsonResponse
    {
        return $this->jsonData($assignment->load(['student', 'route', 'stop', 'vehicle']));
    }

    public function updateAssignment(UpdateAssignmentRequest $request, TransportAssignment $assignment): JsonResponse
    {
        return $this->jsonCreated('Transport assignment updated successfully.', $this->service->updateAssignment($assignment, $request->validated()));
    }

    public function destroyAssignment(TransportAssignment $assignment): JsonResponse
    {
        $this->authorize('delete', $assignment);
        $assignment->delete();

        return $this->jsonMessage('Transport assignment deleted successfully.');
    }

    // ─── Search (Select2 AJAX) ────────────────────────────────────────────────

    public function searchStudents(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $limit = min((int) $request->get('limit', 20), 50);

        $students = Student::query()
            ->where(function ($query) use ($q): void {
                $query->where('first_name', 'like', "%{$q}%")
                    ->orWhere('middle_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('admission_no', 'like', "%{$q}%");
            })
            ->orderBy('first_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $students->map(fn (Student $s) => [
                'id' => $s->id,
                'text' => sprintf('%s (%s)', $s->full_name, $s->admission_no),
            ]),
        ]);
    }

    public function searchRoutes(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $limit = min((int) $request->get('limit', 20), 50);

        $routes = Route::query()
            ->where('route_name', 'like', "%{$q}%")
            ->orWhere('start_point', 'like', "%{$q}%")
            ->orWhere('end_point', 'like', "%{$q}%")
            ->orderBy('route_name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $routes->map(fn (Route $r) => [
                'id' => $r->id,
                'text' => sprintf('%s (%s → %s)', $r->route_name, $r->start_point, $r->end_point),
            ]),
        ]);
    }

    // ─── Reports ────────────────────────────────────────────────────────────────

    public function reports()
    {
        return view('modules.transport.reports', [
            'vehicles' => Vehicle::query()->get(),
            'drivers' => Driver::query()->get(),
            'routes' => Route::query()->get(),
        ]);
    }

    // 1. Vehicle Report
    public function vehicleReportData(Request $request): JsonResponse
    {
        $query = Vehicle::query()->with('driver');

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('driver_name', fn (Vehicle $v) => $v->driver?->name ?? '-')
            ->editColumn('vehicle_type', fn (Vehicle $v) => str($v->vehicle_type)->replace('_', ' ')->headline())
            ->addColumn('occupancy', fn (Vehicle $v) => $v->assignments()->where('status', 'active')->count().' / '.$v->capacity)
            ->editColumn('status', fn (Vehicle $v) => '<span class="badge bg-'.($v->status === 'active' ? 'success' : 'secondary').'">'.$v->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    // 2. Driver Report
    public function driverReportData(Request $request): JsonResponse
    {
        $query = Driver::query()->withCount('vehicles');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->editColumn('license_expiry_date', fn (Driver $d) => $d->license_expiry_date?->format('d M Y'))
            ->addColumn('assigned_routes', fn (Driver $d) => $d->routes()->count())
            ->editColumn('status', fn (Driver $d) => '<span class="badge bg-'.($d->status === 'active' ? 'success' : 'secondary').'">'.$d->status.'</span>')
            ->rawColumns(['status'])
            ->toJson();
    }

    // 3. Route Report
    public function routeReportData(Request $request): JsonResponse
    {
        $query = Route::query()->with(['vehicle', 'driver'])->withCount(['stops', 'assignments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('vehicle_name', fn (Route $r) => $r->vehicle?->vehicle_number ?? '-')
            ->addColumn('driver_name', fn (Route $r) => $r->driver?->name ?? '-')
            ->editColumn('status', fn (Route $r) => '<span class="badge bg-'.($r->status === 'active' ? 'success' : 'secondary').'">'.$r->status.'</span>')
            ->addColumn('actions', fn (Route $r) => '<button class="btn btn-sm btn-outline-info view-route-flow" data-url="'.route('admin.transport.routes.detail', $r).'" title="View Route Flow"><i class="ti ti-map-route"></i></button>')
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    // 4. Route-wise Students
    public function routeStudentsData(Request $request): JsonResponse
    {
        $query = TransportAssignment::query()
            ->with(['student', 'route', 'stop', 'vehicle']);

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('student_name', fn (TransportAssignment $a) => $a->student?->full_name ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('route_name', fn (TransportAssignment $a) => $a->route?->route_name ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('stop_name', fn (TransportAssignment $a) => $a->stop?->stop_name ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('pickup_time', fn (TransportAssignment $a) => $a->stop?->pickup_time?->format('H:i') ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('drop_time', fn (TransportAssignment $a) => $a->stop?->drop_time?->format('H:i') ?? '<span class="text-secondary">Not Assigned</span>')
            ->addColumn('vehicle_name', fn (TransportAssignment $a) => $a->vehicle?->vehicle_number ?? '<span class="text-secondary">Not Assigned</span>')
            ->editColumn('monthly_fee', fn (TransportAssignment $a) => '<span class="text-end d-block">'.number_format((float) $a->monthly_fee, 2).'</span>')
            ->editColumn('status', fn (TransportAssignment $a) => '<span class="badge bg-'.($a->status === 'active' ? 'success' : 'secondary').'">'.$a->status.'</span>')
            ->rawColumns(['student_name', 'route_name', 'stop_name', 'pickup_time', 'drop_time', 'vehicle_name', 'monthly_fee', 'status'])
            ->toJson();
    }

    // 5. Vehicle Occupancy Report
    public function vehicleOccupancyData(Request $request): JsonResponse
    {
        $query = Vehicle::query()->withCount(['assignments' => fn ($q) => $q->where('status', 'active')]);

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        return DataTables::of($query)
            ->addColumn('occupancy_count', fn (Vehicle $v) => $v->assignments_count)
            ->addColumn('capacity_display', fn (Vehicle $v) => $v->capacity)
            ->addColumn('occupancy_pct', fn (Vehicle $v) => $v->capacity > 0 ? round(($v->assignments_count / $v->capacity) * 100).'%' : '0%')
            ->editColumn('vehicle_type', fn (Vehicle $v) => str($v->vehicle_type)->replace('_', ' ')->headline())
            ->toJson();
    }

    // 6. Transport Fee Report
    public function feeReportData(Request $request): JsonResponse
    {
        $query = TransportAssignment::query()
            ->with(['student', 'route'])
            ->selectRaw('route_id, COUNT(*) as student_count, SUM(monthly_fee) as total_fee')
            ->groupBy('route_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('route_name', fn ($row) => Route::find($row->route_id)?->route_name ?? '-')
            ->editColumn('total_fee', fn ($row) => number_format((float) ($row->total_fee ?? 0), 2))
            ->toJson();
    }

    // ─── Exports ────────────────────────────────────────────────────────────────

    public function exportExcel(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);

        return Excel::download(
            new TransportReportExport($data, $report),
            "transport_{$report}_".now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportPdf(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);
        $title = str($report)->replace('_', ' ')->headline().' Report';

        return Pdf::loadView('modules.transport.reports_pdf', compact('data', 'title', 'report'))
            ->setPaper('a4', 'landscape')
            ->download("transport_{$report}_".now()->format('Ymd_His').'.pdf');
    }

    public function printReport(Request $request, string $report)
    {
        $data = $this->getReportData($request, $report);
        $title = str($report)->replace('_', ' ')->headline().' Report';

        return view('modules.transport.reports_print', compact('data', 'title', 'report'));
    }

    private function getReportData(Request $request, string $report): array
    {
        return match ($report) {
            'vehicles' => Vehicle::query()->with('driver')
                ->when($request->filled('vehicle_type'), fn ($q) => $q->where('vehicle_type', $request->vehicle_type))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (Vehicle $v) => [
                    'vehicle_number' => $v->vehicle_number,
                    'vehicle_name' => $v->vehicle_name,
                    'vehicle_type' => str($v->vehicle_type)->replace('_', ' ')->headline(),
                    'capacity' => $v->capacity,
                    'driver' => $v->driver?->name ?? '-',
                    'attendant' => $v->attendant ?? '-',
                    'status' => $v->status,
                ])->toArray(),

            'drivers' => Driver::query()
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (Driver $d) => [
                    'name' => $d->name,
                    'mobile' => $d->mobile,
                    'license_number' => $d->license_number,
                    'license_expiry_date' => $d->license_expiry_date?->format('d M Y'),
                    'address' => $d->address ?? '-',
                    'status' => $d->status,
                ])->toArray(),

            'routes' => Route::query()->with(['vehicle', 'driver'])
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (Route $r) => [
                    'route_name' => $r->route_name,
                    'start_point' => $r->start_point,
                    'end_point' => $r->end_point,
                    'distance' => $r->distance ? $r->distance.' km' : '-',
                    'vehicle' => $r->vehicle?->vehicle_number ?? '-',
                    'driver' => $r->driver?->name ?? '-',
                    'stops' => $r->stops()->count(),
                    'status' => $r->status,
                ])->toArray(),

            'route_students' => TransportAssignment::query()->with(['student', 'route', 'stop', 'vehicle'])
                ->when($request->filled('route_id'), fn ($q) => $q->where('route_id', $request->route_id))
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->get()
                ->map(fn (TransportAssignment $a) => [
                    'student' => $a->student?->full_name ?? '-',
                    'route' => $a->route?->route_name ?? '-',
                    'stop' => $a->stop?->stop_name ?? '-',
                    'vehicle' => $a->vehicle?->vehicle_number ?? '-',
                    'monthly_fee' => number_format((float) $a->monthly_fee, 2),
                    'status' => $a->status,
                ])->toArray(),

            'vehicle_occupancy' => Vehicle::query()->withCount(['assignments' => fn ($q) => $q->where('status', 'active')])
                ->when($request->filled('vehicle_type'), fn ($q) => $q->where('vehicle_type', $request->vehicle_type))
                ->get()
                ->map(fn (Vehicle $v) => [
                    'vehicle_number' => $v->vehicle_number,
                    'vehicle_name' => $v->vehicle_name,
                    'vehicle_type' => str($v->vehicle_type)->replace('_', ' ')->headline(),
                    'capacity' => $v->capacity,
                    'assigned' => $v->assignments_count,
                    'available' => max(0, $v->capacity - $v->assignments_count),
                    'occupancy_pct' => $v->capacity > 0 ? round(($v->assignments_count / $v->capacity) * 100).'%' : '0%',
                ])->toArray(),

            'transport_fee' => TransportAssignment::query()->with('route')
                ->selectRaw('route_id, COUNT(*) as student_count, SUM(monthly_fee) as total_fee')
                ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
                ->groupBy('route_id')
                ->get()
                ->map(fn ($row) => [
                    'route' => Route::find($row->route_id)?->route_name ?? '-',
                    'students' => $row->student_count,
                    'total_fee' => number_format((float) ($row->total_fee ?? 0), 2),
                ])->toArray(),

            default => [],
        };
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function jsonCreated(string $message, mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    private function jsonData(mixed $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    private function jsonMessage(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message]);
    }
}
