<?php

namespace App\Modules\Students\Repositories;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;

interface StudentRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): Student;

    public function update(Student $student, array $data): Student;

    public function delete(Student $student): void;
}
