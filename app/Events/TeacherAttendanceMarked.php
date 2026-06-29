<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeacherAttendanceMarked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $schoolId,
        public readonly int $teacherId,
        public readonly string $status,
        public readonly string $date,
        public readonly string $teacherName = '',
        public readonly string $markedAt = '',
        public readonly array $extra = [],
    ) {}
}
