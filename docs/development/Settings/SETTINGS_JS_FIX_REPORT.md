# SETTINGS JS FIX REPORT

Date: 2026-08-05
Scope: Two JavaScript runtime errors affecting the Settings save flow
Status: Fixed, verified, built

## Errors Reported

### Error 1 — `ReferenceError: initTabPersistence is not defined` (settings.blade.php)

**Root cause:** `resources/views/modules/settings/index.blade.php` called `initTabPersistence('#settingsTabs');` as a **bare top-level statement** in an inline classic `<script>` inside `@push('scripts')`. Inline classic scripts execute during HTML parsing, but the Vite bundle (`resources/js/app.js`) is an ES module (`type="module"`), which is deferred and executes only after parsing completes. At the moment the bare call ran, `window.initTabPersistence` (defined in `app.js:346`) did not exist yet → ReferenceError.

**Why only Settings (and Calendar/Notifications) crashed:** the correct pattern is to call the helper inside a `DOMContentLoaded` / jQuery-ready handler, which fires after module scripts have executed. Settings, Calendar, and Notifications each had the call outside the wrapper; every other blade (Fees, Library, Transport, Payroll, Academics, reports pages) already wrapped it correctly.

**Fix:** moved the call inside the ready handler:

```js
$(function () {
    // ...existing image preview logic...
    initTabPersistence('#settingsTabs');
});
```

Applied the same fix to the two identical latent instances:
- `resources/views/modules/calendar/index.blade.php` — `initTabPersistence('#calendarTabs');`
- `resources/views/modules/notifications/index.blade.php` — `initTabPersistence('#notificationsTabs');`

### Error 2 — `TypeError: extend is not a function` inside the toast (Critical)

**Root cause (deep dive):** the failure happens in toastr's internal `getOptions()` at `node_modules/toastr/toastr.js:452`:

```js
function getOptions() {
    return $.extend({}, getDefaults(), toastr.options);
}
```

Every toast calls this, so when toastr's `$` (the `jquery` dependency resolved by its AMD `define(['jquery'], ...)`) is not a real jQuery instance, `$.extend` is undefined → `TypeError: extend is not a function`.

The chain that produced it:

1. `vite.config.js` aliased `jquery` → `resources/js/jquery-shim.js`.
2. That shim was an **ESM** module: `export default window.jQuery;`.
3. Rollup compiled it to a module namespace object `{ default: window.jQuery }` (verified in the old bundle: `Sn=Object.freeze({...default:Ae,...})`).
4. toastr's AMD factory received that namespace through rollup's `__toESM` helper (`Rh=Ch(mo)`), which returned a **callable shell** wrapping the default export — a function with `jQuery.prototype` but **no `.extend`, `.fn`, etc.**.
5. `App.toast('success', ...)` (app.js:76) → `toastr.success(...)` → `getOptions()` → `$.extend(...)` → crash.

**Consequence for Settings save:** `submitAjaxForm`'s success handler (app.js:133) calls `App.toast(...)` *before* `response.reload`. The throw aborted the success callback, so:
- success notification never appeared,
- `window.location.reload()` was never executed,
- the save button stayed disabled/loading until the 30 s safety timeout.

**Fix:** converted the shim to **CommonJS** so `require('jquery')` returns the real jQuery function instead of a namespace object:

- Created `resources/js/jquery-shim.cjs`:
  ```js
  module.exports = window.jQuery;
  ```
  (`.cjs` extension is required because `package.json` declares `"type": "module"`.)
- Updated `vite.config.js` alias: `jquery: resolve(__dirname, 'resources/js/jquery-shim.cjs')`.
- Removed the obsolete `resources/js/jquery-shim.js`.

**Result in the new bundle (verified):** toastr's UMD fallback now receives `window.jQuery` directly —
`n(mo())` where `mo` (`Ia`) is `function Ia(){ return (Pr || (Pr = 1, ar = window.jQuery)), ar; }`.
DataTables likewise receives the same instance (`s = window.jQuery`), so both plugins operate on the CDN jQuery used by the pages. **No notification library was replaced or changed.**

## Files Changed

| File | Change |
| --- | --- |
| `resources/js/jquery-shim.cjs` | **New** — CommonJS shim exporting the CDN jQuery instance |
| `resources/js/jquery-shim.js` | **Deleted** — ESM shim that caused the `__toESM` namespace wrapping |
| `vite.config.js` | Alias `jquery` → `resources/js/jquery-shim.cjs` |
| `resources/views/modules/settings/index.blade.php` | Moved `initTabPersistence('#settingsTabs')` inside ready handler |
| `resources/views/modules/calendar/index.blade.php` | Moved `initTabPersistence('#calendarTabs')` inside DOMContentLoaded handler |
| `resources/views/modules/notifications/index.blade.php` | Moved `initTabPersistence('#notificationsTabs')` inside DOMContentLoaded handler |
| `e2e/settings-js-fix.spec.ts` | **New** — regression spec for both errors |

## Verification

### Playwright E2E (`e2e/settings-js-fix.spec.ts`) — 3 passed

1. **Settings page loads without `initTabPersistence` ReferenceError** — asserts no pageerror, `#settingsTabs` + `#settingsForm` visible.
2. **Settings save triggers reload** — submits the form and waits for the `reload:true` navigation. Reload executes only when `App.toast()` returns without throwing, so this directly proves the success-toast path completed. Also asserts no `extend is not a function` pageerror.
3. **`App.toast` renders a success toast** — uses the users-page status toggle (a toast path with no reload) and asserts `.toast-success` becomes visible.

All three pass against a live server (`http://127.0.0.1:8000`).

### PHP test suite

`php artisan test` — **164 passed, 577 assertions** (full suite green).

### Production build

`npm run build` — vite 7.3.6, 133 modules transformed, built in 8.51s. Old chunk hashes replaced; bundle verified to inject the raw `window.jQuery` into toastr.

## Save-Flow Behavior After Fix

| Step | Before | After |
| --- | --- | --- |
| Submit settings | Save button → loading | Save button → loading (unchanged) |
| Success toast | Never appeared (crash) | `Settings updated successfully.` toast shows |
| `reload: true` | Never executed | Page reloads |
| Validation error (422) | — | `App.handleValidation` + error toast (unchanged code path) |
| Network/5xx error | — | Error toast (unchanged code path) |

## Notes / Remaining

- `docs/04_Audits/UI/DATATABLE_AUDIT_REPORT.md` documents the original ESM shim (`resources/js/jquery-shim.js`). It is a historical audit record and was intentionally left unchanged; this report supersedes it.
- The historical playbook references (e.g. `docs/06_Testing/e2e/`) are unaffected.
