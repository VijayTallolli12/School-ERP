<?php

namespace App\Modules\HR\Repositories;

use App\Modules\HR\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function query(): Builder
    {
        return Employee::query()
            ->with(['department', 'designation', 'reportingTo']);
    }

    public function findById(int $id): ?Employee
    {
        return Employee::query()->find($id);
    }

    public function create(array $data): Employee
    {
        return Employee::query()->create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->fill($data)->save();

        return $employee->refresh();
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }
}
