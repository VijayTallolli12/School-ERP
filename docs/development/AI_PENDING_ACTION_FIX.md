# CRITICAL FIX — PENDING ACTION CONFIRMATION WORKFLOW

Date: 2026-08-13

---

## 1. REAL USER TEST FAILURE

**User:** "Send a notification to all students that they should come tomorrow
in colour dress because tomorrow is our school Sports Day."

**AI:** Confirmation request shown (correct).

**User:** "Sure"

**AI:** "I couldn't understand your question." ❌

The confirmation reply was treated as a brand-new query.

---

## 2. ROOT CAUSE TRACE (before fix)

```
User action request
   ↓
QueryPlanner::plan() → notification.send (action)   [A: action detected]
   ↓
AIService::ask() → isAction() → handleActionIntent()
   ↓
Confirmation message returned (confirmation_required=true)   [B: confirmation generated]
   ↓
Pending action storage: NONE server-side.
   Only the frontend JS variable `pendingConfirmQuestion` held the question
   string.   [C: NOT stored / D: NOT persisted]
   ↓
User types "Sure" → AIController::ask() → AIService::ask("Sure")
   ↓
getPending() = null (no server-side state)   [E: no association]
   ↓
QueryPlanner::plan("Sure") → unknown   [F: "Sure" not detected]
   ↓
"I couldn't understand your question."   [G: reached normal planner]
```

### Why "Sure" reached the normal query planner

1. There was **no server-side pending-action store**. Confirmation state lived
   only in the browser's JS (`pendingConfirmQuestion`), which is lost the
   moment the user types a new message.
2. The controller passed `question="Sure"` straight into `QueryPlanner`.
   Nothing consulted the pending action before planning.
3. The planner had no concept of confirmations; "Sure" matched no tool →
   `unknown` → friendly "couldn't understand" message.

---

## 3. FIXED ARCHITECTURE

```
User
  ↓
Action request
  ↓
QueryPlanner → action tool selected
  ↓
Validate permissions (RoleDataScoper)
  ↓
Resolve parameters
  ↓
Persist PENDING ACTION server-side (ai_pending_actions, user+school bound)
  ↓
Ask for confirmation
  ↓
User replies ("Sure", "Yes", "No", "Cancel", ...)
  ↓
AIService::ask() checks PENDING ACTION FIRST (before planner)
  ↓
Confirm → claim atomically → execute stored params → complete
Cancel  → mark cancelled → nothing executed
Ambiguous → ask again, do NOT execute
Other/new → supersede old pending, re-plan this message
  ↓
Return actual result
```

### New components

| Component | Purpose |
|---|---|
| `database/migrations/2026_08_13_000001_create_ai_pending_actions_table.php` | `ai_pending_actions` table |
| `app/Modules/AiAssistant/Models/AiPendingAction.php` | Model (BelongsToSchool, user, tool, params, status, expires_at) |
| `app/Modules/AiAssistant/Services/PendingActionService.php` | Create/get/claim/complete/cancel/expire pending actions; TTL; supersede |
| `app/Modules/AiAssistant/Services/ConfirmationClassifier.php` | Reusable natural-language confirmation/cancellation/ambiguous detector |

### Changed

| File | Change |
|---|---|
| `app/Modules/AiAssistant/Services/AIService.php` | Pending-action-first resolution; execute-pending path; action branch persists pending; improved confirmation wording + result summary |
| `resources/views/modules/ai-assistant/modal.blade.php` | Confirm/Cancel send "Yes"/"No"; typing "Sure"/"No" works in the input |
| `config/ai.php` + `.env.example` | `AI_PENDING_ACTION_TTL_MINUTES` (default 10) |
| `docs/development/AI.md` | Documented the pending-action workflow |

---

## 4. PENDING ACTION MECHANISM

- **Stored:** `ai_pending_actions` table (server-side, trusted).
- **Bound to:** authenticated `user_id` + authenticated `school_id`
  (from `SchoolContext` — never from the client).
- **Payload:** tool name, exact validated parameters, original question,
  status (`pending_confirmation → executing → completed | cancelled |
  expired | superseded`), `expires_at`.
- **TTL:** `config('ai.pending_action_ttl_minutes', 10)`.
- **One per user+school:** creating a new pending action supersedes any older
  pending one, so stale confirmations can never fire on an outdated action.
- **Atomic claim:** `claimForExecution()` updates
  `pending_confirmation → executing` with a status guard; a concurrent or
  duplicate confirm cannot claim the same row twice → no double execution.

### Confirmation detection

`ConfirmationClassifier::classify()`:
- **confirm:** Yes, Sure, Okay, OK, Go ahead, Send it, Confirm, Proceed,
  Please do, Do it, Yes please, Sure send it, etc. (exact + short-prefix
  tolerant match).
