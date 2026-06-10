<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestRepository implements LeaveRequestRepositoryInterface
{
    public function query(): Builder
    {
        return LeaveRequest::query()
            ->with(['student', 'leaveType', 'user', 'approver']);
    }

    public function create(array $data): LeaveRequest
    {
        return LeaveRequest::query()->create($data);
    }

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest
    {
        $leaveRequest->fill($data)->save();

        return $leaveRequest->refresh();
    }

    public function delete(LeaveRequest $leaveRequest): void
    {
        $leaveRequest->delete();
    }
}
