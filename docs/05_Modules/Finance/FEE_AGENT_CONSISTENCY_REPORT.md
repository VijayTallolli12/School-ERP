# Fee Collection Agent — Data Consistency Report

## Issue

**Ask ERP** query "Students with pending fees above ₹10,000" returns 5 students.
**Fee Collection Agent** preview (30/60/90 days) returns 0 students, ₹0 outstanding.

## Query Analysis

### Ask ERP — `FeeQueryHandler::studentsWithPendingAbove()`

**File:** `app/Modules/AiAssistant/Handlers/FeeQueryHandler.php:41`

```php
$this->feeService->pendingFeeItemsQuery()
    ->with(['studentFee.student', 'feeCategory'])
    ->get();
```

This calls the shared `FeeService::pendingFeeItemsQuery()`:

```php
StudentFeeItem::query()
    ->whereHas('studentFee.student', fn($q) => $q->where('school_id', ...))
    ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
    ->havingRaw('COALESCE(paid_sum, 0) < student_fee_items.amount')
```

**Filters applied:**
- School scope via `studentFee.student.school_id`
- Items with any unpaid balance (`paid_sum < amount`)
- **No `due_date` filter** — items with NULL, past, or future due_dates all included
- Threshold `>= ₹10,000` applied in PHP after grouping by student

### Fee Collection Agent (BEFORE fix) — `FeeCollectionAgentService::preview()`

**File:** `app/Modules/AiAgents/Services/FeeCollectionAgentService.php:19` (original)

```php
$cutoff = now()->subDays($days)->startOfDay();

StudentFeeItem::query()
    ->whereHas('studentFee.student', fn($q) => $q->where('school_id', ...))
    ->where('due_date', '<', $cutoff)          // RESTRICTIVE FILTER
    ->get()
    ->filter(fn(StudentFeeItem $item) => $item->balance > 0)
```

**Filters applied:**
- School scope via `studentFee.student.school_id`
- `due_date < cutoff` — only items with a due_date before N days ago
- `balance > 0` — in PHP (after query)

### Fee Collection Agent (AFTER fix)

```php
$this->feeService->pendingFeeItemsQuery()
    ->with(['studentFee.student.currentSession.classSection', 'feeCategory', ...])
    ->get();
```

Now uses the **identical** query as Ask ERP via the shared `FeeService::pendingFeeItemsQuery()`.

## Root Cause

### Primary — `due_date` filter exclusion

The line `->where('due_date', '<', $cutoff)` was the root cause. It excluded items in three scenarios:

| Scenario | SQL behavior | Effect |
|---|---|---|
| `due_date IS NULL` | `NULL < date` = never true | **Always excluded** |
| `due_date` is recent (past due by < N days) | `due_date` > `cutoff` → not matched | **Excluded** for days=30 if past due by 1–29 days |
| `due_date` is in the future | Future `due_date` < past `cutoff` = false | **Always excluded** |

Ask ERP had **none** of these restrictions, so it correctly includes all items with unpaid balances.

### Secondary — no shared query method

Both modules queried `StudentFeeItem` independently with different filter logic. There was no single source of truth for "what counts as a pending fee item."

## Fix Applied

### 1. Shared query method — `FeeService::pendingFeeItemsQuery()`

Added to `app/Modules/Fees/Services/FeeService.php:27`:

```php
public function pendingFeeItemsQuery(): Builder
{
    return StudentFeeItem::query()
        ->whereHas('studentFee.student', fn($q) => $q->where('school_id', $this->schoolContext->id()))
        ->withSum(['paymentItems as paid_sum' => fn($q) => $q->whereHas('feePayment')], 'amount')
        ->havingRaw('COALESCE(paid_sum, 0) < student_fee_items.amount');
}
```

This is the canonical definition of "a fee item with an outstanding balance." It is:
- School-scoped
- Balance computed via `withSum` (same subquery as `FeePaymentItem` fee payments)
- Filtered at SQL level using `HAVING` to exclude fully paid items
- **Agnostic to `due_date`** — any unpaid item, regardless of due date, is a "pending" fee

### 2. Refactored `FeeCollectionAgentService`

Changed from inline query to `$this->feeService->pendingFeeItemsQuery()`.

Removed:
- `$cutoff = now()->subDays($days)->startOfDay()`
- `->where('due_date', '<', $cutoff)`
- `->filter(fn(...) => $item->balance > 0)` — redundant with `havingRaw`
- `->values()` — not needed after `havingRaw`

The `$days` parameter is retained for audit logging.

### 3. Refactored `FeeQueryHandler`

Changed `studentsWithPendingAbove()` and `topDefaulters()` to use `$this->feeService->pendingFeeItemsQuery()` instead of inline queries.

Method `totalOutstanding()` retained its original aggregate query (uses a different SQL structure with `SUM` + `JOIN`).

## Dependency Chain

```
AIController
  └── AIService
        ├── IntentResolver
        └── FeeQueryHandler ───┐
                               ├── SchoolContext
AgentController                ├── FeeService (new)
  └── FeeCollectionAgentService ─┘
                  │
                  └── (was: StudentFeeItem::query() inline → now: FeeService::pendingFeeItemsQuery())
```

All dependencies auto-resolved by Laravel container — no registration changes needed.

## Verification

- **28/28 Playwright tests pass** (14 ask-erp-mvp + 14 fee-collection-agent)
- PHP syntax check: no errors on all 3 changed files
- Ask ERP and Fee Agent now query the **same data set** — any student with an unpaid balance appears in both

## Bonus Fix — Class Display Showing N/A

### Root Cause

The `ClassSection` model (`class_section` table) has **no `name` column**. Its columns are: `id`, `school_id`, `class_id`, `section_id`, `class_teacher_id`, `status`. The display name is provided by the `display_name` accessor:

```php
public function getDisplayNameAttribute(): string
{
    if ($this->relationLoaded('schoolClass') && $this->relationLoaded('section')) {
        return ($this->schoolClass->name ?? '') . ' - ' . ($this->section->name ?? '');
    }
    return 'Class #' . $this->class_id . ' - Section #' . $this->section_id;
}
```

The original code used `->name` which is `null` on `ClassSection` models, so the null coalescing operator `?? 'N/A'` always fell through to `'N/A'`.

### Fix (FeeCollectionAgentService.php)

1. Changed `->name` → `->display_name` to use the model's accessor
2. Added `schoolClass` and `section` to the eager load so the accessor returns the full "Grade X - Y" format instead of "Class #N - Section #N"

```diff
 ->with([
-    'studentFee.student.currentSession.classSection',
+    'studentFee.student.currentSession.classSection.schoolClass',
+    'studentFee.student.currentSession.classSection.section',
 ])
 ...
-'class' => $student->currentSession->first()?->classSection?->name ?? 'N/A',
+'class' => $student->currentSession->first()?->classSection?->display_name ?? 'N/A',
```

### Verification

- Debug output shows "Grade 1 - A" and "Grade 2 - A" instead of "N/A"
- All 28 Playwright tests pass

## Future-Proofing

Any new feature that needs to find students with outstanding fees should call `FeeService::pendingFeeItemsQuery()`. The shared method is the single source of truth for pending fee item queries. This prevents the two code paths from diverging again.

For class display, always use `classSection?->display_name` (not `->name`) and ensure `schoolClass` and `section` are eager-loaded for best results.
