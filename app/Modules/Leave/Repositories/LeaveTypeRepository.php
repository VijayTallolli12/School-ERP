<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\LeaveType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class LeaveTypeRepository implements LeaveTypeRepositoryInterface
{
    public function query(): Builder
    {
        return LeaveType::query();
    }

    public function all(): Collection
    {
        return LeaveType::query()->orderBy('name')->get();
    }

    public function create(array $data): LeaveType
    {
        return LeaveType::query()->create($data);
    }

    public function update(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->fill($data)->save();

        return $leaveType->refresh();
    }

    public function delete(LeaveType $leaveType): void
    {
        $leaveType->delete();
    }
}
