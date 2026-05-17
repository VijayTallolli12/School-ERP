<?php

namespace App\Modules\Students\Repositories;

use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Builder;

class StudentRepository implements StudentRepositoryInterface
{
    public function query(): Builder
    {
        return Student::query()
            ->with([
                'user',
                'guardians',
                'sessions.academicYear',
                'sessions.classSection.schoolClass',
                'sessions.classSection.section',
            ]);
    }

    public function create(array $data): Student
    {
        return Student::query()->create($data);
    }

    public function update(Student $student, array $data): Student
    {
        $student->fill($data)->save();

        return $student->refresh();
    }

    public function delete(Student $student): void
    {
        $student->delete();
    }
}
