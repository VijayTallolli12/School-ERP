# Policy Report — Phase 05: Academic Exam Workflow

## New Policies

### 1. GradeScalePolicy
**File:** `app/Modules/Exams/Policies/GradeScalePolicy.php`

| Method | Gate | Permission |
|--------|------|------------|
| `viewAny($user)` | `$user->can('exams.view')` | exams.view |
| `view($user)` | `$user->can('exams.view')` | exams.view |
| `create($user)` | `$user->can('exams.create')` | exams.create |
| `update($user)` | `$user->can('exams.update')` | exams.update |
| `delete($user)` | `$user->can('exams.delete')` | exams.delete |

### 2. ExamSchedulePolicy
**File:** `app/Modules/Exams/Policies/ExamSchedulePolicy.php`

| Method | Gate | Permission |
|--------|------|------------|
| `viewAny($user)` | `$user->can('exams.view')` | exams.view |
| `view($user)` | `$user->can('exams.view')` | exams.view |
| `create($user)` | `$user->can('exams.create')` | exams.create |
| `update($user)` | `$user->can('exams.update')` | exams.update |
| `delete($user)` | `$user->can('exams.delete')` | exams.delete |

### 3. ExamMarkPolicy
**File:** `app/Modules/Exams/Policies/ExamMarkPolicy.php`

| Method | Gate | Permission |
|--------|------|------------|
| `viewAny($user)` | `$user->can('exams.view')` | exams.view |
| `view($user, $mark)` | `$user->can('exams.view')` | exams.view |
| `create($user)` | `$user->can('exams.create')` | exams.create |
| `update($user, $mark)` | `$user->can('exams.update')` | exams.update |
| `delete($user, $mark)` | `$user->can('exams.delete')` | exams.delete |

## Policy Registration

All three policies are registered in `app/Providers/AppServiceProvider.php`:

```php
Gate::policy(GradeScale::class, GradeScalePolicy::class);
Gate::policy(ExamSchedule::class, ExamSchedulePolicy::class);
Gate::policy(ExamMark::class, ExamMarkPolicy::class);
```

## Super Admin Bypass

`AppServiceProvider.php:206-208` registers a `Gate::before` hook that allows Super Admin to bypass all policy checks.

## Updated Role Permissions

In `database/seeders/PermissionSeeder.php`, the **Teacher** role now includes:

| Permission | Previously | Now |
|------------|-----------|-----|
| `exams.view` | ✔ | ✔ |
| `exams.create` | ✘ | ✔ |
| `exams.update` | ✘ | ✔ |
| `exams.reports` | ✔ | ✔ |
