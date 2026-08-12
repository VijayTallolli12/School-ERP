<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Homework\Models\Homework;
use App\Modules\Homework\Services\HomeworkService;
use Illuminate\Support\Carbon;

class HomeworkQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
        private readonly HomeworkService $homeworkService,
    ) {}

    public function pendingHomework(): string
    {
        $schoolId = $this->schoolContext->id();

        $homework = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('due_date', '>=', Carbon::today())
            ->with(['classSection.schoolClass', 'classSection.section', 'subject'])
            ->orderBy('due_date')
            ->limit(50)
            ->get();

        if ($homework->isEmpty()) {
            return 'No pending homework found.';
        }

        $lines = ['Pending Homework Assignments:', ''];

        foreach ($homework as $hw) {
            $className = $hw->classSection?->display_name ?? 'N/A';
            $subjectName = $hw->subject?->name ?? 'N/A';
            $dueDate = $hw->due_date?->format('d M Y') ?? 'No due date';
            $lines[] = "• {$subjectName} — {$hw->title} — Class: {$className} — Due: {$dueDate}";
        }

        return implode("\n", $lines);
    }

    public function pending(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $query = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->where('due_date', '>=', Carbon::today())
            ->with(['classSection.schoolClass', 'classSection.section', 'subject'])
            ->orderBy('due_date');

        if (!empty($parameters['class_section_id'])) {
            $query->where('class_section_id', $parameters['class_section_id']);
        } elseif (!empty($parameters['class_section_ids'])) {
            $query->whereIn('class_section_id', (array) $parameters['class_section_ids']);
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), 50));

        $homework = $query->limit($limit)->get();

        return [
            'count' => $homework->count(),
            'records' => $this->present($homework),
            'summary' => null,
        ];
    }

    public function due(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();
        $targetDate = !empty($parameters['date'])
            ? Carbon::parse($parameters['date'])
            : Carbon::today();

        $query = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereDate('due_date', $targetDate->toDateString())
            ->with(['classSection.schoolClass', 'classSection.section', 'subject'])
            ->orderBy('due_date');

        if (!empty($parameters['class_section_id'])) {
            $query->where('class_section_id', $parameters['class_section_id']);
        } elseif (!empty($parameters['class_section_ids'])) {
            $query->whereIn('class_section_id', (array) $parameters['class_section_ids']);
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), 50));

        $homework = $query->limit($limit)->get();

        return [
            'count' => $homework->count(),
            'records' => $this->present($homework),
            'summary' => ['date' => $targetDate->toDateString()],
        ];
    }

    public function list(array $parameters): array
    {
        $schoolId = $this->schoolContext->id();

        $query = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with(['classSection.schoolClass', 'classSection.section', 'subject'])
            ->orderBy('due_date');

        if (!empty($parameters['class_section_id'])) {
            $query->where('class_section_id', $parameters['class_section_id']);
        } elseif (!empty($parameters['class_section_ids'])) {
            $query->whereIn('class_section_id', (array) $parameters['class_section_ids']);
        }

        if (!empty($parameters['subject_id'])) {
            $query->where('subject_id', $parameters['subject_id']);
        }

        $limit = max(1, min((int) ($parameters['limit'] ?? 50), 50));

        $homework = $query->limit($limit)->get();

        return [
            'count' => $homework->count(),
            'records' => $this->present($homework),
            'summary' => null,
        ];
    }

    private function present($homework): array
    {
        return $homework->map(function ($hw) {
            $className = $hw->classSection
                ? trim(($hw->classSection->schoolClass->name ?? '') . ' - ' . ($hw->classSection->section->name ?? ''))
                : 'N/A';

            return [
                'id' => $hw->id,
                'title' => $hw->title,
                'subject' => $hw->subject?->name,
                'class' => $className,
                'due_date' => $hw->due_date?->toDateString(),
                'status' => $hw->status,
            ];
        })->all();
    }

    public function homeworkDue(?string $date = null): string
    {
        $schoolId = $this->schoolContext->id();
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $endOfDay = (clone $targetDate)->endOfDay();

        $homework = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereBetween('due_date', [$targetDate, $endOfDay])
            ->with(['classSection.schoolClass', 'classSection.section', 'subject'])
            ->orderBy('due_date')
            ->limit(50)
            ->get();

        if ($homework->isEmpty()) {
            return "No homework due on {$targetDate->format('d M Y')}.";
        }

        $lines = ["Homework Due on {$targetDate->format('d M Y')}:", ''];

        foreach ($homework as $hw) {
            $className = $hw->classSection?->display_name ?? 'N/A';
            $subjectName = $hw->subject?->name ?? 'N/A';
            $dueDate = $hw->due_date?->format('d M Y') ?? 'No due date';
            $lines[] = "• {$subjectName} — {$hw->title} — Class: {$className} — Due: {$dueDate}";
        }

        return implode("\n", $lines);
    }

    public function listHomework(?int $classSectionId = null, ?int $subjectId = null): string
    {
        $schoolId = $this->schoolContext->id();

        $query = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->with(['classSection.schoolClass', 'classSection.section', 'subject'])
            ->orderBy('due_date');

        if ($classSectionId) {
            $query->where('class_section_id', $classSectionId);
        }

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        $homework = $query->limit(50)->get();

        if ($homework->isEmpty()) {
            return 'No homework assignments found.';
        }

        $lines = ['Homework Assignments:', ''];

        foreach ($homework as $hw) {
            $className = $hw->classSection?->display_name ?? 'N/A';
            $subjectName = $hw->subject?->name ?? 'N/A';
            $dueDate = $hw->due_date?->format('d M Y') ?? 'No due date';
            $status = ucfirst($hw->status);
            $lines[] = "• {$subjectName} — {$hw->title} — Class: {$className} — Due: {$dueDate} — Status: {$status}";
        }

        return implode("\n", $lines);
    }
}
