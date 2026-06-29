<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendanceMarked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $schoolId,
        public readonly int $studentId,
        public readonly string $status,
        public readonly string $date,
        public readonly string $studentName = '',
        public readonly string $markedAt = '',
        public readonly array $extra = [],
    ) {}
}
