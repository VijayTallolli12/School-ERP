# AI Governance Policy

## Purpose

This document defines how the AI Assistant operates, what data it can access, and what safeguards are in place. It serves as the constitution for AI-driven interactions within the School ERP system.

## 1. Scope

The AI Assistant is available to all authenticated users with active school sessions. It provides natural language querying and action execution across the following modules:

- Attendance
- Students
- Fees
- Exams
- Homework
- Transport
- Library
- Payroll
- Notifications

## 2. Access Control

### 2.1 Authentication

All AI queries require an authenticated user session. The system uses the current tenant context (`SchoolContext`) to determine which school's data is visible.

### 2.2 Authorization

Every intent is checked against the user's role permissions before execution:

```
User Query
  ↓
Intent Resolution (AIIntentService)
  ↓
Role Check (RoleDataScoper::isIntentAllowed)
  ├── Allowed → Continue with scope filters
  └── Denied  → Return role-appropriate error
```

### 2.3 Data Scoping

Queries are automatically scoped to the user's authorized data boundary:

| Role | Data Boundary |
|------|---------------|
| Super Admin / School Admin / Principal | Entire school |
| Accountant | Financial + student + attendance records |
| Teacher | Own class sections only |
| Parent | Own children only |
| Student | Self only |
| Librarian | Library records only |
| Staff | Attendance records |
| Receptionist | Student records only |

## 3. Intent Resolution

### 3.1 Two-Tier Classification

Queries go through a hierarchical classification:

1. **Module Classification** — Identifies the ERP module (attendance, fee, student, etc.)
2. **Intent Classification** — Identifies the specific intent within that module (e.g., `attendance.absent_today`)

### 3.2 Fallback Mechanism

If the primary classifier (Gemini API) fails, the system falls back to a keyword-based parser that matches against known synonym maps.

### 3.3 Unknown Intents

If no intent can be resolved with sufficient confidence, the user is shown a list of supported question categories.

## 4. Action Execution

### 4.1 Query vs. Action

- **Query intents** (`action: query`) — Read-only data retrieval. Executed immediately.
- **Action intents** (`action: action`) — Mutate system state. Require explicit user confirmation before execution.

### 4.2 Destructive Actions

The following actions are flagged as destructive and require confirmation:

| Intent | Action |
|--------|--------|
| `payroll.generate` | Generate payroll for a month/year |
| `attendance.notify` | Send absence notifications to parents |
| `fee.send_reminders` | Send fee reminders to defaulters |
| `exam.publish` | Publish exam results |
| `notification.send` | Send a notification to users |

### 4.3 Confirmation Flow

```
User requests action
  ↓
Is destructive?
  ├── Yes → Show preview + confirmation prompt
  │         User confirms?
  │         ├── Yes → Execute
  │         └── No  → Abort
  └── No  → Execute immediately
```

## 5. Audit Logging

### 5.1 What Is Logged

Every AI query is logged to the `ai_query_logs` table with:

| Field | Description |
|-------|-------------|
| `school_id` | Tenant identifier |
| `user_id` | Authenticated user |
| `role` | User's role at time of query |
| `intent` | Resolved intent key |
| `question` | Original user question |
| `parameters` | Extracted parameters (JSON) |
| `response_summary` | Summary of the response |
| `status` | `success`, `error`, or `denied` |
| `ip_address` | Client IP address |
| `user_agent` | Client user agent string |
| `created_at` | Timestamp of the query |

### 5.2 Retention

Query logs are retained indefinitely for audit purposes. School administrators can access logs via the administration panel.

## 6. Data Privacy

### 6.1 Personally Identifiable Information (PII)

The AI Assistant may process PII (student names, admission numbers, parent contact details) only in the context of authorized queries. PII is never exposed to users outside their authorized scope.

### 6.2 Data Minimization

Responses include only the minimum data necessary to answer the user's question. Full database dumps or unrestricted data exports are not available through the AI interface.

### 6.3 No Permanent Storage

The AI Assistant does not store query content or responses outside the `ai_query_logs` audit table. No external AI training data is collected from user queries.

## 7. External AI Dependencies

### 7.1 Gemini API

The system uses Google's Gemini API for primary intent classification. Data sent to Gemini:
- User's natural language question only
- No PII beyond what the user includes in their question
- Current date context (for relative date resolution)

### 7.2 Data Handling

Gemini API responses are used only for intent determination. Response data is never forwarded to external services.

### 7.3 Fallback Independence

The keyword-based fallback parser operates entirely on-premise with no external dependencies. Core query functionality continues even if the external API is unavailable.

## 8. Error Handling

| Scenario | Behavior |
|----------|----------|
| Unauthenticated user | Return authentication error |
| Unknown intent | Show supported questions |
| Unauthorized intent | Return role-specific denial message |
| External API failure | Fall back to keyword parser |
| Database error | Return error message, log details |
| Timeout | Return timeout error |

## 9. Agent Operations

### 9.1 Autonomous Agents

The system supports autonomous agents for bulk operations:

| Agent | Function | Requires Confirmation |
|-------|----------|----------------------|
| Payroll Agent | Generate monthly payroll | Yes |
| Attendance Agent | Send absence notifications | Yes |
| Fee Collection Agent | Send fee reminders | Yes |

### 9.2 Agent Limitations

- Agents operate within the same RBAC boundaries as regular queries.
- Agents generate previews before execution.
- All agent actions are audited in `ai_query_logs`.

## 10. Configuration

Governance-relevant configuration is stored in:

| Path | Purpose |
|------|---------|
| `config/ai.php` | Role permissions, data scoping rules |
| `config/ai/modules.php` | Module definitions and intents |
| `config/services.php` | Gemini API configuration |

## 11. Policy Review

This governance policy should be reviewed:
- When new roles are added
- When new modules are integrated
- At least once per academic year

## 12. Enforcement

Violations of this governance policy (e.g., unauthorized data access attempts) are logged and may result in:
- Automatic query denial
- Notification to school administrators
- Temporary suspension of AI Assistant access
