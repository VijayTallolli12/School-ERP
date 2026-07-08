<?php

namespace App\Modules\HR\Repositories;

use App\Modules\HR\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

interface EmployeeRepositoryInterface
{
    public function query(): Builder;

    public function findById(int $id): ?Employee;

    public function create(array $data): Employee;

    public function update(Employee $employee, array $data): Employee;

    public function delete(Employee $employee): void;
}
