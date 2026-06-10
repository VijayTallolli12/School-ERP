<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\LeaveType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface LeaveTypeRepositoryInterface
{
    public function query(): Builder;

    public function all(): Collection;

    public function create(array $data): LeaveType;

    public function update(LeaveType $leaveType, array $data): LeaveType;

    public function delete(LeaveType $leaveType): void;
}
