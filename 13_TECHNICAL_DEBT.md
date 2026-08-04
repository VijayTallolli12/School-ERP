# Technical Debt

## Critical

- Broken route list due report repository binding/import issue.
- Pending migrations in current database.
- Failing realtime attendance test.

## High

- Large controllers: Payroll, Library, Transport, Fees, Reports, Exams.
- Mixed report module view/route locations.
- Debug artifacts and historical audit scripts in project root.
- Mobile APIs coupled to admin permissions.
- Production readiness depends on manual checks instead of CI gates.

## Medium

- Raw SQL expressions in reports/fees/AI handlers need review.
- Frontend debug console logs.
- Limited feature tests outside API/realtime and a few module smoke tests.
- Asset bundle/icon font size.
- No verified OpenAPI specification.

## Low

- Inconsistent module completeness: some requested modules are only represented by fields/statuses.
- Documentation is broad but scattered across root, `docs`, and `reports`.

## Recommended Debt Paydown Order

1. Fix bootstrapping and migrations.
2. Stabilize tests.
3. Consolidate reports.
4. Add CI quality gates.
5. Extract large controllers.
6. Archive old audit/debug files.

