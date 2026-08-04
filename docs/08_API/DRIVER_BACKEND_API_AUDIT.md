# DRIVER BACKEND API AUDIT

> Generated: 2026-06-29
> Scope: Driver Dashboard backend readiness assessment

---

## 1. Architecture Overview

| Aspect | Status | Detail |
|--------|--------|--------|
| **API Base** | ✅ `/api/v1/` | All routes under `prefix('v1')` |
| **Auth** | ✅ Sanctum | Bearer token, `expiration => null` |
| **School Tenant** | ✅ `SetSchoolContext` | `X-School-Id` header chain |
| **Permissions** | ✅ Spatie v6 | Team-scoped by `school_id` |
| **Response Format** | ✅ Unified | `{ success, message, data }` |
| **Push Notifications** | ✅ FCM | `PushNotificationService` with `sendToUser`, `sendToUsers`, `sendToTopic` |
| **Driver Model** | ✅ Exists | `App\Modules\Transport\Models\Driver` |
| **Vehicle Model** | ✅ Exists | `App\Modules\Transport\Models\Vehicle` (FK to Driver) |
| **Route Model** | ✅ Exists | `App\Modules\Transport\Models\Route` (FK to Driver, Vehicle) |
| **RouteStop Model** | ✅ Exists | `App\Modules\Transport\Models\RouteStop` (FK to Route) |
| **TransportAssignment** | ✅ Exists | `App\Modules\Transport\Models\TransportAssignment` (FK to Student, Route, RouteStop, Vehicle) |
| **VehicleLocation** | ✅ Exists | `App\Models\VehicleLocation` (FK to Vehicle) |
| **Trip Model** | ❌ **MISSING** | Only `TripStarted` / `TripCompleted` events exist |
| **Trips Table** | ❌ **MISSING** | No `trips` migration exists |
| **Driver App Controller** | ❌ **MISSING** | No driver-specific API controller |
| **Driver API Routes** | ❌ **MISSING** | No `api/v1/driver/*` routes |

---

## 2. Existing Transport-Related API Endpoints

### 2.1 Live Tracking API (`routes/modules/api/transport.php`)

| Method | URI | Controller | Auth | Permission | Description |
|--------|-----|------------|------|------------|-------------|
| POST | `/api/v1/transport/location` | `TransportRealtimeController@updateLocation` | Sanctum | — | Submit GPS location |
| GET | `/api/v1/transport/live` | `TransportRealtimeController@liveStatus` | Sanctum | — | Live dashboard (active/inactive vehicles) |
| GET | `/api/v1/transport/vehicle/{id}/location` | `TransportRealtimeController@vehicleLocation` | Sanctum | — | Location history for a vehicle |

### 2.2 Reusable General Endpoints

| Method | URI | Controller | Auth | Description |
|--------|-----|------------|------|-------------|
| POST | `/api/v1/auth/login` | `ApiAuthController@login` | Public | User login (email/password) |
| GET | `/api/v1/me` | `ApiAuthController@me` | Sanctum | Current user with roles/permissions |
| POST | `/api/v1/auth/refresh` | `ApiAuthController@refreshToken` | Sanctum | Refresh token |
| POST | `/api/v1/auth/logout` | `ApiAuthController@logout` | Sanctum | Logout |
| GET | `/api/v1/notifications` | `NotificationApiController@index` | Sanctum | Paginated notifications |
| GET | `/api/v1/notifications/unread` | `NotificationApiController@unread` | Sanctum | Unread notifications |
| POST | `/api/v1/notifications/{id}/read` | `NotificationApiController@markRead` | Sanctum | Mark notification read |
| POST | `/api/v1/notifications/read-all` | `NotificationApiController@markAllRead` | Sanctum | Mark all read |
| GET | `/api/v1/notifications/announcements` | `NotificationApiController@announcements` | Sanctum | Announcements |
| GET | `/api/v1/notifications/unread-count` | `DeviceController@unreadCount` | Sanctum | Unread count |
| POST | `/api/v1/devices/register` | `DeviceController@register` | Sanctum | Register FCM device token |
| POST | `/api/v1/devices/unregister` | `DeviceController@unregister` | Sanctum | Unregister device token |

### 2.3 Admin Web Transport Routes (NOT APIs — for reference only)

These are web routes (`routes/modules/transport.php`) with DataTables responses, NOT mobile-ready JSON APIs:

