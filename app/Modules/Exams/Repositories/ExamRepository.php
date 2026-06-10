<?php

namespace App\Modules\Exams\Repositories;

use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use Illuminate\Database\Eloquent\Builder;

class ExamRepository implements ExamRepositoryInterface
{
    public function query(): Builder
    {
        return Exam::query()
            ->with(['academicYear', 'classSection.schoolClass', 'classSection.section', 'subject']);
    }

    public function create(array $data): Exam
    {
        return Exam::query()->create($data);
    }

    public function update(Exam $exam, array $data): Exam
    {
        $exam->fill($data)->save();

        return $exam->refresh();
    }

    public function delete(Exam $exam): void
    {
        $exam->delete();
    }

    public function resultsQuery(Exam $exam): Builder
    {
        return ExamResult::query()
            ->leftJoin('students', 'exam_results.student_id', '=', 'students.id')
            ->where('exam_results.exam_id', $exam->id)
            ->select('exam_results.*')
            ->with(['student', 'exam.classSection.schoolClass', 'exam.classSection.section']);
    }

    public function createResult(array $data): ExamResult
    {
        return ExamResult::query()->create($data);
    }

    public function updateResult(ExamResult $result, array $data): ExamResult
    {
        $result->fill($data)->save();

        return $result->refresh();
    }

    public function deleteResult(ExamResult $result): void
    {
        $result->delete();
    }
}
