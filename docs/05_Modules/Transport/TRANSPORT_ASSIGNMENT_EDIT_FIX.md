# Transport Assignment Edit — Root Cause & Fix

## Root Cause

When opening the Edit Assignment modal, existing saved values were not being restored correctly in Select2-enhanced dropdowns. Three issues:

### 1. Route Select2 display not synced

**File:** `resources/views/modules/transport/index.blade.php:390-396`

The edit handler iterates `response.data` and sets values via `.val()` on the native `<select>`:

```javascript
Object.entries(response.data).forEach(([key, value]) => {
    const field = form.find(`[name="${key}"]`);
    field.val(value);
});
```

`field.val(routeId)` sets the hidden native `<select>` but **never notifies Select2**. Select2's visible UI is driven by its internal state, not the native element's value. Without `.trigger('change')`, Select2 stays on the placeholder/blank state.

### 2. Stop Select2 value set after initialization

**File:** `resources/views/modules/transport/index.blade.php:305-323` (`populateAssignmentStops`)

The function calls `App.refreshSelect2Options('#assignmentStop', ...)` which:
1. Destroys the existing Select2 instance
2. Rebuilds `<option>` elements
3. Restores previous value (empty — form was `reset()`)
4. Re-initializes Select2

Then outside `refreshSelect2Options`, `$stop.val(selectedStopId)` sets the native value — **but Select2 was already initialized with empty value**. The display stays blank.

### 3. Vehicle not set on edit

`setAssignmentVehicle(routeId)` was only called by the `#assignmentRoute` change handler (for user-initiated changes), never in the edit handler. The vehicle dropdown was set solely from `response.data.vehicle_id`, which may not match the route's default vehicle.

### 4. Invalid state allowed

When Route was empty (null/blank), the Stop dropdown was still enabled and could contain stale options from a previous selection, creating a `Stop without Route` inconsistency.

---

## Edit API Response Payload

Endpoint: `GET /admin/transport/assignments/{assignment}` (via `TransportController::showAssignment`)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "school_id": 1,
    "student_id": 42,
    "route_id": 3,
    "route_stop_id": 15,
    "vehicle_id": 2,
    "pickup_point": "Main Gate",
    "monthly_fee": "1500.00",
    "status": "active",
    "created_at": "...",
    "updated_at": "...",
    "student": { "id": 42, "full_name": "...", ... },
    "route": { "id": 3, "route_name": "Route A", ... },
    "stop": { "id": 15, "stop_name": "KR Puram Market", "pickup_time": "07:15", "drop_time": "15:30", ... },
    "vehicle": { "id": 2, "vehicle_number": "KA-01-1234", ... }
  }
}
```

All `route_id`, `route_stop_id`, and `vehicle_id` are present in the response. The `student` relation object is also loaded and available for creating the AJAX Select2 option.

---

### Issue 2: Student AJAX Select2 shows stale name

**Root cause:** The `#assignmentStudentId` / `[name="student_id"]` select uses AJAX (`data-ajax-url`), so its `<option>` elements are never pre-rendered — only the empty placeholder option exists in HTML. When `field.val(studentId)` sets the native value:

1. No `<option value="studentId">` exists in the DOM → Select2 can't display any text
2. `form[0].reset()` resets only the native `<select>`, **not** Select2's internal state
3. Select2 retains the previously selected student's display text from the prior edit session

This is why editing the second record shows the **first** record's student name.

**Fix:** Create an `<option>` element from the loaded `response.data.student` relation before setting the value:

```javascript
const student = response.data.student;
if (student) {
    const $st = form.find('[name="student_id"]');
    const optText = student.full_name + ' (' + student.admission_no + ')';
    if (!$st.find('option[value="' + student.id + '"]').length) {
        $st.append(new Option(optText, student.id, true, true));
    }
    $st.val(student.id).trigger('change');
}
```

**Also applied to the "open new assignment" handler** to clear stale state:

```javascript
form.find('[name="student_id"]').val('').trigger('change');
```

---

## Select2 Initialisation Sequence — Before Fix

```
DOMContentLoaded
  ↓
App.initSearchableSelects()  ← student/route/stop Select2 created
  ↓
Edit Assignment A → save → hide modal
  ↓
Click Edit on Assignment B
  ↓
AJAX response received
  ↓
form[0].reset()              ← native selects reset, Select2 NOT cleared
                             → student Select2 still shows A's name 👈
  ↓
field.val(student_id)        ← native has B's ID, but no <option> exists → ❌ stale text
field.val(route_id)          ← native has value, Select2 NOT notified → ❌ blank
field.val(route_stop_id)     ← native has value, Select2 NOT notified → ❌ blank  
field.val(vehicle_id)        ← plain select, works
  ↓
populateAssignmentStops(routeId, stopId)
  ├─ refreshSelect2Options   ← destroys & re-inits stop Select2 (with empty value)
  └─ $stop.val(stopId)       ← after init → ❌ Select2 stays blank
  ↓
Modal shown                  ← ❌ Route blank, Stop blank, Student shows A's name
```

## Select2 Initialisation Sequence — After Fix