- **cancel:** No, Cancel, Don't send it, Stop, Not now, Never mind, Don't do
  it, No thanks, Abort, etc.
- **ambiguous:** Maybe, I'm not sure, Do you think so, etc. — never executes.
- **other:** anything with a modification marker (actually/instead/only/just/
  change/rather/but/...) or a long/new request → supersedes the old pending
  action and re-plans. This is how "Actually, send it only to Class 5."
  safely discards the all-students action without executing it.

### Cancellation

Marks the pending action `cancelled`; the notification is never created.
Returns e.g. "Okay, I cancelled the notification. Nothing was sent."

### Security / ownership

- The client only ever sends the user's reply text. The backend resolves the
  pending action from server state by the authenticated user + school.
- The client cannot supply `tool`/`parameters` — they come from the stored
  pending record.
- On execution, `executePendingAction()` re-verifies ownership, re-checks
  `isIntentAllowed()` (authorization may have changed), and only then claims +
  executes.

### Duplicate prevention

- Atomic `pending_confirmation → executing` claim.
- A second "Sure" after completion finds no pending `pending_confirmation`
  row → returns "already processed" / falls through, never re-executes.

### Failure handling

- On execution failure the pending action is marked `cancelled` (not
  completed) and the user gets a safe message ("Nothing was changed").
- No duplicate notification is created during retries because the failed row
  is consumed and a fresh action requires a fresh request.

### Expiration

- `getPending()` lazily marks expired pending actions as `expired` and ignores
  them. An expired action can never execute.

---

## 5. NOTIFICATION PREVIEW (improved wording)

Before: "Send notification 'they should come tomorrow in colour dres' to
students?\n\nThis will be delivered immediately." ❌

After:
```
### Notification Ready

**Recipients:** Students
**Title:** <title>
**Message:** <message>

This notification will be sent immediately after you confirm.
```
The claim of delivery was removed — the system only reports delivery/send
after actual execution ("Action Complete … Notifications Sent: N").

---

## 6. OTHER ACTION TOOLS

All 7 action tools flow through the same pending-action mechanism:

| Tool | Confirmation prompt | Executes after confirm |
|---|---|---|
| notification.send | ✅ | ✅ (creates Notification, reports recipient count) |
| homework.create | ✅ | ✅ (claimed/completed; execution requires a resolvable class_section_id) |
| exam.publish | ✅ | ✅ (resolves Exam server-side; fails safely if no exam id) |
| transport.assign | ✅ | ✅ |
| payroll.generate | ✅ | ✅ |
| attendance.notify | ✅ | ✅ |
| fee.send_reminders | ✅ | ✅ |

---

## 7. TESTS

`tests/Feature/AiPendingActionWorkflowTest.php` — **18 tests / 145 assertions**:

- Exact regression: action → confirmation → "Sure" executes the notification.
- Natural confirmations (Yes, Sure, Okay, OK, Go ahead, Send it, Confirm,
  Please do, Yes send it, Proceed, Sure send it, Do it) all confirm.
- Natural cancellations (No, Cancel, Don't send it, Stop, Not now, Never mind,
  Don't do it) all cancel; zero notifications created.
- Ambiguous replies (Maybe, I'm not sure, Do you think so?) never execute.
- "Sure" with no pending action does not execute.
- Double confirmation cannot execute twice.
- Another user cannot confirm my pending action.
- Two separate actions confirm independently (multi-turn).
- Modified request supersedes the old pending action (never executes old scope).
- Expired pending action cannot execute.
- homework.create / exam.publish / transport.assign / payroll.generate flows.
- Queries still work through the normal planner.
- ConfirmationClassifier recognizes common phrases.
- HTTP two-turn confirm flow + HTTP cancel flow.

Also re-verified:
- `AiActionDetectionTest` (action vs query classification) — passing.
- `AiSecurityTest` (confirmation gate, tenant isolation, rate limiting) —
  passing.
- `AiRegistryConsistencyTest` (tool/handler registry) — passing.

---

## 8. MANUAL ACCEPTANCE

### Step 1
Ask: "Send a notification to all students that they should come tomorrow in
colour dress because tomorrow is our school Sports Day."

### Step 2
AI shows the confirmation preview (Recipients / Title / Message).

### Step 3
Reply: "Sure"

### Step 4
AI executes the pending action ("Action Complete … Notifications Sent: N").

### Step 5
Verify in the ERP Notifications module: correct title, message, school,
recipient scope, sender, timestamp.

### Step 6
Check `ai_pending_actions` row → status `completed`; `ai_query_logs` records
`confirmation_pending` then `action_executed`.
