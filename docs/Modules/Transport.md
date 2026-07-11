# Transport Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The transport module manages vehicles, drivers, routes, route stops, assignments, searches, and reporting.

## Architecture

Implemented through TransportController, TransportService, TransportRepository, and transport policies.

## Database Tables

- vehicles
- drivers
- transport_routes
- transport_route_stops
- transport_assignments
- vehicle_locations
- trips
- trip_students
- trip_events

## Models

- App\Modules\Transport\Models\Vehicle
- App\Modules\Transport\Models\Driver
- App\Modules\Transport\Models\Route
- App\Modules\Transport\Models\TransportAssignment

## Controllers

- TransportController

## Services

- TransportService

## Routes

- /admin/transport
- /admin/transport/vehicles
- /admin/transport/drivers
- /admin/transport/routes
- /admin/transport/assignments
- /admin/transport/reports

## Policies

- VehiclePolicy
- DriverPolicy
- RoutePolicy
- RouteStopPolicy
- TransportAssignmentPolicy

## Permissions

- transport.view
- transport.create
- transport.update
- transport.delete

## Business Rules

- Transport records are tied to school context.
- Assignment and route relationships are validated by school data.

## Workflow

1. Register vehicle and driver records.
2. Configure routes and stops.
3. Assign students or routes to transport resources.
4. Review reports and occupancy data.

## Common Issues

- Assignment errors occur when the route or student record is invalid.
- Report endpoints may return empty results if context has not been set.

## Troubleshooting

- Verify route and student records exist and belong to the active school.
- Confirm the current user has transport permissions.
