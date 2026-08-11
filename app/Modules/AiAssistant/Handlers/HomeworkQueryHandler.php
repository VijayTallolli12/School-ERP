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
            ->where('status', 'published')
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

    public function homeworkDue(?string $date = null): string
    {
        $schoolId = $this->schoolContext->id();
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();
        $endOfDay = (clone $targetDate)->endOfDay();

        $homework = Homework::query()
            ->where('school_id', $schoolId)
            ->where('status', 'published')
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
            ->where('status', 'published')
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