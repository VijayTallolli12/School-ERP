<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FeeReminderGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $studentFeeId,
        public readonly int $studentId,
        public readonly int $parentUserId,
        public readonly float $amountDue,
        public readonly ?string $dueDate,
        public readonly array $extra = [],
    ) {}
}
