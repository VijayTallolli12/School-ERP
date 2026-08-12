# AI Module

Version: 2.0.0

Revision date: 2026-08-12

## Purpose

The AI module provides natural-language access to the School ERP ("Ask ERP")
and the management briefing dashboard ("Executive Gemini"). Both features
share one intelligence layer: the user question is converted into a
**structured ERP query**, executed against the real database with strict
tenant/role scoping, validated, and only then converted back into a
natural-language answer. The AI never invents ERP data.

## Architecture

```
User question
    │
    ▼
AIController (/admin/ai/ask)
    │
    ▼
AIService ──────────────► QueryPlanner (NL → structured tool request)
    │                             │  └─ LLM provider (Gemini/OpenAI) + rule fallback
    │                             ▼
    │                    ErpToolRegistry (single source of truth for tools)
    │                             │
    │                             ▼
    │                    ErpQueryExecutor (validate, tenant + role scope, dispatch)
    │                             │
    │                             ▼
    │                    ERP domain handlers / services → Database
    │                             │
    │                             ▼
    │                    Validated result envelope
    │                             │
    │                             ▼
    │                    AiResponseGenerator (answer strictly from result data)
    │                             │
    └─────────────────────────────┘
    │
    ▼
AiRequestLogger (AiQueryLog)
```

## Key components

- `App\Modules\AiAssistant\Erp\QueryPlanner` — turns natural language into
  `{tool, parameters, confidence, action}` using the configured LLM provider
  with a deterministic keyword/date/synonym fallback.
- `App\Modules\AiAssistant\Erp\ErpToolRegistry` — the single source of truth
  for all query tools (exam.search, fee.pending, attendance.summary, …) and
  action tools (payroll.generate, attendance.notify, …). Handles exam-type,
  status, and attendance-status synonym normalization.
- `App\Modules\AiAssistant\Erp\NaturalLanguageDateParser` — parses
  today / yesterday / this week / last month / "January 2026" / "15 Jan 2026" /
  "between Jan and Mar 2026" into concrete date_from/date_to ranges.
- `App\Modules\AiAssistant\Erp\ErpQueryExecutor` — whitelists parameters,
  resolves entity names (class/subject) to IDs, applies role scope filters and
  the school tenant boundary, then dispatches to the domain handler.
- `App\Modules\AiAssistant\Erp\AiResponseGenerator` — builds the final answer
  from the validated result; never claims data exists unless the query
  returned records.
- `App\Modules\AiAssistant\Erp\AiRequestLogger` — logs user, school, question,
  intent, structured parameters, result count, execution time and status
  (never API keys or tokens).
- `App\Modules\AiAssistant\Providers\*` — provider abstraction. Configure via
  `AI_PROVIDER=gemini|openai`; credentials come from `GEMINI_API_KEY` /
  `OPENAI_API_KEY` in `.env` (backend only, never the browser).
- `App\Modules\AiAssistant\Handlers\*` — school-scoped query handlers for
  students, attendance, fees, homework, teachers, leave, transport, library,
  payroll, exams, and the executive school summary.

Action intents (payroll.generate, attendance.notify, fee.send_reminders,
exam.publish, notification.send, homework.create, transport.assign) require an
explicit confirmation before any side effect runs.

## Database Tables

- ai_query_logs
- agent_executions

## Models

- App\Modules\AiAssistant\Models\AiQueryLog

## Controllers

- AIController
- AgentController

## Routes

- POST /admin/ai/ask (Ask ERP + Executive Gemini chat)
- GET  /admin/ai/dashboard (Executive Gemini dashboard)
- /admin/agents (Manual AI agents)

## Permissions

- Role-based intent authorization via `config/ai.php` → `role_permissions`.
- Super Admin / School Admin / Principal have `*`.
- All database queries are scoped to `SchoolContext` (tenant boundary).

## Business Rules

- AI actions must be authorized based on role and intent.
- Sensitive or destructive intents require confirmation and logging.
- AI responses are scoped to the active school context.
- The AI must not expose another school's data.
- The AI must not execute raw SQL; it only selects allowed tools.

## Workflow

1. A user submits a natural language question.
2. QueryPlanner converts it into a structured tool request.
3. The tool is validated, authorized, and scoped.
4. The ERP domain handler queries the real database.
5. The result is validated and formatted into an answer.
6. The request is logged (AiQueryLog).

## Common Issues

- Unknown intents return a friendly fallback listing supported areas.
- If the LLM provider is unreachable the rule-based fallback planner is used,
  so the assistant still answers from real ERP data.
- The `/admin/ai/ask` endpoint is rate limited per authenticated user
  (`AI_RATE_LIMIT_PER_MINUTE`, default 20/min) to guard against flooding,
  abuse, and API cost spikes.

## Troubleshooting

- Review the `ai_query_logs` table for the unresolved intent/parameters.
- Check `storage/logs/laravel-<date>.log` for `[AI Request]` entries.
- Confirm the user's role has the matching `role_permissions` pattern.

## Security & Hardening

- All database queries are scoped to the authenticated school tenant
  (`SchoolContext`); caller-supplied `school_id`/`school` parameters are
  stripped by `ErpQueryExecutor`.
- Role scope filters from `RoleDataScoper` (teacher `class_section_ids`,
  parent `student_ids`, student `student_id`) are enforced inside the query
  handlers, so a teacher can only see their own classes' data.
- Action tools (payroll.generate, attendance.notify, fee.send_reminders,
  exam.publish, notification.send, homework.create, transport.assign) all
  require explicit confirmation before any side effect runs.
- **Pending-action confirmation workflow.** When an action is detected, a
  pending action is persisted server-side (`ai_pending_actions`), bound to the
  authenticated user + school, with the exact validated parameters and a short
  TTL (`AI_PENDING_ACTION_TTL_MINUTES`, default 10). The next message is
  checked against the pending action FIRST: "Sure"/"Yes"/"Confirm" executes it
  atomically (no double submission), "No"/"Cancel" discards it, ambiguous
  replies ask again, and modification phrases ("Actually, only to Class 5…")
  supersede the old action. The query planner only runs when no action is
  awaiting confirmation. See `docs/development/AI_PENDING_ACTION_FIX.md`.
- **Action vs query classification** is enforced in `QueryPlanner::detectAction`
  (runs before the provider and before the rule fallback). Natural-language
  action requests are matched with regex patterns that tolerate articles and
  word order ("send a notification" ≠ the exact phrase "send notification"),
  ordered most-specific-first so "send absence notification to parents" maps to
  `attendance.notify` and not the generic `notification.send`. First-person
  requests ("send me the list…") and explicit question markers stay queries.
  The provider is given the full catalog (query tools + action tools) so the
  LLM can also select an action tool; `normalize()` marks tools without a
  `result_type` as actions, which still require confirmation.
- The LLM response prompt explicitly treats ERP record content as untrusted
  DATA: embedded instructions in records must never change permissions, tool
  selection, school scope, or the requested operation.
- Role authorization is enforced via `config/ai.php → role_permissions`; the
  LLM is never the source of truth for authorization.
- `AiRequestLogger` never persists API keys, tokens, or passwords; logs are
  stored in `ai_query_logs` and scoped to the school tenant.
- `ErpQueryExecutor::validateTools()` verifies every registered tool has a
  valid description, params schema, handler, and method — used by the
  registry-consistency regression suite to prevent taxonomy drift.
