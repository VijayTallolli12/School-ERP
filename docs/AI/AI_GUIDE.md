# AI Guide

Version: 1.0.0

Revision date: 2026-07-08

## 1. Architecture

The AI subsystem is implemented through AIService, AIIntentService, PromptBuilder, PlannerService, OrchestratorService, role-aware handlers, and the AI agent registry.

## 2. Intent Routing

User requests are passed through the intent service, which resolves the action intent and returns a route plan for execution.

## 3. Prompt Builder

PromptBuilder constructs prompts and context for supported intents and workflows.

## 4. Executive Copilot

Executive or multi-step intents can be routed through the planner/orchestrator pipeline for coordinated execution.

## 5. Role Awareness and Security

The AI layer checks user role and authorization before executing actions. Sensitive requests are logged and may require confirmation.

## 6. Safety and Audit

AI query logs and agent execution records are stored to support audit and troubleshooting.

## 7. Token Optimization and Caching

The current implementation includes intent routing and logs, but caching and token optimization details should be reviewed against the live code paths before production tuning.
