<?php

namespace App\Modules\AiAssistant\Handlers;

use App\Core\Tenant\SchoolContext;
use App\Modules\Attendance\Models\Attendance;
use App\Modules\Fees\Models\FeePayment;
use App\Modules\Fees\Models\StudentFeeItem;
use App\Modules\Homework\Models\Homework;
use App\Modules\Leave\Models\LeaveRequest;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Transport\Models\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SchoolSummaryHandler
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function getExecutiveSummary(): array
    {
        $schoolId = $this->schoolContext->id();
        $today = Carbon::today()->toDateString();
        $now = Carbon::now();

        return [
            'date' => $today,
            'attendance' => $this->getAttendanceSummary($schoolId, $today),
            'fees' => $this->getFeeSummary($schoolId, $today),
            'transport' => $this->getTransportSummary($schoolId),
            'homework' => $this->getHomeworkSummary($schoolId, $today),
            'exams' => $this->getExamSummary($schoolId),
            'leave' => $this->getLeaveSummary($schoolId, $today),
            'notifications' => $this->getNotificationSummary($schoolId, $today),
            'library' => $this->getLibrarySummary($schoolId),
        ];
    }

    private function getAttendanceSummary(int $schoolId, string $today): array
    {
        $totals = Attendance::query()
            ->where('school_id', $schoolId)
            ->where('attendance_date', $today)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count
            ")
            ->first();

        $total = (int) ($totals->total ?? 0);
        $present = (int) ($totals->present_count ?? 0);
        $absent = (int) ($totals->absent_count ?? 0);
        $late = (int) ($totals->late_count ?? 0);
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

        return [
            'total_marked' => $total,
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'percentage' => $percentage,
        ];
    }

    private function getFeeSummary(int $schoolId, string $today): array
    {
        $pending = (float) StudentFeeItem::query()
            ->whereHas('studentFee.student', fn ($q) => $q->where('school_id', $schoolId))
            ->leftJoin(DB::raw('(SELECT student_fee_item_id, SUM(amount) as paid_sum FROM fee_payment_items WHERE EXISTS (SELECT 1 FROM fee_payments WHERE fee_payments.id = fee_payment_items.fee_payment_id) GROUP BY student_fee_item_id) as fpi'), 'student_fee_items.id', '=', 'fpi.student_fee_item_id')
            ->selectRaw('COALESCE(SUM(student_fee_items.amount - COALESCE(fpi.paid_sum, 0)), 0) as total_pending')
            ->value('total_pending');

        $collectedToday = (float) FeePayment::query()
            ->where('school_id', $schoolId)
            ->whereDate('paid_on', $today)
            ->sum('amount');

        $totalAssigned = (float) StudentFeeItem::query()
            ->whereHas('studentFee.student', fn ($q) => $q->where('school_id', $schoolId))
            ->sum('amount');

        return [
            'total_pending' => max(0, $pending),
            'collected_today' => $collectedToday,
            'total_assigned' => $totalAssigned,
            'collection_rate' => $totalAssigned > 0 ? round((($totalAssigned - max(0, $pending)) / $totalAssigned) * 100, 1) : 0,
        ];
    }

    private function getTransportSummary(int $schoolId): array
    {
        $routes = Route::query()
            ->where('school_id', $schoolId)
            ->withCount('assignments')
            ->with('vehicle')
            ->get();

        $totalStudents = $routes->sum('assignments_count');
        $totalCapacity = $routes->sum(fn ($r) => $r->vehicle?->capacity ?? 0);

        return [
            'total_routes' => $routes->count(),
            'total_students' => $totalStudents,
            'total_capacity' => $totalCapacity,
            'utilization' => $totalCapacity > 0 ? round(($totalStudents / $totalCapacity) * 100, 1) : 0,
        ];
    }

    private function getHomeworkSummary(int $schoolId, string $today): array
    {
        $assignedToday = Homework::query()
            ->where('school_id', $schoolId)
            ->whereDate('created_at', $today)
            ->count();

        $dueToday = Homework::query()
            ->where('school_id', $schoolId)
            ->whereDate('due_date', $today)
            ->count();

        $overdue = Homework::query()
            ->where('school_id', $schoolId)
            ->whereDate('due_date', '<', $today)
            ->count();

        return [
            'assigned_today' => $assignedToday,
            'due_today' => $dueToday,
            'overdue' => $overdue,
        ];
    }

    private function getExamSummary(int $schoolId): array
    {
        $unpublished = \App\Modules\Exams\Models\Exam::query()
            ->where('school_id', $schoolId)
            ->where('is_published', false)
            ->count();

        $published = \App\Modules\Exams\Models\Exam::query()
            ->where('school_id', $schoolId)
            ->where('is_published', true)
            ->count();

        return [
            'published' => $published,
            'unpublished' => $unpublished,
        ];
    }

    private function getLeaveSummary(int $schoolId, string $today): array
    {
        $pendingRequests = LeaveRequest::query()
            ->where('school_id', $schoolId)
            ->where('status', 'pending')
            ->count();

        $approvedToday = LeaveRequest::query()
            ->where('school_id', $schoolId)
            ->where('status', 'approved')
            ->whereDate('updated_at', $today)
            ->count();

        return [
            'pending_requests' => $pendingRequests,
            'approved_today' => $approvedToday,
        ];
    }

    private function getNotificationSummary(int $schoolId, string $today): array
    {
        $sentToday = Notification::query()
            ->where('school_id', $schoolId)
            ->whereDate('sent_at', $today)
            ->count();

        $totalUnsent = Notification::query()
            ->where('school_id', $schoolId)
            ->where('status', 'draft')
            ->count();

        return [
            'sent_today' => $sentToday,
            'unsent_drafts' => $totalUnsent,
        ];
    }

    private function getLibrarySummary(int $schoolId): array
    {
        $overdue = BookIssue::query()
            ->where('school_id', $schoolId)
            ->where('status', 'issued')
            ->whereDate('due_date', '<', Carbon::today())
            ->count();

        $issued = BookIssue::query()
            ->where('school_id', $schoolId)
            ->where('status', 'issued')
            ->count();

        return [
            'books_issued' => $issued,
            'overdue_books' => $overdue,
        ];
    }
}
