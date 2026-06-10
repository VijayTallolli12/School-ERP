<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;

interface LeaveRequestRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): LeaveRequest;

    public function update(LeaveRequest $leaveRequest, array $data): LeaveRequest;

    public function delete(LeaveRequest $leaveRequest): void;
}
