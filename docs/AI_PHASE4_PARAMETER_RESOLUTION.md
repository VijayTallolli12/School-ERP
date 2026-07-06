# AI Phase 4: Smart Parameter Resolution

## Overview

Phase AI-4 introduces a dedicated `ParameterResolver` service that converts human-readable parameters from Gemini into ERP IDs. This eliminates the need for handlers to perform database lookups, centralizing parameter resolution in a single, cacheable service.

## Architecture

```
User Question
    ↓
Gemini (returns human-readable parameters)
    ↓
ParameterResolver (converts to ERP IDs)
    ↓
AgentRouter
    ↓
Handler (receives resolved IDs)
```

## Before vs After

### Before
```json
{
    "class": "Class 5",
    "subject": "Science"
}
```

Handler must query database to find `class_section_id` and `subject_id`.

### After
```json
{
    "class": "Class 5",
    "subject": "Science",
    "class_section_id": 9,
    "subject_id": 3
}
```

Handler receives resolved IDs directly.

## Supported Parameters

| Parameter | Input Example | Output Field | Output Value |
|-----------|---------------|--------------|--------------|
| Class | "Class 5" | `class_section_id` | 9 |
| Section | "Section B" | `class_section_id` | 10 |
| Class+Section | "Class 5 Section B" | `class_section_id` | 10 |
| Subject | "Science" | `subject_id` | 3 |
| Route | "Route A" | `route_id` | 1 |
| Exam | "Mid Term" | `exam_id` | 8 |
| Department | "Teaching Staff" | `department_id` | 2 |
| Fee Category | "Tuition" | `fee_category_id` | 1 |
| Leave Type | "Sick Leave" | `leave_type_id` | 1 |

## Matching Rules

### Exact Match
- Input: "Class 5" → Matches "Class 5" ✓
- Case-insensitive: "CLASS 5" → Matches "Class 5" ✓

### Alias Match
- Input: "Math" → Matches "Mathematics" ✓
- Input: "Science" → Matches "Science" ✓
- Input: "Sick" → Matches "Sick Leave" ✓

### Partial Match
- Input: "Route A" → Matches "Route A - North Campus" ✓
- Input: "Class 5 Section B" → Matches "Class 5 - Section B" ✓

### No Match
- Input: "Class 15" → Returns `null` ✓
- Input: "Physics" → Returns `null` ✓

## Caching Strategy

```php
Cache::remember($cacheKey, 600, function () use ($schoolId) {
    return Model::query()
        ->where('school_id', $schoolId)
        ->get()
        ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
        ->toArray();
});
```

- **Cache Key:** `ai_{entity}_{school_id}`
- **TTL:** 10 minutes (600 seconds)
- **Scope:** Per school (multi-tenant safe)
- **Storage:** Arrays (not Eloquent models) for reliable serialization

### Cache Keys

| Entity | Cache Key | Example |
|--------|-----------|---------|
| Class Sections | `ai_class_sections_{school_id}` | `ai_class_sections_1` |
| Subjects | `ai_subjects_{school_id}` | `ai_subjects_1` |
| Routes | `ai_routes_{school_id}` | `ai_routes_1` |
| Exams | `ai_exams_{school_id}` | `ai_exams_1` |
| Departments | `ai_departments_{school_id}` | `ai_departments_1` |
| Fee Categories | `ai_fee_categories_{school_id}` | `ai_fee_categories_1` |
| Leave Types | `ai_leave_types_{school_id}` | `ai_leave_types_1` |

## Error Handling

Unknown values return `null` instead of failing:

```php
$resolver->resolve(['class' => 'Class 15']);
// Returns: ['class' => 'Class 15', 'class_section_id' => null]
```

This allows handlers to decide how to handle missing parameters.

## Usage

### Basic Usage
```php
$resolver = app(ParameterResolver::class);

$resolved = $resolver->resolve([
    'class' => 'Class 5',
    'subject' => 'Science',
]);

// Result:
// [
//     'class' => 'Class 5',
//     'subject' => 'Science',
//     'class_section_id' => 9,
//     'subject_id' => 3,
// ]
```

