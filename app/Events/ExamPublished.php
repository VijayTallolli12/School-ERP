<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $examId,
        public readonly string $examName,
        public readonly int $classSectionId,
        public readonly array $studentIds = [],
        public readonly array $extra = [],
    ) {}
}
