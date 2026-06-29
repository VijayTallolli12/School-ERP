<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentExecutionCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $executionId,
        public readonly string $agentName,
        public readonly string $status,
        public readonly ?string $summary,
        public readonly array $extra = [],
    ) {}
}
