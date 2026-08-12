# AI Guide

Version: 2.0.0

Revision date: 2026-08-12

## 1. Architecture

The AI subsystem is implemented through AIService, QueryPlanner,
ErpToolRegistry, ErpQueryExecutor, role-aware handlers, and the AI agent
registry. Ask ERP and the Executive Gemini share this single intelligence
layer.

## 2. Intent Routing

User questions are passed through QueryPlanner, which converts natural
language into a structured tool request (`{tool, parameters, confidence}`).
A deterministic keyword/date/synonym fallback keeps the assistant working even
when the LLM provider is unavailable.

## 3. Tool Registry

ErpToolRegistry is the single source of truth for every ERP operation the AI
may use (e.g. `exam.search`, `fee.pending`, `attendance.summary`,
`transport.status`, `school.summary`). The AI only selects allowed tools; the
backend validates and executes them.

## 4. Executive Gemini

The Executive Gemini dashboard uses the same `/admin/ai/ask` pipeline. The
`school.summary` tool aggregates attendance, fees, transport, homework, exams,
leave, notifications and library data from real ERP queries.

## 5. Role Awareness and Security

The AI layer checks user role and authorization before executing actions.
All queries are scoped to the active school tenant. Sensitive requests are
logged and require confirmation.

## 6. Safety and Audit

AiQueryLog and agent execution records store user, school, question, intent,
structured parameters, result count and status. Never log API keys or tokens.

## 7. Provider Configuration

Set `AI_PROVIDER=gemini` or `AI_PROVIDER=openai` in `.env`. Credentials are
read from `GEMINI_API_KEY` / `OPENAI_API_KEY` and only ever used from the
backend.
