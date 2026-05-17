<?php

namespace App\Modules\Teachers\Repositories;

use App\Modules\Teachers\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function query(): Builder
    {
        return Teacher::query()
            ->with([
                'subjects',
                'classSections.schoolClass',
                'classSections.section',
                'classTeacherSections.schoolClass',
                'classTeacherSections.section',
                'documents',
            ]);
    }

    public function create(array $data): Teacher
    {
        return Teacher::query()->create($data);
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $teacher->fill($data)->save();

        return $teacher->refresh();
    }

    public function delete(Teacher $teacher): void
    {
        $teacher->delete();
    }
}
