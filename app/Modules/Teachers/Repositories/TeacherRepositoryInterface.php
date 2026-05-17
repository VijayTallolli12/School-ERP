<?php

namespace App\Modules\Teachers\Repositories;

use App\Modules\Teachers\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

interface TeacherRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): Teacher;

    public function update(Teacher $teacher, array $data): Teacher;

    public function delete(Teacher $teacher): void;
}
