# DRIVER API CONTRACT

> Generated: 2026-08-07
> Base URL: `/api/v1`
> Auth: Sanctum Personal Access Token — `Authorization: Bearer <token>`
> Tenant: `X-School-Id` header or `school_id` param (fallback: primary school)

---

## 1. Response Envelope (Standardized)

Every endpoint returns the exact same shape:

```json
{
  "success": true,
  "message": "Human readable status.",
  "data": {}
}
```

- **Error** responses replace `success: false` and may add an `errors` object (validation) or use HTTP status semantics.
- No inconsistent keys. No mixed structures.
- DateTime values are ISO-8601 strings. `null` when not yet set.

### Shared status codes

| Code | Meaning |
|------|---------|
| 200 | OK |
| 401 | Unauthenticated (missing/invalid token) |
| 403 | Forbidden (wrong role / not owner of resource) |
| 422 | Validation or invalid state transition |
| 404 | Resource not found |

---

## 2. Auth

### 2.1 POST `/driver/login`
Public, throttled `5/min`.

**Body:**
```json
{ "email": "driver@school.com", "password": "secret", "device_name": "driver-app", "school_id": 1 }
```
`school_id` optional if `X-School-Id` header or a primary school is resolvable.

**200:**
```json
{
  "success": true,
  "message": "Driver logged in successfully.",
  "data": {
    "token": "1|abc...",
    "token_type": "Bearer",
    "user": { "id": 1, "name": "Rajesh", "email": "driver@school.com" },
    "school_id": 1,
    "driver": {
      "id": 1, "name": "Rajesh", "mobile": "9...",
      "vehicle": { "id": 1, "vehicle_number": "DL-01", "vehicle_name": "Bus 1" },
      "routes": [{ "id": 1, "route_name": "Route A" }]
    }
  }
}
```

### 2.2 POST `/api/v1/driver/logout`
Authenticated. Revokes the current token.

**200:** `{ "success": true, "message": "Driver logged out successfully.", "data": null }`

### 2.3 GET `/api/v1/driver/me`
Authenticated. Returns driver profile + assigned vehicle + route.

**200 data:** `{ "driver": {...}, "vehicle": {...}, "route": {...} }`

---

## 3. Dashboard

### 3.1 GET `/api/v1/driver/dashboard`
Authenticated (`transport.view`).

**200:**
```json
{
  "success": true,
  "message": "Driver dashboard retrieved.",
  "data": {
    "summary": {
      "total_trips_today": 2,
      "completed_trips": 0,
      "active_trip": 1,
      "total_students_today": 24,
      "total_picked_up": 6,
      "total_dropped_off": 0
    },
    "vehicle": { "id": 1, "vehicle_number": "DL-01-AB-1234", "vehicle_name": "Bus 1", "vehicle_type": "bus", "capacity": 40 },
    "routes": [{ "id": 1, "route_name": "Route A", "start_point": "School", "end_point": "City", "distance": "12.50", "stops_count": 8 }],
    "route_stops_count": 8,
    "today_trips": [{ "id": 1, "type": "both", "status": "in_progress", "route_name": "Route A", "vehicle_number": "DL-01-AB-1234", "total_students": 24, "picked_up": 6, "dropped_off": 0, "started_at": "...", "completed_at": null }]
  }
}
```

---

## 4. Route

### 4.1 GET `/api/v1/driver/routes/today`
Authenticated. Routes with today's trip state.

**200:** `{ "routes": [{ "route_id": 1, "route_name": "...", "start_point": "...", "end_point": "...", "distance": "12.50", "stops_count": 8, "today_trips": [] }] }`

### 4.2 GET `/api/v1/driver/routes/{route}`
Route detail with ordered stops and per-stop student counts.

### 4.3 GET `/api/v1/driver/routes/{route}/stops`
**200:**
```json
{ "route_id": 1, "route_name": "Route A",
  "stops": [{ "stop_id": 1, "stop_name": "Stop 1", "latitude": "28.6128000", "longitude": "77.2295000", "sequence": 1, "pickup_time": "07:30", "drop_time": "15:00", "students_count": 3 }] }
```

### 4.4 GET `/api/v1/driver/routes/{route}/students`
**200:**
```json
{ "route_id": 1, "route_name": "Route A",
  "students": [{ "student_id": 10, "name": "Rahul Sharma", "class": "9", "stop_id": 1, "stop_name": "Stop 1", "stop_sequence": 1 }] }
```

---

## 5. Trip Control — State Machine

```
scheduled (NOT_STARTED)  ──start──▶  in_progress  ──end──▶  completed
```
| Transition | Allowed From | Guard |
|-----------|--------------|-------|
| start | `scheduled` only | Cannot start twice / from in_progress |
| end/complete | `in_progress` only | Cannot end without starting |
| attend/stop | `in_progress` only | Cannot mark before trip start |

### 5.1 POST `/api/v1/driver/trips/start`
Authenticated (`transport.update`). Body: `{ "trip_id": 1 }`

