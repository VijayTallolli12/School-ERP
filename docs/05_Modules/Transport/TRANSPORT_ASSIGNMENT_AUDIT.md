# Transport Assignment — Data Integrity & UI Audit

## 1. Root Cause Analysis

### Question A: Is data being saved?
**Partially.** The `transport_assignments` table stores `student_id`, `route_id`, `pickup_point`, `monthly_fee`, and `status` correctly. However, `route_stop_id` and `vehicle_id` were **never saved** because the assignment form had **no corresponding input fields**. They were not present in the form submission, thus not in `$request->validated()`, and stored as `NULL`.

### Question B: Is data saved but not loaded?
**No.** Once data is saved (i.e., values exist in the DB columns), the DataTable loads it correctly. The eager loading (`->with(['student', 'route', 'stop', 'vehicle'])`) is present and correct. The null-safe operator (`$a->stop?->stop_name`) returns `null` for missing relations, which displayed as `-`.

### Question C: Are relationships missing?
**No.** All four relationships are defined in `TransportAssignment`:
- `student()` → `belongsTo(Student::class)` — FK: `student_id`
- `route()` → `belongsTo(Route::class)` — FK: `route_id`
- `stop()` → `belongsTo(RouteStop::class, 'route_stop_id')` — FK: `route_stop_id`
- `vehicle()` → `belongsTo(Vehicle::class)` — FK: `vehicle_id`

### Question D: Are repository joins incomplete?
**No.** `TransportRepository::assignments()` eagerly loads all four relations:
```php
TransportAssignment::query()
    ->with(['student', 'route', 'stop', 'vehicle'])
    ->latest();
```
No N+1 queries.

### Question E: Are columns present but never populated?
**Yes.** The `route_stop_id` and `vehicle_id` columns exist in the DB schema but are **always `NULL`** because the form had no fields for them. This is the sole root cause.

---

## 2. Files Changed

| File | Change |
|---|---|
| `app/Modules/Transport/Controllers/TransportController.php` | `assignmentsData()`: changed `'-'` → `'Not Assigned'` (with `<span class="text-secondary">`); added status badge; fee right-aligned. Added `rawColumns` for all HTML columns. |
| `app/Modules/Transport/Controllers/TransportController.php` | `routeStudentsData()`: same empty-data treatment, added pickup_time/drop_time columns, status badge. |
| `app/Modules/Transport/Controllers/TransportController.php` | `vehiclesData()`: added status badge. |
| `app/Modules/Transport/Controllers/TransportController.php` | `driversData()`: added status badge. |
| `app/Modules/Transport/Controllers/TransportController.php` | `routesData()`: added status badge. |
| `app/Modules/Transport/Controllers/TransportController.php` | `vehicleReportData()`: added status badge. |
| `app/Modules/Transport/Controllers/TransportController.php` | `driverReportData()`: added status badge. |
| `app/Modules/Transport/Controllers/TransportController.php` | `routeReportData()`: added status badge. |
| `resources/views/modules/transport/index.blade.php` | Assignment form: added `route_stop_id` select, `vehicle_id` select, pickup/drop time display fields. JS: `populateAssignmentStops()`, `setAssignmentVehicle()`, `setAssignmentTimes()`, route/stop change handlers. Edit handler restores stops/times. Open modal resets stops/times/vehicle. DataTable column config: `className: 'text-end'` for fee, widths, `orderable: false` for related columns. Table header widths. |
| `resources/views/modules/transport/reports.blade.php` | Route-wise Students table: added Pickup Time, Drop Time columns. JS: added columns to DataTable. RouteFlowModal: wrapped in `@push('modals')`. |

---

## 3. Queries Fixed

### Data Integrity Fixes (no query changes needed)

The **repository** was already correct:
```php
public function assignments(): Builder
{
    return TransportAssignment::query()
        ->with(['student', 'route', 'stop', 'vehicle'])
        ->latest();
}
```

The **controller** was already correct:
```php
$assignment->load(['student', 'route', 'stop', 'vehicle']);
```

The **form request** was already correct:
```php
'route_stop_id' => ['nullable', 'integer', 'exists:route_stops,id'],
'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
```

The only gap was the **form UI** not sending these fields.

### Empty-data display changed

Before: `$a->stop?->stop_name ?? '-'`
After: `$a->stop?->stop_name ?? '<span class="text-secondary">Not Assigned</span>'`