```
DOMContentLoaded
  ↓
App.initSearchableSelects()
  ↓
Edit Assignment A → save → hide modal
  ↓
Click Edit on Assignment B
  ↓
AJAX response received
  ↓
form[0].reset()
  ↓
field.val(student_id)        ← native has B's ID, still no <option>
field.val(route_id)
field.val(route_stop_id)
field.val(vehicle_id)
  ↓
Create <option> from response.data.student  ← ✅ injects text + value
$st.val(B.id).trigger('change')             ← ✅ Select2 displays B's name
  ↓
$('#assignmentRoute').val(routeId).trigger('change')
  ├─ Select2 reads native value → ✅ displays route name
  └─ change event handler fires:
       ├─ populateAssignmentStops(routeId, null)  ← builds stop options, clears selection
       ├─ setAssignmentVehicle(routeId)            ← ✅ sets vehicle from route
       └─ clears pickup/drop times
  ↓
populateAssignmentStops(routeId, stopId)
  ├─ refreshSelect2Options   ← rebuilds stop options
  └─ $stop.val(stopId).trigger('change')  ← ✅ after init, Select2 syncs
       └─ change handler fires → setAssignmentTimes(stopId)
  ↓
setAssignmentTimes(stopId)   ← safety net, ensures times match
  ↓
Modal shown                  ← ✅ Student, Route, Stop, Vehicle all displayed correctly
```

---

## Fixes Applied

### Fix 1: Route Select2 sync (`index.blade.php:398-407`)

```javascript
// BEFORE
if (type === 'assignment') {
    const routeId = response.data.route_id;
    const stopId = response.data.route_stop_id;
    populateAssignmentStops(routeId, stopId);
    setAssignmentTimes(stopId);
}

// AFTER
if (type === 'assignment') {
    const routeId = response.data.route_id;
    const stopId = response.data.route_stop_id;
    $('#assignmentRoute').val(routeId).trigger('change');
    populateAssignmentStops(routeId, stopId);
    setAssignmentTimes(stopId);
}
```

The `.trigger('change')` on `#assignmentRoute` does double duty:
- **Select2 sync**: updates the visible dropdown label
- **Change handler fires**: populates stop options, sets vehicle from route, clears times

### Fix 2: Stop Select2 sync (`index.blade.php:316`)

```javascript
// BEFORE
$stop.val(selectedStopId);

// AFTER
$stop.val(selectedStopId).trigger('change');
```

`.trigger('change')` notifies Select2 to update its display. Also fires the `#assignmentStop` change handler that calls `setAssignmentTimes`.

### Fix 3: Stop disabled when no route (`index.blade.php:322`)

```javascript
// ADDED at end of populateAssignmentStops
$stop.prop('disabled', !routeId);
```

When `routeId` is empty/falsy (new assignment or route cleared), the stop dropdown is disabled and visually greyed out. When a route is selected, it becomes enabled.

### Fix 4: Student AJAX Select2 — create option from loaded relation (`index.blade.php:402-410`)

```javascript
const student = response.data.student;
if (student) {
    const $st = form.find('[name="student_id"]');
    const optText = student.full_name + ' (' + student.admission_no + ')';
    if (!$st.find('option[value="' + student.id + '"]').length) {
        $st.append(new Option(optText, student.id, true, true));
    }
    $st.val(student.id).trigger('change');
}
```

The AJAX-driven student select has no pre-rendered `<option>` elements. This injects one from the loaded `response.data.student` relation so Select2 can display the text. Also clears stale state on "New Assignment" modal open:

```javascript
form.find('[name="student_id"]').val('').trigger('change');
```

### Fix 5: setAssignmentVehicle via change handler cascade

No explicit code change needed — triggering `change` on `#assignmentRoute` naturally fires the change handler which calls `setAssignmentVehicle(routeId)`.

---

## Regression Testing

| Test Case | Expected | Result |
|---|---|---|
| Create assignment with Student X, Route A, Stop Y | Assignment saved, DataTable refreshed | ✅ |
| Click Edit on that row | Student X shown, Route A selected, Stop Y selected, times+vehicle populated | ✅ |
| Save edit | Modal closes, DataTable reloads | ✅ |
| Click Edit on a different row (Student Z) | Student Z shown (not X), Route+Stop correct | ✅ |
| Change route on edit | Stop options update, vehicle updates, times cleared | ✅ |
| Reload page, click Edit again | Same values reappear | ✅ |
| Open New Assignment modal | Student dropdown empty/placeholder, Stop disabled | ✅ |
| Select student via AJAX search | Student selected, works | ✅ |
| Select Route → Stop becomes enabled | Stop populated from selected route | ✅ |
| Clear Route → Stop becomes disabled | Stop cleared, disabled | ✅ |

---

## Confirmation Checklist

- [x] Student AJAX Select2 displays saved value on edit (not stale name from prior edit)
- [x] Route Select2 displays saved value on edit
- [x] Stop Select2 displays saved value on edit
- [x] Vehicle is set from route on edit
- [x] Pickup/Drop times populated from saved stop
- [x] Monthly Fee and Status restored correctly (plain fields, no Select2 involvement)
- [x] Invalid state prevented: no Route → Stop disabled
- [x] New assignment flow unchanged (stop disabled, all fields empty)
- [x] User-triggered route change still works correctly
