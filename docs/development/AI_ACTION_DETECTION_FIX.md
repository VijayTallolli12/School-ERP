# CRITICAL FIX — NATURAL LANGUAGE ACTION DETECTION (Ask ERP / Executive Copilot)

Date: 2026-08-13

---

## 1. PROBLEM (from real user testing)

Request:

> "Send a notification to all students that they should come tomorrow in colour
> dress because tomorrow is our school Sports Day."

Result (WRONG):

```
Found 3 matching records.
```

The user asked the system to PERFORM an action (send a notification), not to
read ERP records. The AI routed it to a read/query tool.

---

## 2. ROOT CAUSE TRACE (actual execution path)

```
User Input
  "Send a notification to all students ... Sports Day."
      │
      ▼
QueryPlanner::plan()
      │
      ├─ 1. detectAction()
      │      ACTION_KEYWORDS['notification.send'] = ['send notification', ...]
      │      Uses str_contains() exact-substring matching.
      │      "send a notification"  ≠  "send notification"   ← article breaks it
      │      → no match → returns null
      │
      ├─ 2. Provider (LLM)
      │      Catalog passed = $registry->all()  ← QUERY TOOLS ONLY
      │      Action tools (notification.send, ...) were NEVER in the catalog,
      │      so the LLM could not propose an action even if it understood one.
      │      → (provider falls back / not configured in this trace)
      │
      └─ 3. planByRules()  (deterministic fallback)
             matchByKeywords() iterates ONLY query tools ($registry->all()).
             "all students" → matches student.total keyword "all students"
             → intent = student.total (query)
             → executed → "Found 3 matching records."
```

### Two distinct root bugs

1. **Brittle action detection.** `detectAction()` required exact contiguous
   phrases ("send notification"). Natural language inserts articles, reorders
   words, and uses synonyms ("notify all parents", "publish the exam",
   "generate the payroll", "assign this student to route 3"), all of which
   missed the keyword list.

2. **Action tools invisible to the fallback/provider.** Both the provider
   catalog (`$registry->all()`) and the rule fallback (`matchByKeywords`)
   only ever considered QUERY tools. Even a perfectly understood action could
   never be selected.

---

## 3. FIX

### 3.1 `app/Modules/AiAssistant/Erp/QueryPlanner.php`

- Replaced the exact-phrase `ACTION_KEYWORDS` detection with ordered **regex
  patterns** (`ACTION_PATTERNS`), most-specific-first:

  | Intent | Pattern (abridged) |
  |---|---|
  | payroll.generate | generate/run/process/create … payroll/salary |
  | attendance.notify | send/notify/inform … absent/absence/attendance … parents |
  | fee.send_reminders | send/remind … reminders/defaulters |
  | exam.publish | publish/release … exam/result/marks/report card |
  | homework.create | create/add/assign/give/set … homework/assignment/task |
  | transport.assign | assign/allocate/add/put … route/bus/transport |
  | notification.send | send/notify/inform/announce/broadcast/push … notification/announcement/…/students/parents/… |

- Added guards so READ requests are never misclassified as actions:
  - First-person recipients (`send me`, `show me`, `notify me`, …) → query.
  - Question markers (`how many`, `which`, `?`, …) → query.
- Kept the old exact-phrase keywords as a backward-compatible second pass.
- **Provider catalog now includes action tools** (`combinedCatalog()` =
  `all() + actionTools()`). `normalize()` marks any tool without a
  `result_type` as `action`, so the LLM can select an action tool and it still
  flows through the confirmation gate.
- `extractActionParams()` now derives meaningful parameters for
  `notification.send` (target_type, message, title), `homework.create`,
  `exam.publish`, and `transport.assign` for the confirmation preview.

### 3.2 `app/Modules/AiAssistant/Services/AIService.php`

- `notification.send` execution fills the required Notification columns
  (`type=announcement`, `priority=medium`, `status=sent`, `channel=in_app`)
  so the confirmed action cannot fail on NOT NULL columns.
- Action result summary now reports the notification title/audience.

### Security invariant (unchanged)

Action execution still REQUIRES confirmation. `AIService::ask()` →
`isAction()` → `handleActionIntent()` → confirmation gate → execution only
after `confirmed=true`. The LLM can never bypass confirmation — the same gate
applies whether the action was detected by rules or proposed by the provider.

---

## 4. CORRECT BEHAVIOR NOW

```
User Input
  "Send a notification to all students ... Sports Day."
      │
      ▼
QueryPlanner::detectAction()   → notification.send (action)
      │
      ▼
Authorization (RoleDataScoper) → allowed (Super Admin / School Admin / Principal)
      │
      ▼
AgentRouter::route('notification.send')
      │
      ▼
Confirmation required  →  "Send notification ... to students? This will be
                          delivered immediately."
      │
      ▼  (user confirms)
NotificationService::create(...)  →  "Action Complete"
```

Verified in `tests/Feature/AiActionDetectionTest.php` (17 tests):

- The exact reported sentence → `notification.send`, `action`,
  `target_type=students`, message contains "colour dress".
- It returns `confirmation_required=true` (not records), no `result`, no
  "matching records" text.
- With confirmation → action proceeds (Notification row created).
- Natural variants ("Notify all parents", "Create homework for Class 5",
  "Publish the Mathematics exam", "Assign this student to Route 3",
  "Generate the payroll", "Send fee reminders to defaulters",
  "Send absence notifications to parents") all classify as actions.
- Read requests stay queries ("Show all exams in January", "How many students
  are in Class 1?", "Who is absent today?", "Show today's transport status",
  "How much fees are pending?").
- "Send me the list of absent students" is NOT a broadcast.
- A Teacher (no notification permission) is denied the action.

---

## 5. FILES CHANGED

| File | Change |
|---|---|
| `app/Modules/AiAssistant/Erp/QueryPlanner.php` | Regex action detection + guards; combined provider catalog; richer action params |
| `app/Modules/AiAssistant/Services/AIService.php` | Notification execution defaults; result summary |
| `docs/development/AI.md` | Documented action-vs-query classification |
| `tests/Feature/AiActionDetectionTest.php` | 17-test regression suite (added) |

Full AI + feature regression: **passing** (no existing test was weakened; the
new suite pins the corrected behavior).
