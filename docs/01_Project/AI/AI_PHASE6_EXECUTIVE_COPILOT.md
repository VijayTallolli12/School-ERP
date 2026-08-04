# AI Phase 6: Multi-Agent Executive Copilot

## Overview

Phase AI-6 adds orchestration capabilities to answer executive questions by coordinating multiple ERP modules. The AI can now analyze school health, generate KPIs, and provide actionable recommendations.

## Architecture

```
User: "How is my school today?"
    ↓
┌─────────────────────────────────────┐
│ PlannerService                      │
│ - Analyzes intent                   │
│ - Determines required modules       │
│ - Returns execution plan            │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ OrchestratorService                 │
│ - Executes handlers in parallel     │
│ - Collects outputs                  │
│ - Handles partial failures          │
│ - Aggregates results                │
└─────────────────────────────────────┘
    ↓
┌─────────────────────────────────────┐
│ InsightGenerator                    │
│ - Extracts KPIs                     │
│ - Detects anomalies                 │
│ - Generates alerts                  │
│ - Creates recommendations           │
└─────────────────────────────────────┘
    ↓
AIResponseFormatter
    ↓
Executive Report
```

## Services

### PlannerService

Analyzes intent and determines which modules to execute.

```php
$planner = app(PlannerService::class);

$plan = $planner->plan('school.summary');
// Returns:
// [
//     'type' => 'executive',
//     'intent' => 'school.summary',
//     'tasks' => [...],
//     'parallel' => true,
//     'aggregate' => true,
// ]
```

### OrchestratorService

Executes handlers and aggregates results.

```php
$orchestrator = app(OrchestratorService::class);

$result = $orchestrator->execute($plan);
// Returns:
// [
//     'success' => true,
//     'sections' => [...],
//     'stats' => ['total' => 8, 'successful' => 7, 'failed' => 1],
//     'insights' => [...],
// ]
```

### InsightGenerator

Generates KPIs, anomalies, and recommendations.

```php
$generator = app(InsightGenerator::class);

$insights = $generator->generate($result);
// Returns:
// [
//     'health_score' => ['overall' => 82, 'rating' => 'good'],
//     'kpis' => [...],
//     'anomalies' => [...],
//     'alerts' => [...],
//     'recommendations' => [...],
// ]
```

## Execution Flow

### Executive Intent

```
User: "How is my school today?"
↓
PlannerService: plan('school.summary')
├─ Task: Attendance → absentToday()
├─ Task: Fees → totalOutstanding()
├─ Task: Transport → routeOccupancy()
├─ Task: Homework → getExecutiveSummary()
├─ Task: Exams → getExecutiveSummary()
├─ Task: Leave → getExecutiveSummary()
├─ Task: Notifications → getExecutiveSummary()
└─ Task: Library → booksIssued()
↓
OrchestratorService: execute(plan)
├─ Execute all tasks (parallel where possible)
├─ Collect results
├─ Handle failures gracefully
└─ Aggregate output
↓
InsightGenerator: generate(output)
├─ Extract KPIs from each module
├─ Calculate health score
├─ Detect anomalies
├─ Generate alerts
└─ Create recommendations
↓
AIResponseFormatter: formatExecutiveReport()
↓
Executive Report
```

### Single Module Intent

```
User: "Show fee outstanding"
↓
PlannerService: plan('fee.outstanding')
├─ Task: Fees → totalOutstanding()
↓
OrchestratorService: execute(plan)
├─ Execute single task
└─ Return result
↓
AIResponseFormatter: format()
↓
Fee Report
```

## Output Structure

### Executive Report

