# FINAL RISK REGISTER

**Date:** 2026-07-08
**Total Risks:** 25 (2 Critical, 8 High, 9 Medium, 6 Low)

---

## 🔴 CRITICAL RISKS

| ID | Risk | Impact | Likelihood | Severity | Fix | Effort |
|----|------|--------|------------|----------|-----|--------|
| R01 | AI Agent routes completely unguarded — any user can execute payroll/attendance agents | Security breach, unauthorized payroll processing | Very High | Critical | Add permission middleware to route group | 1 hour |
| R02 | AI sidebar items visible to all users — exposes unauthorized features | Users access features they shouldn't, confusion | Very High | Critical | Add @can gates on all AI sidebar items | 30 min |

## 🟠 HIGH RISKS

| ID | Risk | Impact | Likelihood | Severity | Fix | Effort |
|----|------|--------|------------|----------|-----|--------|
| R03 | ParentNotification::parents() crashes on access | Fatal error, page crash | Medium | High | Fix to use Guardian::class | 30 min |
| R04 | User::parent() return type mismatch | TypeError in strict mode | Medium | High | Change BelongsTo→HasOne | 5 min |
| R05 | Teachers cannot apply for leave (missing permission) | Workflow blocked | High | High | Add leave_management.create to Teacher | 15 min |
| R06 | AI role detection broken (lowercase role names) | AI returns incorrect data for all roles | High | High | Fix ContextBuilder to use Title Case | 15 min |
| R07 | 11+ hardcoded hasRole() checks in controllers | Authorization breaks on role rename | Medium | High | Replace with can()/hasPermission() | 4 hours |
| R08 | Zero business logic tests — all changes risk regression | Undetected bugs in production | Very High | High | Write feature tests for 5 core workflows | 40 hours |
| R09 | No backup, monitoring, or error tracking | Data loss, undetected failures in production | Very High | High | Implement backup schedule, install Telescope/Sentry | 16 hours |
| R10 | No deployment/rollback scripts | Manual deployment errors, no rollback capability | High | High | Create deployment and rollback scripts | 8 hours |

## 🟡 MEDIUM RISKS

| ID | Risk | Impact | Likelihood | Severity | Fix | Effort |
|----|------|--------|------------|----------|-----|--------|
| R11 | HR documents on public disk — unauthorized access risk | Data leak | Medium | Medium | Restrict file access via signed URLs | 4 hours |
| R12 | Heavy raw SQL (45+ queries) — fragile to schema changes | Silent query failures on schema change | Medium | Medium | Refactor to Eloquent (partial) | 16 hours |
| R13 | jQuery CDN dependency — UI breaks if CDN down | Complete UI failure | Low | Medium | Bundle jQuery locally | 2 hours |
| R14 | Large bundle sizes (1.3MB total, 668KB CSS) | Slow page loads | Medium | Medium | Purge unused CSS, optimize bundles | 4 hours |
| R15 | 24 href="#" export buttons — dead links if JS fails | Non-functional exports | Low | Medium | Convert to <button> | 2 hours |
| R16 | No rate limiting on AI endpoint | API abuse, cost overrun | Medium | Medium | Add rate limiter to AI routes | 1 hour |
| R17 | Parent portal dual access paths | UX confusion, accidental admin access | Medium | Medium | Add redirect middleware for parents on admin routes | 2 hours |
| R18 | Promotion/admission/year-end workflows not implemented | Manual workarounds required at year-end | Very High | Medium | Implement batch promotion workflow | 40 hours |
| R19 | Missing composite indexes (7 tables) | Slow queries on large datasets | Medium | Medium | Add migration for recommended indexes | 2 hours |

## 🟢 LOW RISKS

| ID | Risk | Impact | Likelihood | Severity | Fix | Effort |
|----|------|--------|------------|----------|-----|--------|
| R20 | Empty DataTables on 8 report pages (no seed data) | Empty reports in demo/dev | Low | Low | Add seed data for report tables | 4 hours |
| R21 | `whereRaw('1 = 0')` brittle guard clauses | Silent filter bypass | Low | Low | Replace with proper query scopes | 1 hour |
| R22 | Dead permissions in seeder (students.export, etc.) | Confusing role configuration | Low | Low | Audit and remove unused permissions | 1 hour |
| R23 | Employee code globally unique (not composite) | Cross-school collision | Low | Low | Add composite unique migration | 1 hour |
| R24 | Notification FormRequests missing authorize() | Thin request validation | Low | Low | Add authorize() methods | 1 hour |
| R25 | Executive dashboard shows simulated KPI data | Misleading leadership reporting | Medium | Low | Replace with real API calls | 8 hours |

---

## RISK PROFILE

| Severity | Count | Action Required |
|----------|-------|-----------------|
| 🔴 Critical | 2 | Fix immediately — blocks any deployment |
| 🟠 High | 8 | Fix before pilot deployment |
| 🟡 Medium | 9 | Fix before production deployment |
| 🟢 Low | 6 | Fix as time permits |

**Total:** 25 risks identified
**Production blockers:** 10 (Critical + High)
