# DataTable Root Cause Report

## Executive Summary

The unexpected `queries` field in server-side DataTable JSON responses is being injected by the Yajra DataTables package when Laravel debug mode is enabled. This is not caused by application query logging middleware or custom response macros in the app code. It is a built-in debug feature of Yajra DataTables and is active whenever `config('app.debug')` is true.

## Findings

- The `queries` field is added inside the vendor package at:
  - `vendor/yajra/laravel-datatables-oracle/src/QueryDataTable.php`
- The debug hook runs when the DataTables package detects application debug mode via:
  - `vendor/yajra/laravel-datatables-oracle/src/Utilities/Config.php::isDebugging()`
  - which returns `(bool) $this->repository->get('app.debug', false)`.
- In the current workspace `.env`, `APP_DEBUG=true` is set, meaning Yajra debug output is enabled in local environment and likely in any environment where that value is inherited or deployed incorrectly.

## Root Cause

Yajra DataTables automatically appends debug metadata to query-based DataTable responses when Laravel debug mode is on. Specifically:

- `DataTableAbstract::render()` calls `showDebugger()` when debugging is enabled.
- `QueryDataTable::showDebugger()` appends:
  - `queries` => executed query log
  - `input` => all request input

Therefore every AJAX DataTable response built from a query (`DataTables::of($query)` or `DataTables::eloquent($query)`) can contain the debug leak.

## Evidence

- `vendor/yajra/laravel-datatables-oracle/src/DataTableAbstract.php`:
  - `if ($this->config->isDebugging()) { $output = $this->showDebugger($output); }`
- `vendor/yajra/laravel-datatables-oracle/src/QueryDataTable.php`:
  - `protected function showDebugger(array $output): array` appends `queries` and `input`.
- `vendor/yajra/laravel-datatables-oracle/src/Utilities/Config.php`:
  - `isDebugging()` checks `app.debug`.
- `.env` currently contains `APP_DEBUG=true`.

## Impact on Performance

- The presence of debug mode in DataTables responses increases response payload size.
- It also forces query log collection and recursive encoding of query metadata, which adds overhead even for empty DataTable requests.
- This explains why empty query results can still be slow: the slow path is not only database execution but also debug log assembly for DataTable responses.

## Recommended Fix

1. Ensure production and staging environments use `APP_DEBUG=false`.
2. Verify the deployed environment does not override `APP_DEBUG` with `true`.
3. If explicit protection is needed for DataTables responses, patch or extend Yajra DataTables to disable debug output separately from `app.debug`.

Suggested mitigation:

- Add `APP_DEBUG=false` to production environment settings.
- In local dev, keep `APP_DEBUG=true` but do not deploy this debug configuration.
- Optionally add a custom override in `AppServiceProvider` or a dedicated config file to ensure DataTables debug output is disabled for production routes.

## Next Steps

- Audit deployment environment variables for `APP_DEBUG`.
- Confirm whether the `queries` field is present in production after `APP_DEBUG=false` is enforced.
- If additional security is required, patch Yajra DataTables debug behavior so that it does not depend solely on `app.debug`.
- Optionally add a `datatables` config override that explicitly disables debug output, then patch the package or add wrapper logic to respect that override.

---

### Notes

This is a root-cause-level issue in the Yajra library integration rather than an app-specific response macro or middleware bug.
