# AI ROLE MATRIX

**Document:** AI_ROLE_MATRIX.md
**Date:** 2026-07-07

---

## AI Feature Definitions

| Feature | Description | Scope |
|---------|-------------|-------|
| **Ask ERP** | Natural language Q&A over school data | Role-scoped |
| **Executive Copilot** | AI-powered analytics, trends, predictions, and recommendations | School-wide data |
| **AI Agents** | Automated multi-step tasks (e.g., "Send fee reminder to all defaulters") | Configurable by admin |
| **AI Insights** | Proactive notifications about patterns, anomalies, and recommendations | Contextual |
| **Execution History** | Log and replay of all AI Agent executions | Admin only |

---

## Feature Assignment Matrix

| Role | Ask ERP | Executive Copilot | AI Agents | AI Insights | Execution History | Justification |
|------|:-------:|:-----------------:|:---------:|:-----------:|:-----------------:|---------------|
| Super Admin | ✓ | ✓ | ✓ | ✓ | ✓ | System-wide oversight requires full AI toolset for analytics, automation, and auditing |
| School Admin | ✓ | ✓ | ✓ | ✓ | ✓ | School operations command requires full AI support for decision-making and automation |
| Principal | ✓ | ✓ | — | ✓ | — | Academic leadership needs analytics but not agent automation; copilot provides strategic insights |
| Teacher | ✓ | — | — | ✓ | — | Classroom-focused; Ask ERP helps with queries, insights save time; no need for agents or cross-school analytics |
| HR | ✓ | — | — | ✓ | — | Teacher management queries via Ask ERP; insights for retention/attendance patterns |
| Accountant | ✓ | — | — | ✓ | — | Fee-related queries; insights for collection patterns and defaulter prediction |
| Payroll Manager | ✓ | — | — | — | — | Payroll-specific queries; insights less relevant as payroll is structured monthly |
| Librarian | ✓ | — | — | — | — | Book search, availability queries; insights not critical for library operations |
| Receptionist | ✓ | — | — | — | — | Quick student/parent lookups; no advanced AI needed |
| Staff | ✓ | — | — | — | — | Basic queries about own records |
| Parent | ✓ (child only) | — | — | — | — | Limited to queries about own children — "What is my child's attendance?" |
| Student | ✓ (self only) | — | — | — | — | Limited to self-queries — "What homework is due?" |

---

## Ask ERP — Query Scopes Per Role

### Super Admin
```
"Show me total users across all schools"
"Which school has the highest fee collection?"
"List schools with storage > 80%"
"Show audit log for role changes this week"
```

### School Admin
```
"What is today's attendance percentage?"
"List students with fee pending > 30 days"
"Show teacher attendance for this month"
"Who are the top 10 defaulters?"
"What is the payroll amount for this month?"
"Show exam results for Class 10"
```

### Principal
```
"Which class has the lowest attendance this month?"
"Show me pending leave approvals"
"Compare exam performance between Class 10-A and 10-B"
"List teachers with > 3 absent days this month"
"What is the homework completion rate for Class 8?"
```

### Teacher
```
"Show my timetable for today"
"Which students have attendance below 75%?"
"List pending homework submissions for Class 5-A"
"What is the average marks for my Science class?"
"How many leave days do I have remaining?"
```

### HR
```
"Which teachers have contracts expiring this month?"
"Show teacher attendance for this week"
"List teachers on leave today"
"How many teachers are in the Science department?"
```

### Accountant
```
"What is the total collection for today?"
"Show me the fee defaulter list"
"Which class has the highest pending dues?"
"Generate fee collection report for this month"
```

### Payroll Manager
```
"Show salary structure for Mr. Sharma"
"What was the total payroll for last month?"
"List employees without salary structure"
"Generate payroll summary report"
```

### Librarian
```
"Search for 'Harry Potter' books"
"Which books are currently overdue?"
"Show books by author 'Ruskin Bond'"
"Who has borrowed the most books this month?"
```

### Receptionist
```
"Show me student profile for roll number 101"
"Find parent contact for student Ravi Kumar"
"Register a new inquiry for Class 1"
```

### Staff
```
"Show my attendance for this month"
"How many leave days do I have remaining?"
```

### Parent
```
"What is my child's attendance today?"
"Show my child's exam results"
"Is there any pending fee?"
"What homework is due for my child?"
"When is the next parent-teacher meeting?"
"Show my child's timetable"
```

### Student
```
"What is my timetable for today?"
"Do I have any homework pending?"
"What are my exam dates?"
"Show my attendance for this month"
"What books do I have from the library?"
```

---

## Executive Copilot — Use Cases

Available to: **Super Admin, School Admin, Principal**

### Super Admin
```
"Show me the top 5 schools by user growth"
"Predict which schools will need more storage next quarter"
"Compare adoption rates across schools"
"Identify schools with unusual activity patterns"
```

### School Admin
```
"Predict fee collection for next month"
"Show enrollment trends for the last 3 years"
"Which departments are understaffed?"
"Compare this year's exam results with last year"
"Forecast teacher requirements for next academic year"
```

### Principal
```
"Show class-wise performance trends this term"
"Predict which students are at risk of failing"
"Compare teacher performance across subjects"
"Show attendance patterns by day of week"
"What is the optimal class size for better outcomes?"
```

---

## AI Agents — Use Cases

