# AI Module

Version: 1.0.0

Revision date: 2026-07-08

## Purpose

The AI module provides intent-based assistant workflows such as Ask ERP, executive copilot planning, and role-aware operational support.

## Architecture

The AI pipeline uses AIService, AIIntentService, PromptBuilder, PlannerService, OrchestratorService, and the agent registry.

## Database Tables

- ai_query_logs
- agent_executions

## Models

- App\Modules\AiAssistant\Models\AiQueryLog

## Controllers

- AIController
- AgentController

## Services

- AIService
- AIIntentService
- PlannerService
- OrchestratorService
- PromptBuilder
- ContextBuilder

## Routes

- /admin/ai/dashboard
- /admin/agents
- /admin/agents/history

## Permissions

- ai.view
- ai.execute

## Business Rules

- AI actions must be authorized based on role and intent.
- Sensitive or destructive intents require confirmation and logging.
- AI responses are scoped to the active school context.

## Workflow

1. A user submits a natural language question.
2. The intent service resolves the task.
3. A route or agent is selected.
4. The request is executed and logged.

## Common Issues

- Unknown intents return a fallback response.
- Authorization failures appear when the role cannot execute the requested operation.

## Troubleshooting

- Review the AI query log for the unresolved intent.
- Confirm the user has the appropriate role and permission for the action.
