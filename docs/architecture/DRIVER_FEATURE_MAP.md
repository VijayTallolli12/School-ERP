# DRIVER FEATURE MAP

> Generated: 2026-08-07
> Module: Driver App API (`/api/v1/driver/*`)
> Status: All features mapped & implemented

---

## 1. Feature → Endpoint Mapping

| # | Driver Feature | Endpoint | Method | Controller @method | Status |
|---|----------------|----------|--------|--------------------|--------|
| 1 | Login | `/driver/login` | POST | DriverApiController@login | ✅ |
| 2 | Driver Profile | `/driver/me` | GET | DriverApiController@me | ✅ |
| 2a | Driver Profile (legacy) | `/driver/profile` | GET | DriverApiController@profile | ✅ |
| 3 | Assigned Vehicle | `/driver/dashboard` (vehicle block) | GET | DriverApiController@dashboard | ✅ |
| 3a | Assigned Vehicle (dedicated) | `/driver/routes/{route}` (vehicle block) | GET | DriverApiController@routeShow | ✅ |
| 4 | Assigned Route | `/driver/routes/today` | GET | DriverApiController@routesToday | ✅ |
| 4a | Route Detail | `/driver/routes/{route}` | GET | DriverApiController@routeShow | ✅ |
| 5 | Stops List | `/driver/routes/{route}/stops` | GET | DriverApiController@routeStops | ✅ |
| 6 | Students per Stop | `/driver/routes/{route}/students` | GET | DriverApiController@routeStudents | ✅ |
| 7 | Trip Start | `/driver/trips/start` | POST | DriverApiController@tripStartById | ✅ |
| 7a | Trip Start (legacy) | `/driver/trips/{trip}/start` | POST | DriverApiController@tripStart | ✅ |
| 8 | End Trip | `/driver/trips/{trip}/end` | POST | DriverApiController@tripEnd | ✅ |
| 8a | Complete Trip (legacy) | `/driver/trips/{trip}/complete` | POST | DriverApiController@tripComplete | ✅ |
| 9 | Live Trip Status | `/driver/trips/current` | GET | DriverApiController@tripCurrent | ✅ |
| 10 | Mark Attendance (Pickup) | `/driver/trips/{trip}/attendance` | POST | DriverApiController@markAttendance | ✅ |
| 10a | Mark Attendance (Drop) | `/driver/trips/{trip}/attendance/{tripStudent}` | PUT | DriverApiController@updateAttendance | ✅ |
| 10b | Pickup (legacy) | `/driver/trips/{trip}/pickup` | POST | DriverApiController@pickup | ✅ |
| 10c | Drop (legacy) | `/driver/trips/{trip}/drop` | POST | DriverApiController@drop | ✅ |
| 11 | Notifications | `/driver/notifications` | GET | DriverApiController@notifications | ✅ |
| 11a | Mark Notifications Read | `/driver/notifications/read` | POST | DriverApiController@markNotificationsRead | ✅ |
| 12 | Emergency / SOS | `/driver/sos` | POST | DriverApiController@sos | ✅ |
| 13 | Trip History | `/driver/trips/history` | GET | DriverApiController@tripsHistory | ✅ |
| 14 | Route Stops Students | `/driver/routes/{route}/stops` | GET | see #5 | ✅ |
| 15 | Stop Arrival | `/driver/trips/{trip}/arrive-stop` | POST | DriverApiController@arriveStop | ✅ |
| 16 | Stop Departure | `/driver/trips/{trip}/leave-stop` | POST | DriverApiController@leaveStop | ✅ |
| 17 | Live Location Update | `/driver/location` | POST | DriverApiController@updateLocation | ✅ |
| 18 | Logout | `/driver/logout` | POST | DriverApiController@logout | ✅ |
| 19 | Dashboard | `/driver/dashboard` | GET | DriverApiController@dashboard | ✅ |
| 20 | Trip Students List | `/driver/trips/{trip}/students` | GET | DriverApiController@tripStudents | ✅ |
| 21 | Trip ETA | `/driver/trips/{trip}/eta` | GET | DriverApiController@eta | ✅ |

---

## 2. Screen → Feature Coverage

| Driver App Screen | Feature(s) | Endpoint(s) |
|-------------------|------------|-------------|
| Splash / Login | Login | `POST /driver/login` |
| Dashboard | Summary, vehicle, today routes, active trip, next stop | `GET /driver/dashboard` |
| Profile | Driver identity | `GET /driver/me` |
| Route List | Today's routes + trip state | `GET /driver/routes/today` |
| Route Detail | Route info, stops, students | `GET /driver/routes/{route}` , `/stops`, `/students` |
| Trip Live | Current trip, ETA | `GET /driver/trips/current`, `/trips/{trip}/eta` |
| Start/End Trip | Start / End buttons | `POST /trips/start`, `POST /trips/{trip}/end` |
| Attendance | Pickup / Drop per student | `POST/PUT /attendance` |
| Stop Flow | Bus arrived / departed a stop | `POST /arrive-stop`, `/leave-stop` |
| History | Past trips | `GET /driver/trips/history` |
| Notifications | Bell list + mark read | `GET /driver/notifications`, `/read` |
| SOS | Emergency alert | `POST /driver/sos` |
| Map | Live GPS push | `POST /driver/location` |

---

## 3. Feature Lifecycle Summary

| Feature | Offline/Idempotent | Realtime Hook |
|---------|--------------------|---------------|
| Login | No (returns token) | — |
| Location | Yes (append-only) | Dispatches `LocationUpdated` |
| Attendance | Yes (duplicate-safe, `request_id`) | Writes `TripEvent` |
| Stop Arrival/Leave | Yes (append-only events) | Writes `TripEvent` |
| Trip Start | State-machine guarded | Dispatches `TripStarted` |
| Trip End | State-machine guarded | Dispatches `TripCompleted` |
| SOS | Yes | Writes `TripEvent(sos_alert)` + log |

---

## 4. Role & Permission Contract

| Role | Permission | Scope |
|------|-----------|-------|
| `Driver` | `transport.view` | Read: dashboard, profile, routes, trips, history, notifications |
| `Driver` | `transport.update` | Write: trips (start/end), attendance, stop flow, location, SOS |