Available to: **Super Admin, School Admin**

### School Admin Agents
```
"Send fee reminder SMS to all parents with dues > 30 days"
"Generate and email report cards for Class 10"
"Archive attendance records for last academic year"
"Create user accounts for 50 new students from CSV"
"Backup all financial records for this month"
"Generate payroll exception report"
"Send birthday greetings to all students this week"
```

### Super Admin Agents (cross-school)
```
"Generate usage report for all schools"
"Sync global settings to all schools"
"Identify inactive schools and send alert"
"Create new academic year structure for all schools"
```

---

## AI Insights — Triggers

Available to: **Super Admin, School Admin, Principal, Teacher, HR, Accountant**

### Proactive Insight Examples

| Role | Trigger Condition | Insight Message |
|------|------------------|-----------------|
| School Admin | Attendance < 75% for 3 consecutive days | "Class 8-B attendance is below 75% for 3 days. Suggested action: Review with class teacher." |
| Principal | Exam results show subject decline | "Mathematics scores dropped 12% compared to last term. Consider curriculum review." |
| Teacher | Student absences > 5 in a month | "Ravi Kumar has missed 6 classes this month. Recommended: Check-in with student." |
| Teacher | Homework submission < 60% | "Class 5-A has only 55% homework submission rate. Send reminder to parents." |
| HR | Teacher absenteeism high | "3 teachers in Science department have > 5 absent days. Possible workload issue." |
| Accountant | Collection rate declining | "Fee collection is 15% lower than last month. 45 new defaulters this week." |
| School Admin | Payroll overdue | "Payroll for March has not been processed. Due date was 5th April." |
| Principal | Exam marking delayed | "Results for Class 10 are pending publication. 3 teachers have not submitted marks." |
| Teacher | Leave balance low | "You have only 2 casual leave days remaining for the year." |
| Accountant | Large transaction | "A fee payment of Rs. 50,000 was received from parent — verify and acknowledge." |

---

## AI Feature Architecture

```
┌─────────────────────────────────────────────────────┐
│                    AI LAYER                           │
├─────────────────────────────────────────────────────┤
│  ┌──────────┐  ┌──────────┐  ┌──────────┐          │
│  │ Ask ERP  │  │Executive │  │   AI     │          │
│  │          │  │ Copilot  │  │  Agents  │          │
│  └─────┬────┘  └────┬─────┘  └────┬─────┘          │
│        │            │             │                 │
│  ┌─────┴────────────┴─────────────┴─────┐           │
│  │         Intent Resolver               │          │
│  │  (LLM + Parameter Extraction)         │          │
│  └────────────────┬──────────────────────┘          │
│                   │                                  │
│  ┌────────────────┴──────────────────────┐           │
│  │         Context Builder                │          │
│  │  (Role, School, Academic Year)         │          │
│  └────────────────┬──────────────────────┘          │
│                   │                                  │
│  ┌────────────────┴──────────────────────┐           │
│  │         Orchestrator Service           │          │
│  │  (Routes to appropriate data source)   │          │
│  └────┬──────┬──────┬──────┬─────────────┘          │
│       │      │      │      │                        │
│  ┌────┴┐ ┌───┴──┐ ┌┴────┐ ┌┴────────┐              │
│  │DB   │ │Search│ │Files│ │External │              │
│  │Query│ │      │ │     │ │  API    │              │
│  └─────┘ └──────┘ └─────┘ └─────────┘              │
└─────────────────────────────────────────────────────┘
```

---

## AI Data Access by Role

| Data Category | SA | AD | PR | TCH | HR | ACC | PM | LIB | REC | STF | PAR | STU |
|--------------|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|:--:|
| Student Data | ✓ | ✓ | ✓ | As | — | — | — | — | ✓ | — | O | O |
| Teacher Data | ✓ | ✓ | ✓ | — | ✓ | — | — | — | — | — | — | — |
| Fee Data | ✓ | ✓ | — | — | — | ✓ | — | — | — | — | O | O |
| Payroll Data | ✓ | ✓ | — | — | — | — | ✓ | — | — | O | — | — |
| Attendance | ✓ | ✓ | ✓ | As | Sw | — | — | — | — | O | O | O |
| Exam Results | ✓ | ✓ | ✓ | As | — | — | — | — | — | — | O | O |
| Library Data | ✓ | ✓ | — | — | — | — | — | ✓ | — | — | O | O |
| Transport Data | ✓ | ✓ | — | — | — | ✓ | — | — | — | — | O | O |
| System Data | ✓ | — | — | — | — | — | — | — | — | — | — | — |

Sw = School-wide, As = Assigned only, O = Own/children only

---

## AI Implementation Principles

1. **Role-scoped data access** — LLM context builder injects role-specific data boundaries
2. **No write operations via AI** — Ask ERP and Copilot are read-only; AI Agents have explicit write capabilities
3. **All AI actions audited** — Every query and agent execution logged with user, timestamp, and parameters
4. **Rate limits per role** — Students/Parents limited to 10 queries/hour; Admin roles 50/hour
5. **Opt-in for parents/students** — AI features enabled by School Admin for parent/student roles
6. **LLM model selection per feature** — Ask ERP uses faster/cheaper model; Copilot uses more capable model
7. **Data masking** — PII masked in cross-school queries (Super Admin sees masked data by default)
