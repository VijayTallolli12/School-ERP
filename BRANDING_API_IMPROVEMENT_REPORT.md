# Branding API Improvement Report

## Scope

Improvements made to the public Branding API (`GET /api/v1/branding`) before it is integrated into the mobile apps.

- **Response structure is unchanged** — existing clients keep working.
- **Image URLs are always absolute, always reachable by mobile devices, and never `localhost`.**
- **Missing assets fall back to default School ERP logo and favicon** — image URLs are never `null`.

---

## Changes

| File | Action | Description |
|------|--------|-------------|
| `app/Http/Controllers/Api/V1/BrandingController.php` | Modified | Generates absolute storage URLs, replaces localhost hosts, returns default logo/favicon when assets are missing |
| `public/images/school-logo.png` | Created | Default School ERP logo (256×256 PNG) used when a school has no logo |
| `public/favicon.ico` | Replaced | Real default favicon (was an empty 0-byte file) used when a school has no favicon |
| `tests/Feature/BrandingApiTest.php` | Created | 10 tests covering structure, dev, production, missing logo, missing favicon, and localhost safety |

---

## How URL Generation Works

`BrandingController` resolves a base URL for every image URL returned by the API:

1. **Prefer the current request host** — `request()->getScheme() . '://' . request()->getHttpHost()`. This is what a phone actually uses to reach the server.
2. **Fall back to `APP_URL`** when the request host is unavailable.
3. **Never return `localhost`/`127.0.0.1`** — if the best candidate is a loopback host, the host is swapped for the machine's local network IP (scheme and port are preserved). The IP is discovered via a UDP socket probe and can be overridden with a `LOCAL_IP` env variable.
4. Uploaded assets are returned as `{base}/storage/{path}`; defaults are returned as `{base}/images/school-logo.png` and `{base}/favicon.ico`.

### Local development

A phone hitting the dev machine over the LAN receives URLs such as:

```
http://192.168.1.3:8000/storage/settings/schools/PPWetG2ksLnzDmlI9E4A34F8fh0nws9DDbDxa7Qw.png
http://192.168.1.3:8000/images/school-logo.png
```

Even when the server is reached via `http://localhost:8000`, the returned URLs use the LAN IP (never `localhost`), so phones on the same network can load them.

### Production

Behind a domain, URLs follow the public host:

```
https://your-domain.com/storage/settings/schools/xxx.png
https://your-domain.com/images/school-logo.png
https://your-domain.com/favicon.ico
```

### Missing logo / missing favicon

If `School.logo_path` or `settings.school.favicon_path` is empty, the API returns the default assets instead of `null`:

```
"school_logo": "http://192.168.1.3:8000/images/school-logo.png",
"favicon":     "http://192.168.1.3:8000/favicon.ico"
```

---

## Verification

### Automated tests

```
php artisan test --filter=BrandingApiTest
```

| Test | Result |
|------|--------|
| Response structure is unchanged (all 9 keys, no extras) | PASS |
| Default branding when no `school_id` | PASS |
| Default branding for missing school | PASS |
| Missing logo returns default School ERP logo | PASS |
| Missing favicon returns default favicon | PASS |
| School assets are served from `/storage/...` | PASS |
| Production uses the domain host | PASS |
| Local development uses the request host IP | PASS |
| Image URLs never return `localhost` | PASS |
| `X-School-Id` header is supported | PASS |

### Full suite

```
php artisan test
```

```
Tests:    179 passed (645 assertions)
Duration: 395.24s
```

### Live end-to-end (dev server, MySQL)

| Scenario | Request | `school_logo` result |
|----------|---------|----------------------|
| Local dev (via localhost) | `GET /api/v1/branding?school_id=1` | `http://192.168.1.3:8000/storage/settings/schools/PPWetG2ksLnzDmlI9E4A34F8fh0nws9DDbDxa7Qw.png` (never `localhost`) |
| Production host | `GET /api/v1/branding?school_id=1` with `Host: erp.schoolapp.example` | `http://erp.schoolapp.example/storage/settings/schools/PPWetG2ksLnzDmlI9E4A34F8fh0nws9DDbDxa7Qw.png` |
| Missing logo | `GET /api/v1/branding?school_id=2` (no assets) | `http://192.168.1.3:8000/images/school-logo.png` (default, non-null) |
| Missing favicon | `GET /api/v1/branding?school_id=2` (no assets) | `http://192.168.1.3:8000/favicon.ico` (default, non-null) |

Asset availability checks (all `200`):

- `GET /images/school-logo.png` → `image/png`, 2971 bytes
- `GET /favicon.ico` → `image/vnd.microsoft.icon`, 341 bytes
- `GET /storage/settings/schools/PPWetG2ksLnzDmlI9E4A34F8fh0nws9DDbDxa7Qw.png` → `image/png`, 401948 bytes

---

## Requirements Checklist

| # | Requirement | Status |
|---|-------------|--------|
| 1 | Do NOT change the response structure | ✅ Unchanged — same 9 keys, same `success`/`message`/`data` envelope |
| 2 | Do NOT break existing clients | ✅ Compatible — URLs are still absolute strings; only `null` values are now resolved to defaults |
| 3 | Ensure image URLs never return `localhost` | ✅ Loopback hosts are swapped for the LAN IP; verified in tests and live |
| 4 | Return default School ERP logo/favicon when assets are missing; never `null` | ✅ Defaults returned for missing `logo_path` / `favicon_path` |
| 5 | Verify local dev, production, missing logo, missing favicon | ✅ See verification above |
| 6 | Run tests | ✅ 179 passed (645 assertions) |