```json
{
    "success": true,
    "type": "executive",
    "intent": "school.summary",
    "description": "Full school executive summary",
    "sections": {
        "attendance": {
            "label": "Attendance",
            "status": "ok",
            "data": "Students present today: 420 out of 450 (93.3%)"
        },
        "fees": {
            "label": "Fees",
            "status": "ok",
            "data": "Total outstanding: ₹2,45,000"
        },
        "transport": {
            "label": "Transport",
            "status": "unavailable",
            "error": "Database connection timeout"
        }
    },
    "stats": {
        "total": 8,
        "successful": 7,
        "failed": 1
    },
    "insights": {
        "health_score": {
            "overall": 82,
            "rating": "good",
            "breakdown": {
                "attendance": 95,
                "fees": 80,
                "transport": 60
            }
        },
        "kpis": {
            "attendance": {"percentage": 93.3},
            "fees": {"rate": 65.2},
            "library": {"overdue": 12}
        },
        "anomalies": [
            {
                "type": "warning",
                "module": "library",
                "message": "High number of overdue books",
                "value": 12
            }
        ],
        "alerts": [
            {
                "severity": "medium",
                "message": "Attendance below 75% threshold",
                "module": "attendance"
            }
        ],
        "recommendations": [
            {
                "priority": "medium",
                "module": "library",
                "action": "Send overdue book reminders to students and parents"
            }
        ],
        "operational_status": {
            "attendance": {"label": "Attendance", "status": "ok"},
            "fees": {"label": "Fees", "status": "ok"},
            "transport": {"label": "Transport", "status": "unavailable"}
        }
    }
}
```

## Health Score Calculation

| Metric | Threshold | Score |
|--------|-----------|-------|
| Attendance >= 90% | Excellent | 95 |
| Attendance >= 75% | Good | 80 |
| Attendance >= 60% | Warning | 60 |
| Attendance < 60% | Critical | 40 |
| Fee Collection >= 80% | Excellent | 95 |
| Fee Collection >= 60% | Good | 80 |
| Fee Collection >= 40% | Warning | 60 |
| Fee Collection < 40% | Critical | 40 |

## Failure Handling

| Scenario | Behavior |
|----------|----------|
| Handler throws exception | Mark module as unavailable, continue |
| Handler returns null | Mark module as unavailable, continue |
| Database timeout | Mark module as unavailable, continue |
| All modules fail | Return empty result with stats |
| Partial success | Return available modules, mark others unavailable |

## File Changes

### New Files

| File | Purpose |
|------|---------|
| `app/Modules/AiAssistant/Services/PlannerService.php` | Analyzes intent, creates execution plan |
| `app/Modules/AiAssistant/Services/OrchestratorService.php` | Executes handlers, aggregates results |
| `app/Modules/AiAssistant/Services/InsightGenerator.php` | Generates KPIs, anomalies, recommendations |
| `docs/AI_PHASE6_EXECUTIVE_COPILOT.md` | This documentation |

### Unchanged Files

| File | Reason |
|------|--------|
| `AIIntentService.php` | No changes needed |
| `PromptBuilder.php` | No changes needed |
| `ContextBuilder.php` | No changes needed |
| `ParameterResolver.php` | No changes needed |
| `ClarificationService.php` | No changes needed |
| `AgentRouter.php` | Route mapping unchanged |
| `AIResponseFormatter.php` | Can optionally use executive format |
| All handlers | No changes needed |
| All agents | No changes needed |
| All tests | Output format unchanged |

## Performance Considerations

### Parallel Execution

The OrchestratorService executes tasks in parallel where possible:

```php
$results = $parallel
    ? $this->executeParallel($tasks)
    : $this->executeSequential($tasks);
```

### Caching Reuse

Executive copilot reuses cached data from ContextBuilder:

```php
// ContextBuilder caches data for 5 minutes
Cache::remember("ai_context_{$schoolId}", 300, ...);

// OrchestratorService can reuse this cached data
```

### No Duplicate Queries

Each module handler runs exactly once per request. The SchoolSummaryHandler is called once and sub-sections are extracted.

## Debug Logging

In development/local environments, the system logs:

```
[AI Orchestrator] Orchestration complete {
    "intent": "school.summary",
    "tasks_count": 8,
    "successful": 7,
    "failed": 1,
    "duration_ms": 245.3
}

[AI Orchestrator] Task failed {
    "module": "transport",
    "handler": "TransportQueryHandler",
    "method": "routeOccupancy",
    "error": "Database connection timeout"
}
```

## Future Enhancements

### Short-term
- Add caching for executive reports
- Add parallel execution using Laravel Queue
- Add more granular health metrics

### Medium-term
- Add historical trend analysis
- Add predictive analytics
- Add custom KPI definitions

### Long-term
- Add machine learning for anomaly detection
- Add natural language executive reports
- Add voice-based executive briefings
