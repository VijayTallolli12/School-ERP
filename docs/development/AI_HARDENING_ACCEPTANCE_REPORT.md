# School ERP AI — Production Hardening & Acceptance Test Report

Date: 2026-08-12

---

## 1. HARDENING SUMMARY

The AI architecture (AIService → QueryPlanner → ErpToolRegistry →
ErpQueryExecutor → ERP handlers → MySQL → validated result →
AiResponseGenerator) was preserved and hardened. No rewrite, no new
infrastructure, no RAG, no vector DB, no provider change.

**Changes made during this phase:**

| Area | What was done |
|------|---------------|
| Role scoping gap (real bug) | `RoleDataScoper` produced `class_section_ids` / `student_ids` / `student_id` filters, but handlers only read the singular `class_section_id`. Teacher queries were **not** actually restricted to their classes. Added scope-filter support to `ExamQueryHandler`, `AttendanceQueryHandler`, `HomeworkQueryHandler`, `StudentQueryHandler`, `FeeQueryHandler`. |
| Action confirmation | `homework.create` and `transport.assign` executed without confirmation. Added both to the destructive/confirmation intents. |
| Action crash (real bug) | `exam.publish` passed the params array to `ExamService::publish(Exam $exam)` → would TypeError. Now resolves the Exam (school-scoped) before publishing; action handling wrapped in try/catch so no raw exceptions leak. |
| Prompt injection | `AiResponseGenerator` system prompt now explicitly declares ERP record content as **untrusted data** and forbids following embedded instructions. |
| Rate limiting | Added a per-user `RateLimiter::for('ai')` (default 20/min, config `AI_RATE_LIMIT_PER_MINUTE`) applied to `/admin/ai/ask` with a friendly 429 JSON response. |
| Registry validation | Added `ErpQueryExecutor::validateTools()` which verifies every tool's description/params/result_type/handler/method. |
| Registry fixes | `teacher.total` pointed at a non-existent handler method → added `TeacherQueryHandler::count()`; `exam.completed`/`exam.upcoming` now accept `exam_type`; added `HR` role to `config/ai.php` (was missing, route allowed HR); added `leave.*` permission for HR. |
| Planner quality (real bugs) | Count-signal boost could hijack unrelated modules (`library.*`/`payroll.*` → `exam.count`); "Which students have outstanding fees?" mapped to the aggregate instead of the list; "How much fees are outstanding?" incorrectly mapped to the threshold tool; "scheduled in January" forced status=scheduled; bare-year ("1999") and "last week of January" date parsing broken. All fixed and pinned by tests. |
| Response quality | Attendance summary now renders a readable "Attendance Summary" block; monetary summaries render with ₹; homework records show subject/class/due date. |
| Performance | `attendance.below_75` converted from N+1 (2 queries per student) to a single grouped aggregate query. |

---

## 2. SECURITY SUMMARY

| Control | Status | Evidence |
|---------|--------|----------|
| Tenant isolation | ✅ | School A only sees School A exams/students; `school_id` stripped from params; "give me all students from every school" stays in the authenticated school. Tests: `test_school_context_cannot_be_overridden_by_prompt`, `test_school_a_only_sees_school_a_exams`, `test_school_id_parameter_is_stripped`, etc. |
| Role authorization | ✅ | Accountant blocked from payroll, Receptionist blocked from fees, Librarian blocked from students; restricted roles get a safe message (not an exception). |
| Teacher data scoping | ✅ | Teacher queries are restricted to their assigned class sections (was previously not enforced). |
| Action confirmation | ✅ | All 7 action tools require confirmation; malicious prompts cannot force an action; confirmation is required before execution. |
| Prompt injection | ✅ | ERP record text is surfaced as data, never followed as instructions; system prompt hardened; malicious user prompts cannot override tenant/tool/permission. |
| Rate limiting | ✅ | Per-user throttle on `/admin/ai/ask`; returns 429 with user-friendly message; separate users are independent. |
| Log security | ✅ | `ai_query_logs` never stores API keys/tokens/passwords; logs store the authenticated school_id; no public/admin route exposes the logs; no secrets in the daily log channel. |
| No raw SQL from prompts | ✅ | Tools are validated and whitelisted; the LLM only selects from the registry; the executor strips `school_id` and non-schema params. |

---

## 3. TOOL REGISTRY CONSISTENCY

All **37 query tools + 7 action tools** were audited against `config/ai.php`
role permissions.

- ✅ Every registered tool has at least one matching role permission (direct
  pattern or admin `*`).
- ✅ Every configured non-`*` permission pattern references at least one
  registered tool (no dangling permissions).
- ✅ Every tool has name, description, params schema, handler, method, and a
  valid result_type; every handler class and method exists.
