<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Leave\Models\LeaveRequest;

class LeaveQueryHandler
{
    private const MAX_RESULTS = 50;

    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function pendingLeave(array $parameters): array
    {
        $limit = max(1, min((int) ($parameters['limit'] ?? 25), self::MAX_RESULTS));

        $requests = LeaveRequest::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'pending')
            ->with(['leaveType', 'user'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return [
            'count' => $requests->count(),
            'records' => $requests->map(fn (LeaveRequest $l) => [
                'id' => $l->id,
                'requester' => $l->user?->name,
                'leave_type' => $l->leaveType?->name,
                'from_date' => $l->from_date?->toDateString(),
                'to_date' => $l->to_date?->toDateString(),
                'days' => $l->days,
                'reason' => $l->reason,
                'status' => $l->status,
            ])->all(),
            'summary' => null,
        ];
    }
}
