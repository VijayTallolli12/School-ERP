<?php

namespace App\Modules\AiAgents\Registry;

use App\Modules\AiAgents\Agents\AgentInterface;
use RuntimeException;

class AgentRegistry
{
    private array $agents = [];

    public function register(AgentInterface $agent): void
    {
        $this->agents[$agent->name()] = $agent;
    }

    public function get(string $name): AgentInterface
    {
        if (!isset($this->agents[$name])) {
            throw new RuntimeException("Agent '{$name}' is not registered.");
        }

        return $this->agents[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->agents[$name]);
    }

    public function all(): array
    {
        return $this->agents;
    }

    public function definitions(): array
    {
        $definitions = [];

        foreach ($this->agents as $agent) {
            $definitions[$agent->name()] = [
                'name' => $agent->name(),
                'description' => $agent->description(),
                'permissions' => $agent->permissions(),
                'config' => $agent->config(),
                'status' => 'active',
                'execution_mode' => 'manual',
            ];
        }

        ksort($definitions);

        return $definitions;
    }
}
