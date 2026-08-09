# DRIVER API DOCS — Developer Guide

> Generated: 2026-08-07
> Companion files: `DRIVER_API_CONTRACT.md`, `DRIVER_FEATURE_MAP.md`, `DRIVER_API_COVERAGE.md`, `driver-openapi.yaml`, `driver-postman-collection.json`

---

## 1. Architecture

```
Driver App (mobile) ──HTTPS/JSON──▶ /api/v1/driver/*    (Sanctum Bearer)
                                         │
                    ┌────────────────────┼──────────────────────┐
                    ▼                    ▼                      ▼
       DriverApiController (ApiBaseController)
                    │
                    ▼
            DriverApiService ──────▶ DriverApiRepository ──▶ DB (trips, trip_students,
                    │                                              trip_events, vehicle_locations)
                    │
                    ├──▶ TripService ──▶ TripStarted / TripCompleted events
                    ├──▶ DriverDashboardService
                    ├──▶ EtaService
                    └──▶ NotificationService
```

### Layers
- **Routes**: `routes/modules/api/driver.php` (aggregated in `routes/modules/api.php`)
- **Controller**: `app/Http/Controllers/Api/V1/DriverApiController.php`
- **Service**: `app/Modules/Driver/Services/DriverApiService.php`
- **Repository**: `app/Modules/Driver/Repositories/DriverApiRepository.php` (+ interface)
- **Requests**: `app/Modules/Driver/Requests/*`
- **Resources**: `app/Http/Resources/Api/V1/Driver*.php`
- **Base responses**: `ApiBaseController` (`success`, `error`, `paginated`, `created`, `noContent`, `forbidden`, `notFound`)

---

## 2. Authentication & Middleware

- **Route middleware chain** (`routes/modules/api.php`): `auth:sanctum` → `school` (tenant) → `throttle:60,1`.
- Driver endpoints additionally assert role + permission in `FormRequest::authorize()` and/or `DriverApiService::resolveDriverForUser()`.
- **Role**: `Driver` across the resolved school. **Permissions**:
  - Read: `transport.view`
  - Write: `transport.update`
- Tenant resolution: `X-School-Id` header → `school_id` input → `user.current_school_id` → primary school. Falls back with 403 if unresolvable.

---

## 3. Adding a New Endpoint (convention)

1. Add request class in `app/Modules/Driver/Requests/` (role + permission in `authorize`, validation in `rules`).
2. Add a service method in `DriverApiService` (handle ownership + state guards).
3. If DB scoping needed, add a repository method + interface entry.
4. Add controller method delegate: `return $this->success($this->service->methodAccess(...), 'Message.');`
5. Register the route in `routes/modules/api/driver.php` under `prefix('driver')`.
6. Add feature test in `tests/Feature/DriverApi*.php`.
7. Update `DRIVER_API_COVERAGE.md` + OpenAPI.

---

## 4. Trip State Machine

`Trip.status` enum: `scheduled | in_progress | completed | cancelled`.

```
 scheduled ──tripService.startTrip──▶ in_progress ──tripService.completeTrip──▶ completed
```
- Start allowed **only** from `scheduled` (422 otherwise).
- End/complete allowed **only** from `in_progress` (422 otherwise).
- Attendance & stop-flow allowed **only** while `in_progress`.
- Ownership: every trip must belong to the authenticated driver (`ensureDriverOwnsTrip` → 403 otherwise).

If you extend the machine (e.g., `cancelled`), centralize transitions in `TripService` and mirror the guard in the service write-methods.

---

## 5. Attendance Idempotency (Offline / Retry)

`DriverApiService::markAction()`:
- If the requested status is already set → return current state (HTTP 200), **no** increment, **no** duplicate `TripEvent`.
- Only first-mark increments `picked_up_count`/`dropped_off_count` and writes the event.
- `request_id` is accepted (stored) for client-side dedupe; replaying the same payload is a no-op success.

Migration dependancy: `picked_up_count`/`dropped_off_count` live on `trips`; `TripStudent` carries `pickup_status`/`drop_status` + geotags.

---

## 6. Stop Flow

`arrive-stop`/`leave-stop` append `TripEvent` rows (`event_type` = `stop_arrived` / `stop_left`) with stop metadata. They are naturally idempotent (append-only). To "re-arrive" would just append another event; there is no increment to double-count.

`route_stops.latitude` / `route_stops.longitude` were added by migration `2026_08_07_000001_add_lat_lng_to_route_stops` to satisfy the stop lat/lng contract and ETA proximity.

---

## 7. Testing

```bash
php artisan test --filter='DriverApiTest'           # legacy suite (26 tests)
php artisan test --filter='DriverApiExtendedTest'   # new endpoints (23 tests)
```
Both use `RefreshDatabase` (sqlite :memory:). Note: idempotent attendance uses `request_id` + in-progress guard; ownership checks expect 403.

---

## 8. Realtime & Events

| Event | Trigger |
|-------|---------|
| `App\Events\TripStarted` | `POST /driver/trips/start` (service via `TripService::startTrip`) |
| `App\Events\TripCompleted` | `POST /driver/trips/{trip}/end` / `complete` |
| `App\Events\LocationUpdated` | `POST /driver/location` |
| `App\Events\BusArriving` / `BusArrived` | parked in `TripService` for proximity triggers |

Events are foundation for push/websocket wiring (see `LIVE_TRANSPORT_AUDIT.md`).