| Method | URI (admin prefix) | Description |
|--------|--------------------|-------------|
| GET | `/transport/vehicles/data` | Vehicles DataTable |
| POST | `/transport/vehicles` | Create vehicle |
| GET | `/transport/vehicles/{vehicle}` | Show vehicle |
| PUT | `/transport/vehicles/{vehicle}` | Update vehicle |
| DELETE | `/transport/vehicles/{vehicle}` | Delete vehicle |
| GET | `/transport/drivers/data` | Drivers DataTable |
| POST | `/transport/drivers` | Create driver |
| GET | `/transport/drivers/{driver}` | Show driver |
| PUT | `/transport/drivers/{driver}` | Update driver |
| DELETE | `/transport/drivers/{driver}` | Delete driver |
| GET | `/transport/routes/data` | Routes DataTable |
| POST | `/transport/routes` | Create route |
| GET | `/transport/routes/{route}` | Show route |
| PUT | `/transport/routes/{route}` | Update route |
| DELETE | `/transport/routes/{route}` | Delete route |
| GET | `/transport/routes/{route}/detail` | Route detail with stops |
| GET | `/transport/route-stops/data` | Route stops DataTable |
| GET | `/transport/assignments/data` | Assignments DataTable |
| GET | `/transport/search/students` | Select2 search |
| GET | `/transport/search/routes` | Select2 search |

---

## 3. Detailed Endpoint Documentation (Existing APIs Relevant to Driver App)

### 3.1 POST `/api/v1/transport/location`
```
Controller: TransportRealtimeController@updateLocation
Auth: Sanctum
Rate Limit: 60/min (general)
```

**Request Body:**
```json
{
  "vehicle_id": 1,
  "latitude": 28.6128,
  "longitude": 77.2295,
  "speed": 35.5,
  "heading": 180.0,
  "captured_at": "2026-06-29T10:30:00Z",
  "source": "driver_app"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Location updated successfully.",
  "data": {
    "location": {
      "id": 42,
      "vehicle_id": 1,
      "latitude": 28.6128,
      "longitude": 77.2295,
      "speed": 35.5,
      "heading": 180.0,
      "captured_at": "2026-06-29T10:30:00Z"
    }
  }
}
```

**Dispatches Event:** `LocationUpdated` with vehicleId, latitude, longitude, speed, heading, capturedAt.

### 3.2 GET `/api/v1/transport/vehicle/{id}/location`
```
Controller: TransportRealtimeController@vehicleLocation
Auth: Sanctum
```

**Query Parameters:** `from`, `to`, `limit` (max 100, default 50)

**Response (200):**
```json
{
  "success": true,
  "message": "Vehicle location retrieved.",
  "data": {
    "vehicle": {
      "id": 1,
      "vehicle_number": "DL-01-AB-1234",
      "vehicle_name": "Bus 1"
    },
    "current_location": {
      "latitude": 28.6128,
      "longitude": 77.2295,
      "speed": 35.5,
      "heading": 180.0,
      "captured_at": "2026-06-29T10:30:00Z"
    },
    "location_history": [...]
  }
}
```

### 3.3 GET `/api/v1/transport/live`
```
Controller: TransportRealtimeController@liveStatus
Auth: Sanctum
```

**Response (200):**
```json
{
  "success": true,
  "message": "Live transport status retrieved.",
  "data": {
    "summary": {
      "total_vehicles": 10,
      "active_vehicles": 5,
      "inactive_vehicles": 5,
      "trips_running": 4
    },
    "active_vehicles": [...],
    "inactive_vehicles": [...]
  }
}
```

### 3.4 POST `/api/v1/auth/login`
```
Controller: ApiAuthController@login
Auth: Public (throttled: 5/min)
```

**Request Body:**
```json
{
  "email": "driver@school.com",
  "password": "secret",
  "device_name": "driver-app-android"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "token": "1|abc123...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "name": "Rajesh Kumar",
      "email": "driver@school.com"
    },
    "school_id": 1
  }
}
```

**Note:** Driver users must exist as `User` records in the system. The existing login is role-agnostic and will work for drivers, but there is no driver-specific login response enrichment (no `driver_uuid`, `vehicle`, `route` data returned).

### 3.5 GET `/api/v1/me`
```
Controller: ApiAuthController@me
Auth: Sanctum
```

