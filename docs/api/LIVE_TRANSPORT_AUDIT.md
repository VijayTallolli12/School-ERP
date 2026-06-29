# Live Transportation Tracking — Audit Summary

## Phase 5.5

**Objective:** Provide real-time bus location tracking for transport managers and parents.

## Deliverables

### Migration
- `create_vehicle_locations_table` — stores GPS breadcrumbs per vehicle

### Model
- `VehicleLocation` — `App\Models\VehicleLocation`
  - Tracks `vehicle_id`, `latitude`, `longitude`, `speed`, `heading`, `captured_at`, `source`

### API Endpoints (authenticated)
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/api/v1/transport/location` | `api.v1.transport.location.update` | Submit GPS location |
| GET | `/api/v1/transport/live` | `api.v1.transport.live` | Live dashboard (active/inactive vehicles) |
| GET | `/api/v1/transport/vehicle/{id}/location` | `api.v1.transport.vehicle.location` | Location history for a vehicle |

### Events (with push notification support)
| Event | Payload |
|-------|---------|
| `LocationUpdated` | vehicleId, latitude, longitude, speed, heading, capturedAt |
| `BusArriving` | vehicleId, routeStopId, stopName, distanceMeters, estimatedMinutes |
| `BusArrived` | vehicleId, routeStopId, stopName |
| `TripStarted` | vehicleId, routeId, startedAt |
| `TripCompleted` | vehicleId, routeId, completedAt |

### Listeners
- `SendPushNotification` — sends FCM push for transport events
- `LogTransportActivity` — logs to transports_activity_log (placeholder)

### Services
- `EtaService` — distance calculation (Haversine), ETA estimation, threshold checks

### Adapters (structure only)
- `GpsDeviceAdapterInterface`
- `GpsDeviceAdapter` — placeholder for GPS hardware integration

### Tests
- `LiveTransportTest` — 17 tests, 53 assertions covering:
  - Location submission (success, minimal fields, validation)
  - Live status endpoint (with/without data, summary stats)
  - Vehicle location history
  - 404 handling
  - Unauthenticated access
  - Event dispatch verification (all 5 events)
  - ETA service unit tests (distance, estimated minutes, threshold)

## Future Enhancements
- Proximity-based BusArriving/BusArrived auto-detection (needs lat/lng on RouteStop)
- GPS device adapter implementation
- Geofencing for school zones
- WebSocket broadcasting for real-time map updates
