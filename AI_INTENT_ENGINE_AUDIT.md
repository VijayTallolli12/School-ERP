# AI Intent Engine — Audit Report

## 1. Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                       Ask ERP Modal (UI)                        │
│                      POST /admin/ai/ask                         │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                      AIController                                │
│            Validates input, passes question + confirmed flag     │
└──────────────────────────┬──────────────────────────────────────┘
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│                       AIService (Orchestrator)                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────────┐  ┌─────────────┐ │
│  │Intent    │  │Agent     │  │Handler/Agent │  │Response     │ │
│  │Resolve   │─▶│Router    │─▶│Execute       │─▶│Formatter    │ │
│  └────┬─────┘  └──────────┘  └──────────────┘  └──────┬──────┘ │
│       │                                                 │       │
│  ┌────▼─────┐                                   ┌──────▼──────┐ │
│  │Gemini LLM│                                   │Gemini LLM   │ │
│  │(intent)  │                                   │(summary)    │ │
│  └──────────┘                                   └─────────────┘ │
│       │                                                 │       │
│  ┌────▼─────┐                                           │       │
│  │Fallback  │                                           │       │
│  │(keyword) │                                           │       │
│  └──────────┘                                           │       │
└─────────────────────────────────────────────────────────┴───────┘
```

### Layers

| Layer | File | Responsibility |
|-------|------|----------------|
| Controller | `AIController.php` | Validates HTTP input, passes question + confirmation flag |
| Orchestrator | `AIService.php` | Routes question through intent resolution → routing → execution → formatting |
| Intent Engine | `AIIntentService.php` | Primary: Gemini LLM; Fallback: Keyword parser (`IntentResolver.php`) |
| Router | `AgentRouter.php` | Maps resolved intent to existing handler/agent/service |
| Handlers | `*QueryHandler.php` (6 files) | Execute read-only database queries (unchanged) |
| Agents | `*Agent.php` (4 files) | Execute business actions (Payroll, Attendance, Fee, Library) |
| Formatter | `AIResponseFormatter.php` | Optional: Gemini produces professional summary from ERP data only |

### Data Flow

1. **User submits question** → `AIController::ask()`
2. **Intent Resolution** → `AIIntentService::resolve()`
   - Sends prompt to Gemini API (temperature 0.1, strict JSON)
   - On failure → falls back to keyword-based `IntentResolver`
3. **Routing** → `AgentRouter::route()` maps intent to handler/agent/service
4. **Destructive Check** → If intent requires confirmation and `confirmed=false`, return `confirmation_required=true`
5. **Execution** → Call handler method, agent execution, or service method
6. **Formatting** → `AIResponseFormatter::format()` optionally summarizes via Gemini (temperature 0.2)
7. **Response** → JSON returned to UI

---

## 2. Prompt Strategy

### Intent Classification Prompt (AIIntentService)

```
You are an intent classification engine for a School ERP system.
Your ONLY job is to analyze user queries and return a JSON object.

RULES:
- Return ONLY valid JSON. No markdown, no code fences, no explanations.
- Never execute any ERP logic or generate fake data.
- Never fabricate parameter values not present in the query.
- If the query is unclear or doesn't match any intent, set intent to "unknown".

SUPPORTED INTENTS:
{list of all intents with descriptions}

Analyze this question and respond with ONLY this JSON structure:
{"intent": "string", "parameters": {}, "confidence": 0.0}
```

### Response Summary Prompt (AIResponseFormatter)

```
You are a professional business report writer for a School ERP system.

