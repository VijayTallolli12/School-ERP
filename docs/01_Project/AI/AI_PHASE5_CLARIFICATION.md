# AI Phase 5: Intelligent Clarification Engine

## Overview

Phase AI-5 adds an intelligent clarification layer that asks follow-up questions when confidence is low or required parameters are missing, instead of guessing.

## Architecture

```
User Question
    ↓
Gemini (returns intent with confidence)
    ↓
ClarificationService
    ├─ Confidence >= 0.85 → Proceed
    ├─ Confidence < 0.85 → Ask clarification
    └─ Missing params → Ask for param
    ↓
User Response
    ↓
Continue with resolved intent/param
```

## Clarification Rules

### Confidence-Based

| Confidence | Action |
|------------|--------|
| >= 0.85 | Proceed normally |
| < 0.85 | Ask clarification |

### Parameter-Based

| Intent | Required Params | Missing Action |
|--------|-----------------|----------------|
| `fee.pending_above` | `amount` | Ask "What amount?" |
| `payroll.generate` | `month`, `year` | Ask "Which month/year?" |
| `attendance.notify` | `date` | Ask "For which date?" |
| `fee.send_reminders` | `days` | Ask "How many days?" |
| `exam.publish` | `exam_id` | Ask "Which exam?" |
| `notification.send` | `title`, `message`, `target_type` | Ask in order |
| `homework.create` | `class_section_id`, `subject_id`, `title`, `due_date` | Ask in order |
| `transport.assign` | `route_id`, `student_ids` | Ask in order |

## Output Format

### Module Clarification

```json
{
    "type": "clarification",
    "question": "Which attendance report would you like?",
    "options": [
        "Today's Absences",
        "Monthly Percentage",
        "Below 75%",
        "Send Notifications"
    ],
    "intent_map": {
        "Today's Absences": "attendance.absent_today",
        "Monthly Percentage": "attendance.monthly_percentage",
        "Below 75%": "attendance.below_75",
        "Send Notifications": "attendance.notify"
    }
}
```

### Parameter Clarification

```json
{
    "type": "clarification",
    "question": "What is the minimum fee amount?",
    "options": ["₹1,000", "₹5,000", "₹10,000", "₹25,000"],
    "param_name": "amount"
}
```

## Configuration

### Module Clarification Config

Each module in `config/ai/modules.php` has a `clarification` section:

```php
'attendance' => [
    'description' => '...',
    'intents' => [...],
    'clarification' => [
        'prompt' => 'Which attendance report would you like?',
        'options' => [
            "Today's Absences" => 'attendance.absent_today',
            'Monthly Percentage' => 'attendance.monthly_percentage',
            'Below 75%' => 'attendance.below_75',
            'Send Notifications' => 'attendance.notify',
        ],
    ],
],
```

### Adding New Clarification Options

1. Open `config/ai/modules.php`
2. Add new option to the module's `clarification.options`:
```php
'clarification' => [
    'prompt' => 'Which report would you like?',
    'options' => [
        'Existing Option' => 'existing.intent',
        'New Option' => 'new.intent',
    ],
],
```

## Usage

### Check if Clarification Needed

```php
$clarificationService = app(ClarificationService::class);

$clarification = $clarificationService->needsClarification([
    'intent' => 'unknown',
    'confidence' => 0.5,
    'module' => 'attendance',
]);

if ($clarification) {
    // Return clarification to user
    return $clarification;
}
```

### Resolve User Response

```php
$response = $clarificationService->resolveClarification(
    'Monthly Percentage',
    [
        'intent_map' => [
            'Monthly Percentage' => 'attendance.monthly_percentage',
        ],
    ]
);

// Result: ['type' => 'intent', 'intent' => 'attendance.monthly_percentage']
```

### Resolve Parameter Response

```php
$response = $clarificationService->resolveClarification(
    'Today',
    ['param_name' => 'date']
);

// Result: ['type' => 'param', 'param_name' => 'date', 'value' => '2026-07-04']
```

## File Changes

### New Files

| File | Purpose |
|------|---------|
| `app/Modules/AiAssistant/Services/ClarificationService.php` | Handles clarification logic |
| `docs/AI_PHASE5_CLARIFICATION.md` | This documentation |

### Modified Files

| File | Changes |
|------|---------|
| `config/ai/modules.php` | Added `clarification` section to each module |

### Unchanged Files

| File | Reason |
|------|--------|
| `AIIntentService.php` | No changes needed |
| `PromptBuilder.php` | No changes needed |
| `ContextBuilder.php` | No changes needed |
| `ParameterResolver.php` | No changes needed |
| `AgentRouter.php` | Route mapping unchanged |
| `AIService.php` | Can optionally use ClarificationService |
| `AIResponseFormatter.php` | Response formatting unchanged |
| All handlers | No changes needed |
| All agents | No changes needed |
| All tests | Output format unchanged |

## Parameter Normalization

The `resolveClarification` method normalizes parameter values:

| Parameter | Input | Output |
|-----------|-------|--------|
| `amount` | "₹10,000" | 10000.0 |
| `month` | "January" | 1 |
| `year` | "2026" | 2026 |
| `date` | "Today" | "2026-07-04" |
| `date` | "Yesterday" | "2026-07-03" |
| `days` | "30 days" | 30 |
| `target_type` | "Students" | "students" |
| `due_date` | "Tomorrow" | "2026-07-05" |
| `due_date` | "This Week" | "2026-07-06" |

## Flow Examples

### Example 1: Low Confidence

```
User: "Show attendance"
↓
Gemini: intent="unknown", confidence=0.5, module="attendance"
↓
ClarificationService: confidence < 0.85
↓
Response: {
    "type": "clarification",
    "question": "Which attendance report would you like?",
    "options": ["Today's Absences", "Monthly Percentage", ...]
}
↓
User: "Monthly Percentage"
↓
Resolve: intent="attendance.monthly_percentage"
↓
Proceed with intent
```

### Example 2: Missing Parameter

```
User: "Show students with pending fees above"
↓
Gemini: intent="fee.pending_above", confidence=0.9, parameters={}
↓
ClarificationService: missing required param "amount"
↓
Response: {
    "type": "clarification",
    "question": "What is the minimum fee amount?",
    "options": ["₹1,000", "₹5,000", "₹10,000", "₹25,000"]
}
↓
User: "₹10,000"
↓
Resolve: param_name="amount", value=10000
↓
Proceed with parameters
```

### Example 3: High Confidence

```
User: "Show students absent today"
↓
Gemini: intent="attendance.absent_today", confidence=0.98
↓
ClarificationService: confidence >= 0.85, no missing params
↓
Proceed directly
```

## Performance Considerations

### No Additional Database Queries

The ClarificationService:
- Uses only config data
- No database queries
- No caching needed
- Minimal memory footprint

### Response Time

- Clarification check: < 1ms
- Config lookup: < 1ms
- Total overhead: < 2ms

## Debug Logging

In development/local environments, the system logs:

```
[AI Clarify] Clarification needed {
    "reason": "low_confidence",
    "confidence": 0.5,
    "module": "attendance",
    "options_count": 4
}

[AI Clarify] Parameter clarification {
    "intent": "fee.pending_above",
    "missing_param": "amount",
    "options_count": 4
}
```

## Future Enhancements

### Short-term
- Add multi-step clarification flows
- Add clarification history tracking
- Add smart defaults based on user behavior

### Medium-term
- Add ML-based confidence calibration
- Add personalized clarification options
- Add context-aware suggestions

### Long-term
- Add natural language clarification
- Add voice-based clarification
- Add predictive clarification
