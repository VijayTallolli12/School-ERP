# Security Report

Security score: 68 / 100.

## Implemented Controls

- Web authentication with login/logout/password reset.
- API authentication with Sanctum bearer tokens.
- API login throttling: `throttle:5,1`.
- Authenticated API group uses `auth:sanctum`, `school`, and `throttle:60,1`.
- CSRF protection is active for web routes; `api/*` is excluded.
- Spatie Permission with teams is enabled.
- Route middleware uses permission checks across most admin modules.
- Policies exist for core models.
- Mass assignment is partially controlled through `$fillable` and guarded super-admin field on User.
- Login activity records exist.

## Security Issues

| Severity | Issue | Evidence | Recommendation |
|---|---|---|---|
| Critical | Route/container failure blocks reliable route protection verification. | `php artisan route:list` fails. | Fix provider imports and add route-list CI check. |
| High | Debug mode enabled in audited environment. | `php artisan about`: Debug Mode ENABLED. | Enforce `APP_DEBUG=false` in production. |
| High | Pending migrations affect security/audit tables. | AI query logs and HR tables pending. | Apply migrations before production. |
| High | Mobile APIs are coupled to admin permissions. | `docs/api/MOBILE_API_AUDIT.md` notes parent endpoints require admin-level permissions. | Create dedicated mobile guards/scopes/policies. |
| Medium | No MFA verified. | No MFA code/routes found. | Add optional MFA for admin/principal/accountant roles. |
| Medium | File upload hardening incomplete from static evidence. | Documents and certificates upload files to public storage. | Add MIME validation, size limits, virus scanning, private storage for sensitive docs. |
| Medium | Raw SQL exists in fee/report/AI query code. | Multiple `DB::raw`, `whereRaw`, `orderByRaw` usages. | Review all raw SQL for parameter binding and database portability. |
| Medium | Audit log UI not verified. | Spatie activitylog exists, no full audit logs module verified. | Add searchable audit log UI and retention rules. |

## Production Security Gate

Not approved for production until route bootstrap, migrations, debug config, file-upload policy, and mobile permission separation are verified.

