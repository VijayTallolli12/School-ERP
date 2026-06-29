<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HomeworkAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $homeworkId,
        public readonly int $classSectionId,
        public readonly string $title,
        public readonly ?string $dueDate,
        public readonly array $studentIds = [],
        public readonly array $extra = [],
    ) {}
}
