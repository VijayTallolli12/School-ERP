<?php

namespace App\Modules\Homework\Repositories;

use App\Modules\Homework\Models\Homework;
use Illuminate\Database\Eloquent\Builder;

interface HomeworkRepositoryInterface
{
    public function query(): Builder;

    public function create(array $data): Homework;

    public function update(Homework $homework, array $data): Homework;

    public function delete(Homework $homework): void;
}