This applies to: `assignmentsData()`, `routeStudentsData()`, and the export method `getReportData()`.

---

## 4. Relationships Verified

| Entity | Relation | FK | Target | Eager-loaded? |
|---|---|---|---|---|
| `TransportAssignment` | `stop()` | `route_stop_id` | `RouteStop` | Yes (`->with('stop')`) |
| `TransportAssignment` | `vehicle()` | `vehicle_id` | `Vehicle` | Yes (`->with('vehicle')`) |
| `TransportAssignment` | `route()` | `route_id` | `Route` | Yes (`->with('route')`) |
| `TransportAssignment` | `student()` | `student_id` | `Student` | Yes (`->with('student')`) |
| `Route` | `stops()` | `route_id` | `RouteStop` | Yes (in routeDetail) |
| `Route` | `vehicle()` | `vehicle_id` | `Vehicle` | Yes (in routes query) |
| `Route` | `driver()` | `driver_id` | `Driver` | Yes (in routes query) |

No N+1 queries detected. All repository methods use eager loading.

---

## 5. Screens Validated

| Tab | CRUD | Filters | DataTable | Exports | Status Badges | Responsive |
|---|---|---|---|---|---|---|
| Vehicles | ✓ | N/A | ✓ | ✓ (reports) | ✓ | ✓ (`responsive: true`) |
| Drivers | ✓ | N/A | ✓ | ✓ (reports) | ✓ | ✓ |
| Routes | ✓ | N/A | ✓ | ✓ (reports) | ✓ | ✓ |
| Route Stops | ✓ | N/A | ✓ | N/A | N/A | ✓ |
| Assignments | ✓ | N/A | ✓ | ✓ (reports) | ✓ | ✓ |
| Reports (6 tabs) | N/A | ✓ all | ✓ all | ✓ Excel/PDF/Print | ✓ | ✓ |

---

## 6. Remaining Issues (if any)

- **Existing records**: Assignments created before the fix have `route_stop_id = NULL` and `vehicle_id = NULL`. These will display `<span class="text-secondary">Not Assigned</span>` for those columns. Users should edit these records to assign the correct stop and vehicle.
- **Route Stop times**: `pickup_time` and `drop_time` on the form are read-only display fields (reflecting the selected stop's times). They are not stored on the assignment itself — they come from the `route_stop` relationship. This is by design.
- **No validation that route has stops**: The form allows selecting a route with no stops. The stop dropdown will show "No stops available". No backend validation enforces that a route must have stops before assignment. This is acceptable for now — the form guide the user.
- **No backfill migration**: No automatic migration fills `route_stop_id`/`vehicle_id` for existing records. Manual edit is required.

---

## 7. End-to-End Data Flow

```
Form fields (assignmentModal)
├── student_id (select)       ──┐
├── route_id (select)         ──┤
├── route_stop_id (select)    ──┤  → POST /admin/transport/assignments
├── vehicle_id (select)       ──┤     StoreAssignmentRequest validates
├── pickup_point (text)       ──┤     TransportService::createAssignment()
├── monthly_fee (number)      ──┤     TransportRepository::createAssignment()
└── status (select)           ──┘     TransportAssignment::create($data)
                                      ↓
                              transport_assignments table
                              ├── route_stop_id = 5  (FK → route_stops.id)
                              ├── vehicle_id = 3     (FK → vehicles.id)
                              └── ... 
                                      ↓
                              GET /admin/transport/assignments/data
                              TransportRepository::assignments()
                              → with(['student','route','stop','vehicle'])
                                      ↓
                              DataTable columns:
                              stop_name    ← $a->stop->stop_name
                              pickup_time  ← $a->stop->pickup_time
                              drop_time    ← $a->stop->drop_time
                              vehicle_name ← $a->vehicle->vehicle_number
                              monthly_fee  ← $a->monthly_fee (right-aligned)
                              status       ← badge (green=active, gray=inactive)
```

---

## 8. Production Readiness

All five transport tabs have been verified for:
- Create, Read, Update, Delete operations
- DataTable server-side processing with responsive layout
- Filter buttons (reports)
- Excel/PDF/Print exports (reports)
- Status badges (green/gray) for all status columns
- "Not Assigned" text instead of bare `-` for missing optional data
- Eager loading throughout — no N+1
- Form auto-population of stops, vehicle, and times

**Status: Production-ready** for the Payroll phase.
