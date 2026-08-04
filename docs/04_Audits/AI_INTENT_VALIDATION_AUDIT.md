# AI Intent Validation Audit — Phase AI-1.1

## 1. Updated Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                       Ask ERP Modal (UI)                            │
│                    POST /admin/ai/ask                               │
│              [confirmed flag for destructive actions]                │
└───────────────────────────┬─────────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────────┐
│                       AIController                                  │
│         Validates input: question + confirmed (boolean)             │
└───────────────────────────┬─────────────────────────────────────────┘
                            │
┌───────────────────────────▼─────────────────────────────────────────┐
│                      AIService (Orchestrator)                       │
│                                                                     │
│  ┌──────────────┐  ┌───────────┐  ┌──────────────┐  ┌───────────┐  │
│  │ AIIntent     │─▶│Agent      │─▶│Handler/Agent │─▶│Response   │  │
│  │ Service      │  │Router     │  │Execute       │  │Formatter  │  │
│  └──────┬───────┘  └───────────┘  └──────────────┘  └─────┬─────┘  │
│         │                                                   │        │
│  ┌──────▼───────┐                                    ┌─────▼─────┐  │
│  │ Gemini LLM   │                                    │Gemini LLM │  │
│  │ (intent +    │                                    │(executive │  │
│  │  params)     │                                    │ summary)  │  │
│  └──────┬───────┘                                    └─────┬─────┘  │
│         │                                                  │        │
│  ┌──────▼───────┐                                    ┌─────▼─────┐  │
│  │ Keyword      │                                    │Local      │  │
│  │ Fallback     │                                    │Fallback   │  │
│  └──────────────┘                                    └───────────┘  │
│                                                                     │
│  ┌──────────────┐                                                   │
│  │Debug Logger  │ (dev mode only)                                   │
│  └──────────────┘                                                   │
└─────────────────────────────────────────────────────────────────────┘
```

### Components Modified

| File | Change |
|------|--------|
| `AIIntentService.php` | Domain-aware system prompt, structured parameter extraction, synonym normalization, action/query classification |
| `AIService.php` | Debug logging, executive payroll confirmation, school summary orchestration |
| `AIResponseFormatter.php` | Executive response templates, school summary formatting, action result formatting |
| `AgentRouter.php` | Added `school.summary` intent route |
| `SchoolSummaryHandler.php` | **NEW** — Aggregates data from 8 ERP modules |
| `modal.blade.php` | Confirmation flow UI (confirm/cancel buttons for destructive actions) |

### Files Unchanged

- `AIController.php` — No changes needed
- `IntentResolver.php` — Retained as fallback
- All 6 query handlers — Untouched
- All 4 agents — Untouched

---

## 2. Prompt Strategy

### Intent Classification Prompt (AIIntentService)

**Domain Context**: The prompt now teaches Gemini about ALL 16 ERP modules:
Students, Parents, Teachers, Attendance, Fees, Payroll, Homework, Exams, Library, Transport, Notifications, Leave, Timetable, Calendar, Reports.

**Intent Lists**: Separated into QUERY_INTENTS and ACTION_INTENTS for clarity.

**Rules Enforced**:
1. Return ONLY valid JSON (temperature 0.1)
2. NEVER execute ERP logic
3. NEVER invent modules or intents
4. ALWAYS choose closest supported intent
5. Extract ALL relevant parameters
6. Resolve relative dates to actual dates
7. Extract month names, grouping, sorting, limits

**Parameter Schema**: Explicit field definitions for each parameter type:
- `period`: today/yesterday/this_week/current_month/last_month/current_year
- `month`: 1-12 (resolved from month name)
- `year`: number (resolved from context)
- `amount`: monetary threshold
- `limit`: row limit
- `group_by`: class/section/teacher/route/department
- `sort`: asc/desc

**Response Format**:
```json
{
  "intent": "fee.outstanding",
  "parameters": {"period": "current_month", "group_by": "class"},
  "confidence": 0.98,
  "action": "query"
}
```

### Response Summary Prompt (AIResponseFormatter)

**Executive Quality**: Reports now include:
- Report title as heading
- Key metrics with labels
- Business insight (1-2 sentences)
- Recommendation (1 sentence)

**School Summary Prompt**: Aggregates data from 8 modules into a principal-friendly briefing with:
- Section headings per module
- Key metrics per section
- Overall school health assessment
- Priority actions needed

### Design Principles

- **Temperature 0.1** for intent classification (deterministic)
- **Temperature 0.2** for response formatting (professional)
- **Temperature 0.3** for school summary (slightly more creative)
- **`responseMimeType: application/json`** enforced for intent responses
- **Local fallback** when Gemini unavailable (no data loss)

---

## 3. Parameter Extraction Rules

### Date Resolution

| Input Pattern | Resolved Parameter |
|--------------|-------------------|
| today, current day, this day | period: "today" |
| yesterday | period: "yesterday" |
| tomorrow | period: "tomorrow" |
| this week, current week | period: "this_week" |
| last week, previous week | period: "last_week" |
| this month, monthly, current month | period: "current_month" |
| last month, previous month | period: "last_month" |
| this year, current year | period: "current_year" |

### Month Extraction

| Input | Resolved |
|-------|----------|
| January, Jan | month: 1 |
| February, Feb | month: 2 |
| March, Mar | month: 3 |
| April, Apr | month: 4 |
| May | month: 5 |
| June, Jun | month: 6 |
| July, Jul | month: 7 |
| August, Aug | month: 8 |
| September, Sep | month: 9 |
| October, Oct | month: 10 |
| November, Nov | month: 11 |
| December, Dec | month: 12 |

### Academic Filters

| Input Pattern | Parameter |
|--------------|-----------|
| class wise, class-wise, by class | group_by: "class" |
| section wise, section-wise, by section | group_by: "section" |
| teacher wise, by teacher | group_by: "teacher" |
| route wise, by route | group_by: "route" |
| department wise, by department | group_by: "department" |

### Numeric Extraction

| Pattern | Parameter |
|---------|-----------|
| above 5000 | amount: 5000 |
| top 5 employees | limit: 5 |
| show me 10 students | limit: 10 |
| for 60 days | days: 60 |

---

## 4. Supported Synonyms

### Query Synonyms

| Synonym | Canonical Intent |
|---------|-----------------|
| pending fee, fee due, outstanding fee, fee balance, fee pending | fee.outstanding or fee.pending_above |
| salary, employee salary, salaries | payroll.* |
| bus, school bus, van | transport.* |
| class wise, by class | student.by_class or group_by=class |
| section wise, by section | group_by=section |
| teacher wise, by teacher | group_by=teacher |
| route wise, by route | group_by=route |
| department wise, by department | group_by=department |

### Sort Synonyms

| Synonym | Value |
|---------|-------|
| highest, top, best | sort: "desc" |
| lowest, bottom, worst | sort: "asc" |

### Metric Synonyms

| Synonym | Value |
|---------|-------|
| present | status_present |
| absent | status_absent |
| late | status_late |
| completed | status_completed |
| running | status_running |
| scheduled | status_scheduled |
| paid | status_paid |

---

## 5. Intent Mapping

### Query Intents (21)

| Intent | Handler | Method |
|--------|---------|--------|
| student.total | StudentQueryHandler | totalStudents |
| student.admitted_this_month | StudentQueryHandler | admittedThisMonth |
| student.by_class | StudentQueryHandler | studentsByClass |
| attendance.absent_today | AttendanceQueryHandler | absentToday |
| attendance.monthly_percentage | AttendanceQueryHandler | monthlyPercentage |
| attendance.below_75 | AttendanceQueryHandler | studentsBelow75 |
| fee.outstanding | FeeQueryHandler | totalOutstanding |
| fee.pending_above | FeeQueryHandler | studentsWithPendingAbove |
| fee.today_collection | FeeQueryHandler | todayCollection |
| fee.top_defaulters | FeeQueryHandler | topDefaulters |
| transport.route_occupancy | TransportQueryHandler | routeOccupancy |
| transport.students_on_route | TransportQueryHandler | studentsOnRoute |
| transport.vehicle_assignments | TransportQueryHandler | vehicleAssignments |
| library.books_issued | LibraryQueryHandler | booksIssued |
| library.overdue_books | LibraryQueryHandler | overdueBooks |
| library.fine_collection | LibraryQueryHandler | fineCollection |
| payroll.latest_run | PayrollQueryHandler | latestRun |
| payroll.locked_runs | PayrollQueryHandler | lockedRuns |
| payroll.highest_salary | PayrollQueryHandler | highestSalaryEmployees |
| payroll.generated_this_month | PayrollQueryHandler | generatedThisMonth |
| school.summary | SchoolSummaryHandler | getExecutiveSummary |

### Action Intents (7)

| Intent | Route Type | Target | Destructive |
|--------|-----------|--------|-------------|
| payroll.generate | agent | PayrollAgent | Yes |
| attendance.notify | agent | AttendanceAgent | Yes |
| fee.send_reminders | agent | FeeCollectionAgent | Yes |
| exam.publish | service | ExamService | Yes |
| notification.send | service | NotificationService | Yes |
| homework.create | service | HomeworkService | No |
| transport.assign | service | TransportService | No |

---

## 6. Debug Logging

**Scope**: Development mode only (`APP_ENV=local|development`)

**Channel**: `daily` log channel

**Logged Fields**:

| Field | Description |
|-------|-------------|
| query | Original user query |
| intent | Resolved intent key |
| parameters | Extracted parameters |
| confidence | Classification confidence |
| action | query or action |
| source | gemini or fallback |
| route_type | handler, agent, or service |
| handler/agent/service | Selected target |
| confirmation_required | Boolean |
| success | Execution success |
| execution_time_ms | Total pipeline time |

**Example Log Entry**:
```
[2026-07-03 10:15:32] local.DEBUG: [AI Copilot] Intent resolved {
    "query": "How much fee is pending this month?",
    "intent": "fee.outstanding",
    "parameters": {"period": "current_month"},
    "confidence": 0.98,
    "action": "query",
    "source": "gemini"
}
```

**Production Behavior**: Logging is silently disabled via `app()->environment()` check.

---

## 7. Performance

### Gemini API Calls

| Stage | Timeout | Max Tokens | Temperature |
|-------|---------|-----------|-------------|
| Intent Classification | 30s | 512 | 0.1 |
| Response Formatting | 30s | 512 | 0.2 |
| School Summary | 30s | 768 | 0.3 |
| Action Result | 30s | 384 | 0.2 |

### Fallback Behavior

- Missing API key → instant keyword fallback (no HTTP call)
- Gemini HTTP error → keyword fallback (logged)
- Invalid JSON from Gemini → keyword fallback (logged)
- Gemini timeout → keyword fallback (logged)

### Local Formatting

When Gemini is unavailable, the `AIResponseFormatter` uses local templates:
- `formatLocal()` — Simple report with title and data
- `buildSchoolSummaryLocal()` — Section-based summary from 8 modules
- `buildActionResultLocal()` — Action result with status and metrics

---

## 8. Validation Results

### Test Coverage

| Metric | Target | Status |
|--------|--------|--------|
| Total Test Cases | 60 | Defined in `AI_VALIDATION_TEST_SHEET.md` |
| Intent Accuracy | 95%+ | Pending live validation with API key |
| Parameter Accuracy | 90%+ | Pending live validation |
| Wrong Agent Routing | 0 | Route map verified statically |
| Invalid JSON | 0 | All paths return valid JSON structure |
| PHP Syntax | Pass | All files pass `php -l` |
| Existing Tests | 101/102 pass | 1 pre-existing failure in LiveAttendanceTest |

### Static Validation

- All 28 intents have valid route mappings in `AgentRouter`
- All 7 destructive intents are in `DESTRUCTIVE_INTENTS` list
- All handler methods exist in their respective handler classes
- All agent names match registered agent names
- All service method names exist on their respective services

---

## 9. Remaining Improvements

### Near-Term

1. **Live Gemini Validation**: Run all 60 test cases against Gemini API and record accuracy metrics
2. **Teacher Query Handler**: Add teacher-specific queries (total teachers, absent today, etc.)
3. **Leave Query Handler**: Add leave-specific queries (pending requests, approved today, etc.)
4. **Timetable Query Handler**: Add timetable queries (today's schedule, current period, etc.)

### Medium-Term

1. **Conversation Memory**: Track previous queries for context-aware follow-ups
2. **Proactive Insights**: Suggest actions based on data anomalies (e.g., "3 students haven't paid fees in 90 days")
3. **Role-Based Responses**: Tailor responses based on user role (principal vs teacher vs parent)
4. **Multi-Language Support**: Extend prompts to support Hindi and regional languages

### Long-Term

1. **Voice Integration**: Add speech-to-text for hands-free queries
2. **Scheduled Reports**: Auto-generate daily/weekly summaries
3. **Predictive Analytics**: Use historical data to forecast trends
4. **Mobile Push Integration**: Send executive summaries via push notifications

---

## 10. Files Changed

### New Files
| File | Purpose |
|------|---------|
| `app/Modules/AiAssistant/Handlers/SchoolSummaryHandler.php` | Multi-module executive summary aggregation |
| `AI_VALIDATION_TEST_SHEET.md` | 60 test cases for intent validation |
| `AI_INTENT_VALIDATION_AUDIT.md` | This document |

### Modified Files
| File | Change |
|------|--------|
| `app/Modules/AiAssistant/Services/AIIntentService.php` | Domain prompt, parameter extraction, synonym map, action/query classification |
| `app/Modules/AiAssistant/Services/AIService.php` | Debug logging, executive responses, school summary orchestration |
| `app/Modules/AiAssistant/Services/AIResponseFormatter.php` | Executive templates, school summary, action result formatting |
| `app/Modules/AiAssistant/Services/AgentRouter.php` | Added school.summary route |
| `resources/views/modules/ai-assistant/modal.blade.php` | Confirmation flow UI |

### Unchanged Files
- `app/Modules/AiAssistant/Controllers/AIController.php`
- `app/Modules/AiAssistant/Services/IntentResolver.php`
- `app/Modules/AiAssistant/Handlers/StudentQueryHandler.php`
- `app/Modules/AiAssistant/Handlers/AttendanceQueryHandler.php`
- `app/Modules/AiAssistant/Handlers/FeeQueryHandler.php`
- `app/Modules/AiAssistant/Handlers/TransportQueryHandler.php`
- `app/Modules/AiAssistant/Handlers/LibraryQueryHandler.php`
- `app/Modules/AiAssistant/Handlers/PayrollQueryHandler.php`
- `app/Modules/AiAgents/Agents/*` (all 4 agents)
- `config/services.php` (no changes needed)
- `.env` / `.env.example` (no changes needed)
