# Security Report — Phase 08: AI Role Awareness

## Security Controls

| Control | Implementation | Location |
|---------|---------------|----------|
| **Role-based intent authorization** | `RoleDataScoper::isIntentAllowed()` checks user's role against `config('ai.role_permissions')` patterns using `Str::is()` | `RoleDataScoper.php:30-45` |
| **Data scoping** | Scope filters (`class_section_ids`, `teacher_id`, `student_ids`, `student_id`) are resolved from the authenticated user's relationships and injected into query parameters | `RoleDataScoper.php:47-61` |
| **Audit trail** | Every AI query is logged to `ai_query_logs` with user, role, intent, question, parameters, response summary, status, IP address, and user agent | `AIService.php:608-625` |
| **Super Admin bypass** | Roles with `*` wildcard bypass all intent restrictions | `config/ai.php:8-10` |
| **Tenant isolation** | `BelongsToSchool` trait on `AiQueryLog` model ensures audit data is scoped to the correct school | `AiQueryLog.php:13` |

## Threat Mitigation

| Threat | Mitigation |
|--------|------------|
| Unauthorized data access by low-privilege roles | `isIntentAllowed()` gates every intent; `getScopeFilters()` narrows data to what the role is allowed to see |
| Data leakage between schools | `SchoolContext` + `BelongsToSchool` scoping ensures multi-tenant isolation |
| Insider threat / rogue queries | Full audit log with user identity, IP, user agent, and intent |
| Privilege escalation | Role is read from the authenticated user's `roles` relationship — not from request input |
| Logging failure masking attacks | `logQuery()` catches all exceptions; an attacker cannot disable audit by crashing the logs |

## Governance Documentation

| Document | Purpose |
|----------|---------|
| `docs/CONSTITUTION/AI_GOVERNANCE.md` | Defines AI governance: access control, accountability, data privacy, transparency, human oversight, and compliance framework |
| `docs/CONSTITUTION/DATA_VISIBILITY_MATRIX.md` | Role hierarchy and module-level permission matrix; serves as an audit-ready reference for which roles can access what data |