- ✅ No tool is both a query tool and an action tool.
- ✅ All roles named in `config/ai.php` are valid roles.

`tests/Feature/AiRegistryConsistencyTest.php` (7 tests / 483 assertions)
automatically fails if this ever drifts — a permanent guard against the
original taxonomy-drift bug.

---

## 4. CONFIDENCE AUDIT

The internal `confidence` value is **retained and used**.

- `QueryPlanner` produces it and `isUsable()` requires its presence to accept
  provider output; it is clamped in `normalize()`.
- `AIService` carries it through planning/action responses.
- `ErpQueryExecutor` and `AiResponseGenerator` do not consume it; it is not
  used by `AiRequestLogger`.

**User visibility:** confirmed NOT user-visible. The Confidence card/percentage
was already removed from both Ask ERP and Executive Gemini in the prior phase;
no Confidence UI exists in any view, CSS, or mobile source. The value remains
only in the JSON payload and internal logs for planning/fallback/monitoring.

---

## 5. TOOL TEST COVERAGE

All registered query tools are covered by `tests/Feature/AiToolCoverageTest.php`
(7 tests / 369 assertions) — each is executed against real seeded data and
asserted for success, envelope shape, result_type, count, records, summary,
and filters. Representative planner-selection questions are pinned for every
tool.

| Tool | Executed | Planner-selected | Result verified | Tenant-scoped |
|------|:---:|:---:|:---:|:---:|
| exam.search | ✅ | ✅ | ✅ | ✅ |
| exam.count | ✅ | ✅ | ✅ | ✅ |
| exam.get | ✅ | ✅ | ✅ | ✅ |
| exam.upcoming | ✅ | ✅ | ✅ | ✅ |
| exam.completed | ✅ | ✅ | ✅ | ✅ |
| student.total | ✅ | ✅ | ✅ | ✅ |
| student.search | ✅ | ✅ | ✅ | ✅ |
| student.by_class | ✅ | ✅ | ✅ | ✅ |
| student.admitted_this_month | ✅ | ✅ | ✅ | ✅ |
| attendance.search | ✅ | ✅ | ✅ | ✅ |
| attendance.absent | ✅ | ✅ | ✅ | ✅ |
| attendance.summary | ✅ | ✅ | ✅ | ✅ |
| attendance.below_75 | ✅ | ✅ | ✅ | ✅ |
| fee.outstanding | ✅ | ✅ | ✅ | ✅ |
| fee.pending | ✅ | ✅ | ✅ | ✅ |
| fee.pending_above | ✅ | ✅ | ✅ | ✅ |
| fee.today_collection | ✅ | ✅ | ✅ | ✅ |
| fee.top_defaulters | ✅ | ✅ | ✅ | ✅ |
| homework.pending | ✅ | ✅ | ✅ | ✅ |
| homework.due | ✅ | ✅ | ✅ | ✅ |
| homework.list | ✅ | ✅ | ✅ | ✅ |
| teacher.total | ✅ | ✅ | ✅ | ✅ |
| teacher.search | ✅ | ✅ | ✅ | ✅ |
| teacher.on_leave | ✅ | ✅ | ✅ | ✅ |
| leave.pending | ✅ | ✅ | ✅ | ✅ |
| transport.status | ✅ | ✅ | ✅ | ✅ |
| transport.routes | ✅ | ✅ | ✅ | ✅ |
| transport.route_occupancy | ✅ | ✅ | ✅ | ✅ |
| transport.students_on_route | ✅ | ✅ | ✅ | ✅ |
| library.books_issued | ✅ | ✅ | ✅ | ✅ |
| library.overdue_books | ✅ | ✅ | ✅ | ✅ |
| library.fine_collection | ✅ | ✅ | ✅ | ✅ |
| payroll.latest_run | ✅ | ✅ | ✅ | ✅ |
| payroll.locked_runs | ✅ | ✅ | ✅ | ✅ |
| payroll.highest_salary | ✅ | ✅ | ✅ | ✅ |
| payroll.generated_this_month | ✅ | ✅ | ✅ | ✅ |
| school.summary | ✅ | ✅ | ✅ | ✅ |

Action tools are not run as read operations; they are verified to require
confirmation (`AiSecurityTest`).

---

## 6. AI ACCEPTANCE TEST RESULTS

`tests/Feature/AiAcceptanceTest.php` (29 tests / 107 assertions) and
`tests/Feature/AiDomainAcceptanceTest.php` (20 tests / 53 assertions).