**Response (200):**
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "user": {
      "id": 1,
      "name": "Rajesh Kumar",
      "email": "driver@school.com"
    },
    "roles": ["Driver"],
    "permissions": ["transport.view", "transport.location.update"]
  }
}
```

---

## 4. Event Inventory

| Event | Dispatched By | Properties | Has Listener? |
|-------|---------------|------------|---------------|
| `LocationUpdated` | `TransportRealtimeController@updateLocation` | vehicleId, latitude, longitude, speed, heading, capturedAt | ❌ No (mentioned in LIVE_TRANSPORT_AUDIT.md but not implemented) |
| `TripStarted` | Not dispatched anywhere yet | vehicleId, routeId, startedAt | ❌ No |
| `TripCompleted` | Not dispatched anywhere yet | vehicleId, routeId, completedAt | ❌ No |
| `BusArriving` | Not dispatched anywhere yet | vehicleId, routeStopId, stopName, distanceMeters, estimatedMinutes | ❌ No |
| `BusArrived` | Not dispatched anywhere yet | vehicleId, routeStopId, stopName | ❌ No |

**All 5 events exist as event classes but:**
- Only `LocationUpdated` is actually dispatched (from the location update endpoint).
- The other 4 events (`TripStarted`, `TripCompleted`, `BusArriving`, `BusArrived`) have **no dispatch site** — they are defined but never fired.
- No listeners are registered for any of these events in `EventServiceProvider`.
- No broadcasting channels are configured (no WebSocket/Pusher/reverb setup).

---

## 5. Services Inventory

| Service | Status | Methods |
|---------|--------|---------|
| `EtaService` | ✅ Complete | `distanceBetween()`, `distanceToStop()`, `estimatedMinutes()`, `isWithinThreshold()` |
| `PushNotificationService` | ✅ Complete | `sendToUser()`, `sendToUsers()`, `sendToTopic()` |

`EtaService` uses Haversine formula. It is injected into `TransportRealtimeController` but only used for future proximity detection — not currently called in any endpoint.

---

## 6. Model Relationships (Driver-Facing)

```
Driver (id, name, mobile, license_number, license_expiry, status)
  ├── hasMany → Vehicle (id, vehicle_number, vehicle_name, vehicle_type, capacity, driver_id)
  └── hasMany → Route (id, route_name, start_point, end_point, vehicle_id, driver_id)
                  └── hasMany → RouteStop (id, route_id, stop_name, pickup_time, drop_time, sequence)
                                    └── hasMany → TransportAssignment (student_id, route_stop_id)
                                                      └── belongsTo → Student

VehicleLocation (id, vehicle_id, latitude, longitude, speed, heading, captured_at, source)
  └── belongsTo → Vehicle
