<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Repositories\EmployeeRepositoryInterface;

class EmployeeService
{
    public function __construct(private readonly EmployeeRepositoryInterface $employees) {}

    public function create(array $data): Employee
    {
        $data['created_by'] = auth()->id();
        $data['employment_status'] ??= 'active';

        return $this->employees->create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $data['updated_by'] = auth()->id();

        return $this->employees->update($employee, $data);
    }

    public function delete(Employee $employee): void
    {
        $this->employees->delete($employee);
    }
}