| Real question | Result |
|---------------|--------|
| Any Mid Term or Half Yearly exam was scheduled on Jan 2026 | ✅ → exam.search, Jan 2026 range, mid_term+half_yearly, finds the records |
| Show all exams scheduled in January 2026 | ✅ |
| Were there any Half Yearly exams in January? | ✅ |
| Did we have a Half Yearly examination in Jan 2026? | ✅ |
| Show me exams for Class 1 | ✅ |
| Which subject had the Half Yearly exam? | ✅ |
| How many exams were scheduled in January 2026? | ✅ |
| Which exams were completed? | ✅ |
| How many students are there? / in Class 1 / Show students in Class 1 | ✅ |
| Who is absent today? / today's attendance summary | ✅ |
| How much fees are pending? / outstanding fees / today's fee collection | ✅ |
| How many teachers? / teachers on leave today | ✅ |
| What homework is pending? / pending for Class 5 | ✅ |
| Today's transport status / which routes are active | ✅ |
| Library issued / overdue | ✅ |
| Payroll latest run | ✅ |
| Give me today's school summary | ✅ (real metrics, no confidence, no internals) |
| What needs my attention today? / school performance summary | ✅ |

Date understanding (today, yesterday, this week, last week, this month, last
month, January 2026, Jan 2026, 15 January 2026, between Jan–Mar 2026, first
week of January, last week of January, bare year) is verified in
`AiAcceptanceTest`.

AND/OR and compound filters (Mid Term or Half Yearly in Jan; Half Yearly for
Class 1 in Jan; completed Half Yearly in Jan) verified against the generated
structured query, not just the final text.

Result types (list / count / single / summary / detail) verified.

---

## 7. EXECUTIVE GEMINI RESULTS

The dashboard and school.summary tool were verified against real ERP data:

- Attendance (present/absent/percentage) — real
- Fees (outstanding, collection rate) — real
- Transport (routes, students) — real
- Homework (assigned/due/overdue) — real
- Exams (published/unpublished) — real
- Leave (pending) — real
- Library (issued/overdue) — real

No hardcoded KPI values remain in the dashboard — the KPI grid and health
score are computed from the real `school.summary` result. No confidence, no
internal metadata. (Phase 20 verified.)

---

## 8. SECURITY TEST RESULTS

`tests/Feature/AiSecurityTest.php` (23 tests / 103 assertions):

- Prompt injection (tenant/tool/permission): ✅
- Malicious prompt cannot force an action without confirmation: ✅
- ERP record text treated as data, not instructions: ✅
- `school_id` cannot be overridden by prompt/params/frontend: ✅
- School A ↔ School B isolation (exams, students): ✅
- Accountant/Receptionist/Librarian authorization, safe error responses: ✅
- Teacher scope enforced on exam queries: ✅
- Action confirmation required / executes only after confirmation: ✅
- homework.create requires confirmation: ✅
- Rate limiting per user + 429 friendly response: ✅
- Logs never store API keys/tokens/passwords: ✅
- Logs store the authenticated school_id: ✅
- Provider throw → rule fallback: ✅
- Provider returns unsupported tool → rule fallback: ✅
- Provider returns malformed params → sanitized: ✅

---

## 9. TEST TOTALS

| Suite | Tests | Assertions |
|-------|------:|-----------:|
| AiAssistantTest | 34 | 130 |
| AiRegistryConsistencyTest | 7 | 483 |
| AiSecurityTest | 23 | 103 |
| AiToolCoverageTest | 7 | 369 |
| AiAcceptanceTest | 29 | 107 |
| AiConversationFollowUpTest | 3 | 8 |
| AiDomainAcceptanceTest | 20 | 53 |
| **AI subtotal** | **123** | **1253** |
| Full application suite | **380 passed** | **2286** |

**Full `php artisan test`: 380 passed / 2286 assertions / 0 failed.**
No existing ERP regression. No test was modified merely to make the suite
green; the only test adjustments were for genuinely incorrect expectations
(which exposed real planner/formatter bugs that were then fixed).

---

## 10. FILES CHANGED (this hardening phase)