**200:** `{ "data": { "trip": { "id": 1, "status": "in_progress", "started_at": "..." } } }`
**422:** if trip not `scheduled`.

### 5.2 POST `/api/v1/driver/trips/{trip}/end`
**200:** `{ "data": { "trip": { "id": 1, "status": "completed", "completed_at": "..." } } }`
**422:** if not `in_progress`.

### 5.3 GET `/api/v1/driver/trips/current`
Latest scheduled/in-progress trip with stops + students.
**200:** `{ "data": { "has_current_trip": true, "trip": {...}, "route": {...}, "vehicle": {...}, "stops": [...] } }` (or `has_current_trip: false, trip: null`)

### 5.4 GET `/api/v1/driver/trips/today`
**200:** `{ "data": { "trips": [{ "id":1, "type":"both", "status":"scheduled", "route_name":"...", "total_students": 24, "picked_up_count": 6, ... }] } }`

### 5.5 GET `/api/v1/driver/trips/{trip}/students`
**200:** `{ "data": { "pickup_order": [...], "drop_order": [...] } }`

### 5.6 GET `/api/v1/driver/trips/history?from=&to=&per_page=`
**200:** `{ "data": { "trips": [...], "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 10 } } }`

---

## 6. Attendance — Indexed-Point, Duplicate-Safe

The modern attendance endpoints are **idempotent**: re-sending the same (student, action) returns the current state (HTTP 200) and never double-increments counts. An optional `request_id` enables client dedupe / offline retry.

### 6.1 POST `/api/v1/driver/trips/{trip}/attendance`
**Body:**
```json
{ "student_id": 10, "action": "pickup", "latitude": 28.6128, "longitude": 77.2295, "request_id": "uuid-456" }
```
(`action`: `pickup` | `drop`)

**200:**
```json
{ "data": { "trip_student": { "id": 42, "student_id": 10, "student_name": "Rahul Sharma", "pickup_status": "picked_up", "drop_status": "pending", "picked_up_at": "...", "dropped_off_at": null } } }
```

### 6.2 PUT `/api/v1/driver/trips/{trip}/attendance/{trip_student}`
Same schema, targets a known `trip_students` row.

**Guard rules:**
- Requires trip `in_progress`.
- Student must belong to the trip; otherwise 422 or 403 (ownership).
- Duplicate matching status → returns current state (200), no double increment.

---

## 7. Stop Flow

### 7.1 POST `/api/v1/driver/trips/{trip}/arrive-stop`
Body: `{ "route_stop_id": 1, "latitude": … , "longitude": …, "request_id": … }`

**200:**
```json
{ "data": { "stop": {"stop_id": 1, "stop_name": "Stop 1", "sequence": 1, "latitude": 28.61, "longitude": 77.22}, "event": "stop_arrived", "recorded_at": "...", "students": [...] } }
```
Writes a `TripEvent` of type `stop_arrived`. Idempotent (append-only).

### 7.2 POST `/api/v1/driver/trips/{trip}/leave-stop`
Same shape; event type `stop_left`.

---

## 8. Notifications

### 8.1 GET `/api/v1/driver/notifications`
**200:**
```json
{ "data": { "unread_count": 3, "notifications": [{ "id": 5, "title": "Trip delayed", "message": "...", "type": "transport", "priority": "high", "is_read": false, "sent_at": "..." }] } }
```

### 8.2 POST `/api/v1/driver/notifications/read`
**Body:** `{ "ids": [1,2] }` OR `{ "read_all": true }` (default `true` when ids empty)
**200:** `{ "data": { "unread_count": 0 } }`

---

## 9. Location & Emergency

### 9.1 POST `/api/v1/driver/location`
Body: `{ "vehicle_id": 1, "latitude": …, "longitude": …, "speed": 35.5, "heading": 180, "captured_at": "...", "trip_id": 1 }`
**200:** `{ "data": { "location": { "id": 42, "vehicle_id": 1, "latitude": …, "longitude": …, "speed": …, "heading": …, "captured_at": "..." } } }`
Dispatches `LocationUpdated`. Writes `TripEvent(location_update)` when `trip_id` given. `403` if vehicle/trip not owned.

### 9.2 POST `/api/v1/driver/sos`
Body: `{ "latitude": …, "longitude": …, "message": "Emergency!", "trip_id": 1 }`
**200:** `{ "success": true, "message": "SOS alert sent successfully.", "data": null }`
Writes `TripEvent(sos_alert)` + logs.

---

## 10. ETA

### 10.1 GET `/api/v1/driver/trips/{trip}/eta?current_latitude=..&current_longitude=..`
**200:**
```json
{ "data": { "trip_id": 1, "current_location": { "latitude": 28.61, "longitude": 77.22 },
  "eta": [{ "stop_id": 1, "stop_name": "Stop 1", "sequence": 1, "distance_meters": 1200.0, "distance_km": 1.2, "estimated_minutes": 4, "is_nearby": true }] } }
```