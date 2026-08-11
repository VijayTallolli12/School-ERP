# Files Modified — Phase 08: AI Role Awareness

## New Files

| # | File |
|---|------|
| 1 | `config/ai.php` |
| 2 | `app/Modules/AiAssistant/Services/RoleDataScoper.php` |
| 3 | `app/Modules/AiAssistant/Models/AiQueryLog.php` |
| 4 | `database/migrations/2026_07_07_000003_create_ai_query_logs_table.php` |
| 5 | `docs/CONSTITUTION/DATA_VISIBILITY_MATRIX.md` |
| 6 | `docs/CONSTITUTION/AI_GOVERNANCE.md` |

## Modified Files

| # | File | Changes |
|---|------|---------|
| 1 | `app/Modules/AiAssistant/Services/AIService.php` | Replaced `checkTeacherAuthorization()` with `checkRoleAuthorization()`, added `applyRoleScoping()`, added `logQuery()` |
