<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Library\Models\BookIssue;
use Illuminate\Support\Carbon;

class LibraryQueryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext
    ) {}

    public function booksIssued(): string
    {
        $schoolId = $this->schoolContext->id();

        $count = BookIssue::query()
            ->where('school_id', $schoolId)
            ->where('status', 'issued')
            ->count();

        return "Currently issued books: {$count}";
    }

    public function overdueBooks(): string
    {
        $schoolId = $this->schoolContext->id();

        $count = BookIssue::query()
            ->where('school_id', $schoolId)
            ->where('status', 'issued')
            ->whereDate('due_date', '<', Carbon::today())
            ->count();

        return "Overdue books: {$count}";
    }

    public function fineCollection(): string
    {
        $schoolId = $this->schoolContext->id();

        $total = (float) BookIssue::query()
            ->where('school_id', $schoolId)
            ->where('status', 'returned')
            ->where('fine_paid', true)
            ->sum('fine_amount');

        $totalFine = (float) BookIssue::query()
            ->where('school_id', $schoolId)
            ->sum('fine_amount');

        return "Total fine collected: \u{20B9}" . number_format($total, 2) . " (total fines levied: \u{20B9}" . number_format($totalFine, 2) . ")";
    }
}