RULES:
- Write a clear, professional summary based ONLY on the data provided.
- NEVER fabricate numbers, names, or any facts.
- NEVER add information not present in the data.
- NEVER mention that you are an AI or language model.
- Keep it concise (3-5 sentences).
- Use professional business language.
```

### Design Principles

- **Temperature 0.1** for intent classification (deterministic, focused)
- **Temperature 0.2** for summarization (slightly creative, professional)
- **Explicit "never fabricate" instructions** prevent hallucination
- **Strict JSON enforcement** via `maxOutputTokens: 256` and prompt constraint
- **Post-processing**: Strip code fences, validate JSON schema, verify intent exists in supported list

---

## 3. Supported Intents

### Query Intents (Read-Only)

| Intent | Handler | Method | Parameters |
|--------|---------|--------|------------|
| `student.total` | StudentQueryHandler | totalStudents | — |
| `student.admitted_this_month` | StudentQueryHandler | admittedThisMonth | — |
| `student.by_class` | StudentQueryHandler | studentsByClass | — |
| `attendance.absent_today` | AttendanceQueryHandler | absentToday | — |
| `attendance.monthly_percentage` | AttendanceQueryHandler | monthlyPercentage | — |
| `attendance.below_75` | AttendanceQueryHandler | studentsBelow75 | — |
| `fee.outstanding` | FeeQueryHandler | totalOutstanding | — |
| `fee.pending_above` | FeeQueryHandler | studentsWithPendingAbove | `amount` |
| `fee.today_collection` | FeeQueryHandler | todayCollection | — |
| `fee.top_defaulters` | FeeQueryHandler | topDefaulters | — |
| `transport.route_occupancy` | TransportQueryHandler | routeOccupancy | — |
| `transport.students_on_route` | TransportQueryHandler | studentsOnRoute | — |
| `transport.vehicle_assignments` | TransportQueryHandler | vehicleAssignments | — |
| `library.books_issued` | LibraryQueryHandler | booksIssued | — |
| `library.overdue_books` | LibraryQueryHandler | overdueBooks | — |
| `library.fine_collection` | LibraryQueryHandler | fineCollection | — |
| `payroll.latest_run` | PayrollQueryHandler | latestRun | — |
| `payroll.locked_runs` | PayrollQueryHandler | lockedRuns | — |
| `payroll.highest_salary` | PayrollQueryHandler | highestSalaryEmployees | `limit` |
| `payroll.generated_this_month` | PayrollQueryHandler | generatedThisMonth | — |

### Action Intents (Destructive — Confirmation Required)

| Intent | Route Type | Target | Parameters |
|--------|-----------|--------|------------|
| `payroll.generate` | Agent | PayrollAgent | `month`, `year` |
| `attendance.notify` | Agent | AttendanceAgent | `date` |
| `fee.send_reminders` | Agent | FeeCollectionAgent | `days` |
| `exam.publish` | Service | ExamService::publish | `exam_id` |
| `notification.send` | Service | NotificationService::create | `title`, `message`, `target_type` |

### Action Intents (Non-Destructive)

| Intent | Route Type | Target | Parameters |
|--------|-----------|--------|------------|
| `homework.create` | Service | HomeworkService::create | `class_section_id`, `subject_id`, `title`, `due_date` |
| `transport.assign` | Service | TransportService | `route_id`, `student_ids` |

---

## 4. Security

### API Key Management
- **No hardcoded key** in source code.
- Key stored in `.env`: `GEMINI_API_KEY=your_key_here`
- Retrieved via `config('services.gemini.api_key')` from `config/services.php`
- `.env` is in `.gitignore` (verify with your team)

### Prompt Injection Mitigation
- User input is embedded directly into the prompt — the system prompt instructs Gemini to return ONLY JSON and NEVER execute ERP logic.
- The `temperature: 0.1` setting minimizes creative偏离.
- Output is validated against a strict list of supported intents.
- Unknown intents return `intent: "unknown"` which is handled upstream.

### Destructive Action Guard
- 5 intents are flagged as destructive in `AgentRouter::DESTRUCTIVE_INTENTS`.
- Without `confirmed=true` POST parameter, these return `confirmation_required=true` and are NOT executed.
- Execution only proceeds when the user explicitly confirms.

### Service Boundaries
- Gemini NEVER has access to the database or ERP business logic.
- Gemini ONLY classifies intent and optionally formats summaries.
- All data queries go through existing handler classes with proper authorization (SchoolContext, permissions).
- Agent executions are tracked via `AgentExecution` model and Spatie Activity Log.

### Error Handling
- Gemini API failure → silent fallback to keyword-based `IntentResolver`.
- Missing API key → automatic fallback (no Gemini calls attempted).
- Invalid JSON from Gemini → caught and falls back.
- All errors logged via `Log::warning` / `Log::error`.
- Controller catches exceptions and returns user-friendly error message.

---

## 5. JSON Schema

### Intent Resolution Response

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["intent", "parameters", "confidence"],
  "properties": {
    "intent": {
      "type": "string",
      "description": "The resolved intent key from supported intents list, or 'unknown'",
      "examples": ["fee.outstanding", "attendance.absent_today", "unknown"]
    },
    "parameters": {
      "type": "object",
      "description": "Parameters extracted from the query",
      "properties": {
        "amount": { "type": "number", "description": "Monetary threshold for fee queries" },
        "month": { "type": "number", "description": "Month number (1-12)" },
        "year": { "type": "number", "description": "Year (e.g. 2026)" },
        "date": { "type": "string", "description": "Date in YYYY-MM-DD format" },
        "days": { "type": "number", "description": "Number of days lookback" },
        "exam_id": { "type": "number" },
        "limit": { "type": "number" },
        "title": { "type": "string" },
        "message": { "type": "string" },
        "target_type": { "type": "string" },
        "class_section_id": { "type": "number" },
        "subject_id": { "type": "number" },
        "due_date": { "type": "string" },
        "route_id": { "type": "number" },
        "student_ids": { "type": "array", "items": { "type": "number" } }
      },
      "additionalProperties": false
    },
    "confidence": {
      "type": "number",
      "minimum": 0.0,
      "maximum": 1.0,
      "description": "Confidence score of the intent classification"
    },
    "source": {
      "type": "string",
      "enum": ["gemini", "fallback"],
      "description": "Indicates whether Gemini or keyword fallback resolved the intent"
    }
  },
  "additionalProperties": false
}
```