```

**Key Insight:** A Driver can find their assigned Vehicle(s) via `$driver->vehicles`, their assigned Route(s) via `$driver->routes`, and students via `TransportAssignment` through the route and route stops.

---

## 7. Missing APIs — Driver App Features Not Supported

### 7.1 Authentication & Profile

| Feature | Status | Notes |
|---------|--------|-------|
| **Driver Login** | ❌ Missing | No driver-specific login; generic login works but returns no driver context |
| **Driver Profile GET** | ❌ Missing | No endpoint to fetch driver's own profile with vehicle/route |
| **Driver Profile PUT** | ❌ Missing | No endpoint to update profile |

### 7.2 Dashboard

| Feature | Status | Notes |
|---------|--------|-------|
| **Driver Dashboard** | ❌ Missing | No aggregated dashboard for driver (today's schedule, stats) |

### 7.3 Vehicle & Route

| Feature | Status | Notes |
|---------|--------|-------|
| **Assigned Vehicle** | ❌ Missing | No `GET /api/v1/driver/vehicle` endpoint |
| **Assigned Route** | ❌ Missing | No `GET /api/v1/driver/route` endpoint |
| **Route Detail with Stops** | ❌ Missing | Admin web has `routes/{route}/detail` but no API for drivers |

### 7.4 Trip Management

| Feature | Status | Notes |
|---------|--------|-------|
| **Today's Trips** | ❌ Missing | No Trip model/table/endpoint exists at all |
| **Trip Detail** | ❌ Missing | Depends on Trip model |
| **Trip Start** | ❌ Missing | Only event exists (never dispatched, no route) |
| **Trip Complete** | ❌ Missing | Only event exists (never dispatched, no route) |

### 7.5 Student Management

| Feature | Status | Notes |
|---------|--------|-------|
| **Student Pickup List** | ❌ Missing | Requires route → stops → assignments → students chain |
| **Student Drop List** | ❌ Missing | Same as above, by drop sequence |
| **Pickup Confirmation** | ❌ Missing | No endpoint; no pickup tracking table exists |
| **Drop Confirmation** | ❌ Missing | No endpoint; no drop tracking table exists |

### 7.6 Location & ETA

| Feature | Status | Notes |
|---------|--------|-------|
| **Live Location Update** | ✅ Exists | `POST /api/v1/transport/location` |
| **Vehicle Location (GET)** | ✅ Exists | `GET /api/v1/transport/vehicle/{id}/location` |
| **ETA Endpoint** | ❌ Missing | `EtaService` exists but no API endpoint exposes it |
| **ETA for Stops** | ❌ Missing | No endpoint to get ETA per stop on route |

### 7.7 Notifications & Emergency

| Feature | Status | Notes |
|---------|--------|-------|
| **Notifications List** | ✅ Exists | Reusable `GET /api/v1/notifications` |
| **Mark Read** | ✅ Exists | Reusable `POST /api/v1/notifications/{id}/read` |
| **Device Registration** | ✅ Exists | `POST /api/v1/devices/register` |
| **SOS / Emergency** | ❌ Missing | No endpoint for emergency alert |
| **Push Notification Triggers** | ⚠️ Partial | Service exists but no events trigger driver-specific pushes |

---

## 8. Realtime Capability Assessment

| Requirement | Status | Detail |
|------------|--------|--------|
| **GPS Update Endpoint** | ✅ Exists | `POST /api/v1/transport/location` |
| **Vehicle Location Endpoint** | ✅ Exists | `GET /api/v1/transport/vehicle/{id}/location` |
| **ETA Endpoint** | ❌ Missing | `EtaService` available, needs API exposure |
| **Events Dispatched** | ⚠️ Partial | Only `LocationUpdated` is dispatched |
| **Push Notifications** | ⚠️ Partial | Service exists but no listeners wired up |
| **WebSocket / Broadcasting** | ❌ Missing | No broadcasting channels configured |
| **Polling Fallback** | ⚠️ Partial | REST endpoints exist but no driver-specific live status |

**Architecture gap:** The current transport system is designed for **polling** (REST GET endpoints). No real-time WebSocket/Pusher/reverb configuration exists. Events are dispatched but not broadcast. The `LocationUpdated` event only uses `Dispatchable` — it does not implement `ShouldBroadcast`.

---

## 9. Database Requirements

### Existing Tables (usable by driver app)

| Table | Purpose | Ready? |
|-------|---------|--------|
| `drivers` | Driver records | ✅ |
| `vehicles` | Vehicle records (FK to driver) | ✅ |
| `routes` | Route records (FK to driver, vehicle) | ✅ |
| `route_stops` | Stops per route with pickup/drop times | ✅ |
| `transport_assignments` | Student ↔ Route ↔ Stop assignments | ✅ |
| `vehicle_locations` | GPS breadcrumbs | ✅ |
| `students` | Student records | ✅ |

### Missing Tables (needed for driver app)

| Table | Purpose | Priority |
|-------|---------|----------|
| `trips` | Trip instances (driver trip start/end per day per route) | Required |
| `trip_stops` | Per-stop tracking (arrived, departed, students picked/dropped) | Required |
| `pickup_records` | Individual student pickup confirmation log | Required |
| `drop_records` | Individual student drop confirmation log | Required |
| `emergency_alerts` | SOS/emergency alert records | Medium |

---

## 10. Recommended Implementation Order

| Phase | Feature | Effort | Depends On |
|-------|---------|--------|------------|
| **D5.1** | Trip model + migration + Trip CRUD API | 3 days | Existing transport schema |
| **D5.2** | Driver App Controller + routes (profile, vehicle, route, dashboard) | 2 days | D5.1 |
| **D5.3** | Trip lifecycle: Start / Complete + stop arrival events | 2 days | D5.1 |
| **D5.4** | Student pickup/drop records model + API | 3 days | D5.1 |
| **D5.5** | Driver-specific ETA endpoint (uses existing EtaService) | 1 day | D5.1 |
| **D5.6** | SOS/emergency endpoint | 1 day | — |
| **D5.7** | WebSocket broadcasting for live location (Pusher/reverb) | 2 days | D5.1 |
| **D5.8** | Wire up event listeners for push notifications | 1 day | Existing PushNotificationService |
| **D5.9** | Driver app login enrichment (return driver profile data) | 0.5 day | — |

**Total estimated effort: ~15.5 days**

---

## 11. Frontend Readiness Assessment

| Component | Backend Readiness | Notes |
|-----------|-------------------|-------|
| **Login Screen** | ⚠️ Partial | Generic `/auth/login` works; needs driver context in response |
| **Dashboard** | ❌ Not Ready | Needs `GET /api/v1/driver/dashboard` |
| **Profile** | ❌ Not Ready | Needs `GET /api/v1/driver/profile` |
| **Assigned Vehicle** | ❌ Not Ready | Needs `GET /api/v1/driver/vehicle` |
| **Assigned Route** | ❌ Not Ready | Needs `GET /api/v1/driver/route` + stops |
| **Today's Trips** | ❌ Not Ready | Needs Trip model + `GET /api/v1/driver/trips` |
| **Trip Detail** | ❌ Not Ready | Needs `GET /api/v1/driver/trips/{id}` |
| **Student Pickup List** | ❌ Not Ready | Needs trip stops + assignments chain |
| **Student Drop List** | ❌ Not Ready | Same as above |
| **Pickup Confirmation** | ❌ Not Ready | Needs `POST /api/v1/driver/trips/{id}/pickup/{studentId}` |
| **Drop Confirmation** | ❌ Not Ready | Needs `POST /api/v1/driver/trips/{id}/drop/{studentId}` |
| **Live Map** | ⚠️ Partial | GPS update endpoint exists; needs WebSocket for real-time |
| **ETA Display** | ❌ Not Ready | `EtaService` exists but no API |
| **Notifications** | ⚠️ Partial | Generic endpoints work; push triggers need wiring |
| **SOS / Emergency** | ❌ Not Ready | No endpoint exists |
| **Trip Start / Complete** | ❌ Not Ready | Events exist but no API |

**Summary:** Only **Live Location Update** and **Notifications** are backend-ready today. All other features require new backend development before the frontend can be built.

---

## 12. API Contract (API_CONTRACT.md)

**No API_CONTRACT.md exists in the project.** After backend implementation, the contract should be created documenting all Driver API endpoints following the same pattern as the existing APIs documented in this audit.

---

## Appendix A: Key Files Referenced

| File | Path |
|------|------|
| Transport Realtime API Routes | `routes/modules/api/transport.php` |
| API Route Aggregator | `routes/modules/api.php` |
| Main API Entry | `routes/api.php` |
| Auth API Routes | `routes/modules/api/auth.php` |
| Notifications API Routes | `routes/modules/api/notifications.php` |
| Transport Web Routes | `routes/modules/transport.php` |
| TransportRealtimeController | `app/Http/Controllers/Api/V1/TransportRealtimeController.php` |
| ApiBaseController | `app/Http/Controllers/Api/V1/ApiBaseController.php` |
| ApiAuthController | `app/Modules/Auth/Controllers/ApiAuthController.php` |
| Driver Model | `app/Modules/Transport/Models/Driver.php` |
| Vehicle Model | `app/Modules/Transport/Models/Vehicle.php` |
| Route Model | `app/Modules/Transport/Models/Route.php` |
| RouteStop Model | `app/Modules/Transport/Models/RouteStop.php` |
| TransportAssignment Model | `app/Modules/Transport/Models/TransportAssignment.php` |
| VehicleLocation Model | `app/Models/VehicleLocation.php` |
| TransportController (Web) | `app/Modules/Transport/Controllers/TransportController.php` |
| EtaService | `app/Services/EtaService.php` |
| PushNotificationService | `app/Services/PushNotificationService.php` |
| LocationUpdated Event | `app/Events/LocationUpdated.php` |
| TripStarted Event | `app/Events/TripStarted.php` |
| TripCompleted Event | `app/Events/TripCompleted.php` |
| BusArriving Event | `app/Events/BusArriving.php` |
| BusArrived Event | `app/Events/BusArrived.php` |
| Transport Migration | `database/migrations/2026_06_18_000001_create_transportation_tables.php` |
| Vehicle Locations Migration | `database/migrations/2026_06_23_000002_create_vehicle_locations_table.php` |
| Existing Audit (Transport) | `docs/api/LIVE_TRANSPORT_AUDIT.md` |
| Existing Audit (Mobile) | `docs/api/MOBILE_API_AUDIT.md` |
| API Roadmap | `docs/api/PHASE5_API_ROADMAP.md` |
