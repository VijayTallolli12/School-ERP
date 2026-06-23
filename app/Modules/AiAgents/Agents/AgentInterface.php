<?php

namespace App\Modules\AiAgents\Agents;

interface AgentInterface
{
    public function name(): string;

    public function description(): string;

    public function preview(array $params): array;

    public function execute(array $params): array;

    public function permissions(): array;

    public function config(): array;

    public function validateParams(array $params): array;
}