### API Response (from AIController)

```json
{
  "success": true,
  "answer": "Professional summary or raw answer text",
  "intent": "fee.outstanding",
  "confidence": 0.95,
  "confirmation_required": false,
  "agent_recommendation": {
    "agent": "fee_collection",
    "label": "Fee Collection Agent",
    "params": { "days": 30 }
  }
}
```

For destructive actions without confirmation:

```json
{
  "success": true,
  "answer": "This will generate and lock payroll for month: 7, year: 2026. This action cannot be undone.",
  "intent": "payroll.generate",
  "confidence": 0.94,
  "confirmation_required": true,
  "parameters": { "month": 7, "year": 2026 },
  "agent_recommendation": {
    "agent": "payroll",
    "label": "Payroll Agent",
    "params": { "month": 7, "year": 2026 }
  }
}
```

---

## 6. Example Requests

### Example 1: Fee Query (Gemini)

**Request:**
```http
POST /admin/ai/ask
Content-Type: application/json

{
  "question": "What is the total outstanding fee?",
  "confirmed": false
}
```

**Gemini Response (parsed):**
```json
{
  "intent": "fee.outstanding",
  "parameters": {},
  "confidence": 0.97
}
```

**Final Response:**
```json
{
  "success": true,
  "answer": "Total outstanding fees: ₹1,25,000.00 (out of ₹5,00,000.00 total assigned)",
  "intent": "fee.outstanding",
  "confidence": 0.93,
  "confirmation_required": false,
  "agent_recommendation": {
    "agent": "fee_collection",
    "label": "Fee Collection Agent",
    "params": { "days": 30 }
  }
}
```

### Example 2: Destructive Action (Payroll, No Confirmation)

