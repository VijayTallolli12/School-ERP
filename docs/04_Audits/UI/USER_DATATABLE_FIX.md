# USER DATATABLE FIX

## Root Cause

The `users` table uses column `status` (VARCHAR, values: `active`/`inactive`), but the DataTable query builder referenced a non-existent column `is_active`.

## Files Changed

### 1. `app/Modules/Users/Controllers/UserManagementController.php`

**Line 43 — Filter WHERE clause**

```diff
- $query->where('is_active', $request->status === 'active');
+ $query->where('status', $request->string('status'));
```

**Line 53 — Display column rendering**

```diff
- ->editColumn('is_active', fn (User $user) => $user->is_active ? 'Active' : 'Inactive')
+ ->addColumn('is_active', fn (User $user) => $user->status === 'active' ? 'Active' : 'Inactive')
```

- `editColumn` tries to read `$user->is_active` (always `null` → always showed `'Inactive'`)
- `addColumn` reads the real `$user->status` column and maps to display label
- JSON output key `is_active` is preserved (view expects it)

### 2. `resources/views/modules/users/index.blade.php` (previous fix)

**Lines 251-253 — DataTable column definitions**

```diff
- {data: 'role_label', name: 'role_label', ...},
- {data: 'school_name', name: 'school_name', ...},
- {data: 'status_label', name: 'status', ...},
+ {data: 'role', name: 'role', ...},
+ {data: 'school', name: 'school', ...},
+ {data: 'is_active', name: 'is_active', ...},
```

## Database Schema Verification

```php
Schema::getColumnListing('users');
// ['id', 'name', 'email', 'email_verified_at', 'password',
//  'remember_token', 'created_at', 'updated_at', 'uuid',
//  'phone', 'avatar_path', 'status', 'is_super_admin',
//  'current_school_id', 'last_login_at', 'last_login_ip',
//  'force_password_change', 'deleted_at']
```

Column `status` (VARCHAR(30), default `'active'`) — defined in migration `2024_01_01_000030_add_erp_fields_to_users_table.php:15`.

Column `is_active` — **does not exist** in any migration or the actual schema.

## Verification

SQL query before fix: `where "is_active" = ?`
SQL query after fix:  `where "status" = ?`

User `$user->status` returns `'active'` (correct). `$user->is_active` returns `null` (always rendered `'Inactive'`).
