<?php

namespace App\Modules\Exams\Services;

use App\Core\Tenant\SchoolContext;
use App\Modules\Exams\Models\Exam;
use App\Modules\Exams\Models\ExamResult;
use App\Modules\Exams\Repositories\ExamRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ExamService
{
    public function __construct(private readonly ExamRepositoryInterface $exams) {}

    public function create(array $data): Exam
    {
        return DB::transaction(function () use ($data): Exam {
            $schoolId = app(SchoolContext::class)->id();

            $payload = $this->examPayload($data);
            $payload['school_id'] = $schoolId;
            $payload['created_by'] = auth()->id();
            $payload['updated_by'] = auth()->id();

            return $this->exams->create($payload);
        });
    }

    public function update(Exam $exam, array $data): Exam
    {
        return DB::transaction(function () use ($exam, $data): Exam {
            $payload = $this->examPayload($data);
            $payload['updated_by'] = auth()->id();

            return $this->exams->update($exam, $payload);
        });
    }

    public function publish(Exam $exam): Exam
    {
        return $this->exams->update($exam, [
            'is_published' => ! $exam->is_published,
            'updated_by' => auth()->id(),
        ]);
    }

    public function delete(Exam $exam): void
    {
        $this->exams->delete($exam);
    }

    public function saveResult(array $data): ExamResult
    {
        return DB::transaction(function () use ($data): ExamResult {
            $payload = $this->prepareResultPayload($data);
            $payload['created_by'] = auth()->id();
            $payload['updated_by'] = auth()->id();

            return $this->exams->createResult($payload);
        });
    }

    public function updateResult(ExamResult $result, array $data): ExamResult
    {
        return DB::transaction(function () use ($result, $data): ExamResult {
            $payload = $this->prepareResultPayload($data);
            $payload['updated_by'] = auth()->id();

            return $this->exams->updateResult($result, $payload);
        });
    }

    public function deleteResult(ExamResult $result): void
    {
        $this->exams->deleteResult($result);
    }

    public function bulkSave(Exam $exam, array $results): array
    {
        $schoolId = app(SchoolContext::class)->id();
        $userId = auth()->id();

        return DB::transaction(function () use ($exam, $results, $schoolId, $userId): array {
            $saved = [];
            $now = now();

            foreach ($results as $entry) {
                $studentId = (int) ($entry['student_id'] ?? 0);
                $marks = (int) ($entry['marks_obtained'] ?? 0);
                $grade = $entry['grade'] ?? null;
                $remarks = $entry['remarks'] ?? null;
                $status = $marks >= $exam->pass_marks ? 'pass' : 'fail';

                $payload = [
                    'school_id' => $schoolId,
                    'exam_id' => $exam->id,
                    'student_id' => $studentId,
                    'marks_obtained' => $marks,
                    'grade' => $grade,
                    'remarks' => $remarks,
                    'status' => $status,
                    'updated_by' => $userId,
                ];

                $existing = ExamResult::query()
                    ->where('exam_id', $exam->id)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existing) {
                    $payload['created_by'] ??= $existing->created_by;
                    $existing->fill($payload)->save();
                    $saved[] = $existing->fresh();
                } else {
                    $payload['created_by'] = $userId;
                    $saved[] = ExamResult::query()->create($payload);
                }
            }

            return $saved;
        });
    }

    private function examPayload(array $data): array
    {
        return Arr::only($data, [
            'academic_year_id',
            'class_section_id',
            'subject_id',
            'exam_name',
            'exam_type',
            'exam_date',
            'maximum_marks',
            'pass_marks',
            'status',
            'is_published',
        ]);
    }

    private function prepareResultPayload(array $data): array
    {
        $exam = Exam::query()->findOrFail($data['exam_id']);

        $marks = (int) ($data['marks_obtained'] ?? 0);
        $status = $marks >= $exam->pass_marks ? 'pass' : 'fail';

        return array_merge(Arr::only($data, [
            'school_id',
            'exam_id',
            'student_id',
            'marks_obtained',
            'grade',
            'remarks',
        ]), [
            'status' => $status,
        ]);
    }
}