### In AIService
```php
// After intent resolution, before handler execution
$resolvedParams = $this->parameterResolver->resolve($intentResult['parameters']);
$intentResult['parameters'] = $resolvedParams;
```

### Clearing Cache
```php
$resolver->clearCache();
```

## File Changes

### New Files

| File | Purpose |
|------|---------|
| `app/Modules/AiAssistant/Services/ParameterResolver.php` | Resolves human-readable parameters to ERP IDs |
| `docs/AI_PHASE4_PARAMETER_RESOLUTION.md` | This documentation |

### Unchanged Files

| File | Reason |
|------|--------|
| `AIIntentService.php` | No changes needed |
| `PromptBuilder.php` | No changes needed |
| `ContextBuilder.php` | No changes needed |
| `AgentRouter.php` | Route mapping unchanged |
| `AIService.php` | Can optionally use ParameterResolver |
| `AIResponseFormatter.php` | Response formatting unchanged |
| All handlers | No changes needed |
| All agents | No changes needed |
| All tests | Output format unchanged |

## Performance Considerations

### Database Queries

Each resolution executes up to 7 queries (one per entity type):
1. `ClassSection` with relations (1 query)
2. `Subject` (1 query)
3. `Route` (1 query)
4. `Exam` (1 query)
5. `PayrollDepartment` (1 query)
6. `FeeCategory` (1 query)
7. `LeaveType` (1 query)

**Total:** 7 queries per cache miss (cached for 10 minutes)

### Cache Hit Rate

With 10-minute TTL:
- First request: 7 queries
- Next ~500 requests (assuming 1 req/min): 0 queries
- **Effective query reduction:** ~98%

### Query Optimization

All queries use:
- `school_id` scope (via `BelongsToSchool` trait)
- `status` or `is_active` filters
- `toArray()` for minimal data transfer
- Array storage in cache (not Eloquent models)

## Debug Logging

In development/local environments, the system logs:

```
[AI Params] Parameters resolved {
    "input": {"class": "Class 5", "subject": "Science"},
    "output": {"class": "Class 5", "subject": "Science", "class_section_id": 9, "subject_id": 3},
    "duration_ms": 12.5
}

[AI Params] Class resolved {
    "input": "Class 5",
    "class_section_id": 9,
    "class": "Class 5",
    "section": "Section A"
}

[AI Params] Subject resolved {
    "input": "Science",
    "subject_id": 3,
    "name": "Science"
}
```

## Adding New Parameter Types

To add support for a new parameter type:

1. Add a new private method in `ParameterResolver`:
```php
private function resolveNewType(string $name): ?int
{
    $schoolId = app(SchoolContext::class)->id();

    if (!$schoolId) {
        return null;
    }

    $cacheKey = "ai_new_types_{$schoolId}";

    $items = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
        return NewType::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->get()
            ->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])
            ->toArray();
    });

    $normalizedInput = $this->normalize($name);

    foreach ($items as $item) {
        if ($this->matchesWithAliases($normalizedInput, $item['name'], 'new_type')) {
            return $item['id'];
        }
    }

    return null;
}
```

2. Add the method call in `resolve()`:
```php
if (!empty($parameters['new_type']) && empty($parameters['new_type_id'])) {
    $resolved['new_type_id'] = $this->resolveNewType($parameters['new_type']);
}
```

3. Add cache clearing in `clearCache()`:
```php
Cache::forget("ai_new_types_{$schoolId}");
```

4. Add aliases if needed:
```php
private const ALIASES = [
    // ... existing aliases
    'new_type' => [
        'alias1' => ['full_name1'],
        'alias2' => ['full_name2'],
    ],
];
```

## Future Enhancements

### Short-term
- Add fuzzy matching for typo tolerance
- Add synonyms for more entities
- Add batch resolution for multiple parameters

### Medium-term
- Add resolution statistics
- Add confidence scores for matches
- Add machine learning for match optimization

### Long-term
- Add NLP-based entity extraction
- Add context-aware resolution
- Add multi-language support
