# Route Report — Phase 08: AI Role Awareness

## Routes

**No new routes were added in this phase.**

| Route | Action | Controller | Status |
|-------|--------|------------|--------|
| `POST /ai/ask` | `ask` | `AiChatController` | **Reused** — unchanged; previously handled teacher-only queries, now handles role-aware queries via updated `AIService` |

## Reasoning

Because authorization and data scoping were moved into `RoleDataScoper` (a service-layer concern), the existing `/ai/ask` route and its controller required zero modifications to the routing layer. All role-aware logic is injected at the service level.
