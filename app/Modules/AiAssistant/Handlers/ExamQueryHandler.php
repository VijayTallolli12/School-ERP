<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Exams\Models\Exam;
use Illuminate\Support\Carbon;

/**
 * Structured exam queries for the AI intelligence layer.
 * Always scoped to the current school. Returns arrays, never raw SQL.
 */
class ExamQueryHandler
{
    private const MAX_RESULTS = 50;

    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function search(array $parameters): array
    {
        $query = $this->baseQuery($parameters);

        return $this->toList($query, $parameters['limit'] ?? self::MAX_RESULTS);
    }

    public function count(array $parameters): array
    {
        $count = $this->baseQuery($parameters)->count();

        return [
            'count' => $count,
            'records' => [],
            'summary' => null,
        ];
    }

    public function get(array $parameters): array
    {
        $query = $this->baseQuery($parameters);

        $exam = $query->orderBy('exam_date')->first();

        return [
            'count' => $exam ? 1 : 0,
            'record' => $exam ? $this->present($exam) : null,
            'records' => $exam ? [$this->present($exam)] : [],
            'summary' => null,
        ];
    }

    public function upcoming(array $parameters): array
    {
        $query = Exam::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'scheduled');

        $this->applyCommonFilters($query, $parameters);

        return $this->toList($query, $parameters['limit'] ?? self::MAX_RESULTS);
    }

    public function completed(array $parameters): array
    {
        $query = Exam::query()
            ->where('school_id', $this->schoolContext->id())
            ->where('status', 'completed');

        $this->applyCommonFilters($query, $parameters);

        return $this->toList($query, $parameters['limit'] ?? self::MAX_RESULTS);
    }

    private function baseQuery(array $parameters)
    {
        $query = Exam::query()
            ->where('school_id', $this->schoolContext->id());

        $this->applyCommonFilters($query, $parameters);

        return $query;
    }

    private function applyCommonFilters($query, array $parameters): void
    {
        if (!empty($parameters['exam_type'])) {
            $types = (array) $parameters['exam_type'];
            $query->where(function ($q) use ($types) {
                foreach ($types as $type) {
                    $q->orWhere('exam_type', $type);
                }
            });
        }

        if (!empty($parameters['date_from'])) {
            $query->whereDate('exam_date', '>=', $parameters['date_from']);
        }

        if (!empty($parameters['date_to'])) {
            $query->whereDate('exam_date', '<=', $parameters['date_to']);
        }

        if (!empty($parameters['class_section_id'])) {
            $query->where('class_section_id', $parameters['class_section_id']);
        } elseif (!empty($parameters['class_section_ids'])) {
            $query->whereIn('class_section_id', (array) $parameters['class_section_ids']);
        }

        if (!empty($parameters['subject_id'])) {
            $query->where('subject_id', $parameters['subject_id']);
        }

        if (!empty($parameters['status'])) {
            $status = $parameters['status'];
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', $status);
            }
        }

        if (!empty($parameters['exam_id'])) {
            $query->where('id', $parameters['exam_id']);
        }
    }

    private function toList($query, int $limit): array
    {
        $limit = max(1, min($limit, self::MAX_RESULTS));

        $exams = $query
            ->with(['classSection.schoolClass', 'classSection.section', 'subject', 'academicYear'])
            ->orderByDesc('exam_date')
            ->limit($limit)
            ->get();

        return [
            'count' => $exams->count(),
            'records' => $exams->map(fn (Exam $exam) => $this->present($exam))->all(),
            'summary' => null,
        ];
    }

    private function present(Exam $exam): array
    {
        $classSection = $exam->classSection;

        return [
            'id' => $exam->id,
            'exam_name' => $exam->exam_name,
            'exam_type' => $exam->exam_type,
            'exam_date' => $exam->exam_date?->toDateString(),
            'class_section_id' => $exam->class_section_id,
            'class' => $classSection
                ? trim(($classSection->schoolClass->name ?? '') . ' - ' . ($classSection->section->name ?? ''))
                : null,
            'subject_id' => $exam->subject_id,
            'subject' => $exam->subject?->name,
            'maximum_marks' => $exam->maximum_marks,
            'pass_marks' => $exam->pass_marks,
            'status' => $exam->status,
            'is_published' => (bool) $exam->is_published,
            'academic_year' => $exam->academicYear?->name,
        ];
    }
}