**Request:**
```http
POST /admin/ai/ask
Content-Type: application/json

{
  "question": "Generate payroll for July 2026",
  "confirmed": false
}
```

**Response:**
```json
{
  "success": true,
  "answer": "This will generate and lock payroll for Month: 7, Year: 2026. This action cannot be undone.",
  "intent": "payroll.generate",
  "confidence": 0.94,
  "confirmation_required": true,
  "parameters": { "month": 7, "year": 2026 },
  "agent_recommendation": {
    "agent": "payroll",
    "label": "Payroll Agent",
    "params": { "month": 7, "year": 2026 }
  }
}
```

### Example 3: Destructive Action (Payroll, Confirmed)

**Request:**
```http
POST /admin/ai/ask
Content-Type: application/json

{
  "question": "Generate payroll for July 2026",
  "confirmed": true
}
```

**Response:**
```json
{
  "success": true,
  "answer": "Payroll for July 2026 has been generated and locked. 45 employees processed. Total gross: ₹18,50,000.00, Total net: ₹14,80,000.00.",
  "intent": "payroll.generate",
  "confidence": 0.94,
  "confirmation_required": false,
  "execution": {
    "month": 7,
    "year": 2026,
    "success": true,
    "payroll_run_id": 12,
    "total_employees": 45,
    "total_gross": 1850000.00,
    "total_net": 1480000.00,
    "payslips_generated": 45,
    "records_processed": 45
  }
}
```

### Example 4: Unknown Query → Fallback

**Request:**
```http
POST /admin/ai/ask
Content-Type: application/json

{
  "question": "What is the meaning of life?"
}
```

**Response:**
```json
{
  "success": false,
  "answer": "I couldn't understand your question. Try asking about:\n• Student: total students, admitted this month, students by class\n• Attendance: absent today, monthly attendance, attendance below 75\n...",
  "intent": "unknown",
  "confidence": 0.0
}
```

### Example 5: Gemini Unavailable → Keyword Fallback

When `GEMINI_API_KEY` is missing or Gemini API returns an error:

**Response:**
```json
{
  "success": true,
  "answer": "Students absent today (2026-07-03): 12 (out of 450 marked)",
  "intent": "attendance.absent_today",
  "confidence": 0.6,
  "source": "fallback"
}
```

---

## 7. Files Modified / Created

### New Files
| File | Purpose |
|------|---------|
| `app/Modules/AiAssistant/Services/AIIntentService.php` | Gemini-powered intent classification with keyword fallback |
| `app/Modules/AiAssistant/Services/AgentRouter.php` | Intent-to-handler/agent/service routing with destructive action flags |
| `app/Modules/AiAssistant/Services/AIResponseFormatter.php` | Optional Gemini-powered response summarization |

### Modified Files
| File | Change |
|------|--------|
| `.env` | Added `GEMINI_API_KEY`, `GEMINI_MODEL` |
| `.env.example` | Added `GEMINI_API_KEY`, `GEMINI_MODEL` |
| `config/services.php` | Added `gemini` config array |
| `app/Modules/AiAssistant/Services/AIService.php` | Integrated AIIntentService, AgentRouter, AIResponseFormatter; added destructive action handling |
| `app/Modules/AiAssistant/Controllers/AIController.php` | Added optional `confirmed` boolean parameter |

### Unchanged Files
- `app/Modules/AiAssistant/Handlers/*` (6 query handlers)
- `app/Modules/AiAgents/Agents/*` (4 agents)
- `app/Modules/AiAssistant/Services/IntentResolver.php` (retained as fallback)
- `resources/views/modules/ai-assistant/modal.blade.php` (UI untouched)
- `routes/modules/ai_assistant.php` (route unchanged)

---

## 8. Configuration Reference

```env
# .env
GEMINI_API_KEY=your_google_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash
GEMINI_TIMEOUT=30
```

```php
// config/services.php
'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    'timeout' => env('GEMINI_TIMEOUT', 30),
],
```
