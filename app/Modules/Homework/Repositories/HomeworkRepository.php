<?php

namespace App\Modules\Homework\Repositories;

use App\Modules\Homework\Models\Homework;
use Illuminate\Database\Eloquent\Builder;

class HomeworkRepository implements HomeworkRepositoryInterface
{
    public function query(): Builder
    {
        return Homework::query()
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject']);
    }

    public function create(array $data): Homework
    {
        return Homework::query()->create($data);
    }

    public function update(Homework $homework, array $data): Homework
    {
        $homework->fill($data)->save();

        return $homework->refresh();
    }

    public function delete(Homework $homework): void
    {
        $homework->delete();
    }
}
