# Transport Assignment — Data Flow Analysis

## 1. Database Schema (`transport_assignments`)

| Column | Type | Nullable | FK |
|---|---|---|---|
| `id` | bigint, PK | NO | — |
| `school_id` | bigint | NO | `schools.id` |
| `student_id` | bigint | NO | `students.id` (cascade delete) |
| `route_id` | bigint | YES | `routes.id` (null on delete) |
| `route_stop_id` | bigint | YES | `route_stops.id` (null on delete) |
| `vehicle_id` | bigint | YES | `vehicles.id` (null on delete) |
| `pickup_point` | varchar(255) | YES | — |
| `monthly_fee` | decimal(10,2) | defaults 0 | — |
| `status` | varchar(30) | defaults 'active' | — |

Unique constraint: `(school_id, student_id)` — one active assignment per student per school.

## 2. Relationships (TransportAssignment model)

| Accessor | Type | FK | Target Model |
|---|---|---|---|
| `student()` | BelongsTo | `student_id` | `Student` |
| `route()` | BelongsTo | `route_id` | `Route` |
| `stop()` | BelongsTo | `route_stop_id` | `RouteStop` |
| `vehicle()` | BelongsTo | `vehicle_id` | `Vehicle` |

## 3. Data Flow: Form → DB → DataTable

### 3a. Create/Edit Form

The modal `#assignmentModal` (`index.blade.php:237-252`) renders these fields:

| name | type | Always present? |
|---|---|---|
| `student_id` | select (required) | YES |
| `route_id` | select | YES |
| `pickup_point` | text input | YES |
| `monthly_fee` | number input | YES |
| `status` | select (required) | YES |
| **`route_stop_id`** | **— MISSING —** | **NO ⟶ never sent** |
| **`vehicle_id`** | **— MISSING —** | **NO ⟶ never sent** |

### 3b. Validation (StoreAssignmentRequest / UpdateAssignmentRequest)

Both already accept `route_stop_id` and `vehicle_id` as `nullable|integer|exists`.

### 3c. Save

`TransportService::createAssignment()` / `updateAssignment()` calls `TransportRepository->createAssignment($data)` / `->updateAssignment(...)`, which pass the validated array directly to Eloquent `create()` / `fill()->save()`.

Because `route_stop_id` and `vehicle_id` are absent from the form submission, they are **not present in `$request->validated()`** and are **stored as `NULL`** in the database.

### 3d. DataTable Read

`TransportController::assignmentsData()` uses `TransportRepository::assignments()` which is:

```php
TransportAssignment::query()->with(['student', 'route', 'stop', 'vehicle'])->latest()
```

Then maps columns:

| DataTable column | Source | Expression | Null-safe? |
|---|---|---|---|
| `student_name` | `$a->student->full_name` | `$a->student?->full_name ?? '-'` | Yes |
| `route_name` | `$a->route->route_name` | `$a->route?->route_name ?? '-'` | Yes |
| `stop_name` | `$a->stop->stop_name` | `$a->stop?->stop_name ?? '-'` | Yes |
| `pickup_time` | `$a->stop->pickup_time` | `$a->stop?->pickup_time?->format('H:i') ?? '-'` | Yes |
| `drop_time` | `$a->stop->drop_time` | `$a->stop?->drop_time?->format('H:i') ?? '-'` | Yes |
| `vehicle_name` | `$a->vehicle->vehicle_number` | `$a->vehicle?->vehicle_number ?? '-'` | Yes |

Eager loading is correct. The null-safe operator correctly outputs `-` when the related model is `null`.

## 4. Root-Cause Analysis

| Question | Answer |
|---|---|
| **A. Are these columns intentionally optional?** | The DB schema marks `route_stop_id` and `vehicle_id` as nullable, so technically yes. However, the form allows selecting a Route without selecting its stops or vehicle, making the UX incomplete. |
| **B. Is data not being saved?** | **YES.** `route_stop_id` and `vehicle_id` are never sent from the browser because the form has no corresponding fields. They are stored as `NULL`. |
| **C. Is data saved but not loaded?** | No. The DataTable code is correct — eager loading and null-safe access work properly. If data were saved, it would display. |
| **D. Are relationships broken?** | No. Every relationship (`stop()`, `vehicle()`, `route()`, `student()`) is correctly defined with the right FK and target model. |

## 5. Fix Needed

Add `route_stop_id` and `vehicle_id` select fields to the assignment form modal. The `route_stop_id` dropdown should be filtered dynamically based on the selected `route_id`. The `vehicle_id` should auto-populate from the route's `vehicle_id` but remain overridable.

No schema or model changes required — the DB columns and relationships already exist.