### Modified
| File | Reason |
|------|--------|
| `app/Modules/AiAssistant/Handlers/ExamQueryHandler.php` | Honor `class_section_ids` scope filter |
| `app/Modules/AiAssistant/Handlers/AttendanceQueryHandler.php` | Honor `class_section_ids`/`student_ids`/`student_id` scope; `below_75` converted to single aggregate query |
| `app/Modules/AiAssistant/Handlers/StudentQueryHandler.php` | Honor scope filters in count/search/by_class/admitted |
| `app/Modules/AiAssistant/Handlers/HomeworkQueryHandler.php` | Honor `class_section_ids` scope |
| `app/Modules/AiAssistant/Handlers/FeeQueryHandler.php` | Honor scope filters on pending lists/defaulters |
| `app/Modules/AiAssistant/Handlers/TeacherQueryHandler.php` | Added `count()` (registry pointed at missing method) |
| `app/Modules/AiAssistant/Services/AIService.php` | exam.publish resolves the Exam model safely; action handling wrapped in try/catch |
| `app/Modules/AiAssistant/Services/AgentRouter.php` | homework.create + transport.assign now require confirmation |
| `app/Modules/AiAssistant/Services/RoleDataScoper.php` | HR error message added |
| `app/Modules/AiAssistant/Erp/ErpQueryExecutor.php` | Added `validateTools()` + `getHandlerMap()` |
| `app/Modules/AiAssistant/Erp/ErpToolRegistry.php` | Signal-boost rework (accumulating, count-only-when-matched); exam.upcoming/completed accept exam_type; date/status fixes |
| `app/Modules/AiAssistant/Erp/NaturalLanguageDateParser.php` | Bare-year parsing; fixed first/last week-of-month; fixed between-month range; "last week of X" no longer shadowed |
| `app/Modules/AiAssistant/Erp/QueryPlanner.php` | "scheduled in <month>" no longer forces status=scheduled |
| `app/Modules/AiAssistant/Erp/AiResponseGenerator.php` | Prompt-injection hardening; attendance/money/homework response formatting |
| `app/Providers/AppServiceProvider.php` | Registered the `ai` rate limiter |
| `config/ai.php` | Added HR role + leave permission; rate-limit config |
| `routes/modules/ai_assistant.php` | Applied `throttle:ai` to `/admin/ai/ask` |
| `.env.example` | Documented `AI_RATE_LIMIT_PER_MINUTE` |
| `docs/development/AI.md` | Documented hardening/security |

### Added
| File | Reason |
|------|--------|
| `tests/Feature/AiRegistryConsistencyTest.php` | Registry ↔ permission drift guard (Phase 3) |
| `tests/Feature/AiSecurityTest.php` | Tenant/role/injection/action/rate-limit/log tests (Phases 6,8,9,10,11,12,24) |
| `tests/Feature/AiToolCoverageTest.php` | Every-tool execution + planner-selection coverage (Phase 5) |
| `tests/Feature/AiAcceptanceTest.php` | NL/date/AND-OR/result-type acceptance (Phases 13–16) |
| `tests/Feature/AiConversationFollowUpTest.php` | Documents stateless follow-up limitation (Phase 17) |
| `tests/Feature/AiDomainAcceptanceTest.php` | Major-domain acceptance + Gemini (Phases 18–19) |

No files were removed in this phase.

---

## 11. REMAINING LIMITATIONS (honest)

1. **Conversational memory is not supported.** Ask ERP / Executive Gemini are
   stateless: each `/admin/ai/ask` request carries only the question. The
   frontend keeps a display-only history. A bare follow-up like "What about
   February?" cannot inherit context. This is documented and pinned by
   `AiConversationFollowUpTest`. Adding server-side conversation memory would
   require a new persistence layer (session/db context) — intentionally out of
   scope for this hardening phase.
2. **Provider fallback is deterministic, not "smart".** When the LLM provider
   is unavailable or returns unusable output, `QueryPlanner` falls back to the
   keyword/date/synonym planner. This is reliable for the covered tools but
   does not match the LLM's flexibility for unusual phrasing.
3. **`attendance.below_75`** and **fee pending lists** still process in-memory
   PHP aggregation over a bounded result set (students capped at 500, fee items
   are school-scoped) — acceptable for a single school but worth revisiting if
   a school exceeds a few thousand students.
4. **Action tools are confirmed but not end-to-end integration-tested** for
   every side effect (e.g. a real payroll run, a real notification send). The
   tests verify the confirmation gate and safe failure, not the downstream
   business side effects of every agent/service.
5. **`parent` and `student` roles are configured but the web AI route excludes
   them** (`role: Super Admin|School Admin|Principal|HR|Teacher|Accountant|
   Librarian|Receptionist|Staff`). Their permission entries exist for
   completeness/other entry points but cannot reach the web endpoint.

---

## VERDICT

The mandatory acceptance criteria are met:

- "Any Mid Term or Half Yearly exam was scheduled on Jan 2026" returns the real
  2026-01-31 Half Yearly / Computer Science / 100-mark / Completed exam via the
  shared intelligence layer — not a hardcoded answer.
- All 37 query tools execute against real ERP data with tenant + role scoping.
- All 7 action tools require confirmation.
- Prompt injection, multi-tenant, role, rate-limit, and log-security controls
  are implemented and tested.
- Internal confidence is retained (used) and not user-visible.
- Full regression: **380 tests / 2286 assertions / 0 failures**.
