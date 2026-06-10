<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Tenant\SchoolContext;
use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use App\Modules\Calendar\Models\AcademicCalendar;
use App\Modules\Documents\Services\DocumentService;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Exams\Models\Exam;
use App\Modules\Timetable\Services\TimetableService;
use App\Modules\Reports\Services\AbsentStudentReportService;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $schoolId = app(SchoolContext::class)->id();
        $user = auth()->user();

        $feeStats = null;
        if ($user?->can('fees.view')) {
            $feeStats = app(FeeService::class)->dashboardFeeStats();
        }

        $timetableStats = null;
        if ($user?->can('timetable.view')) {
            $timetableService = app(TimetableService::class);
            $timetableStats = [
                'today_schedules' => $timetableService->todaySchedulesCount(),
                'active_classes' => $timetableService->activeClassCount(),
            ];
        }

        $upcomingEvents = null;
        if ($user?->can('academic_calendar.view')) {
            $upcomingEvents = AcademicCalendar::published()
                ->upcoming(6)
                ->get(['id', 'title', 'event_type', 'start_date', 'end_date', 'location']);
        }

        $documentStats = null;
        if ($user?->can('student_documents.view')) {
            $docService = app(DocumentService::class);
            $documentStats = [
                'expiring' => $docService->getExpiringDocuments(30, 6),
                'recent' => $docService->getRecentDocuments(6),
                'pending_count' => $docService->getPendingCount(),
                'expiring_count' => $docService->getExpiringCount(30),
            ];
        }

        $absentToday = null;
        if ($user?->can('attendance.view')) {
            $absentService = app(AbsentStudentReportService::class);
            $absentToday = $absentService->getTodayAbsentCount($schoolId);
        }

        // Batch stats in a single query using UNION-style approach
        // Use separate count queries but cache the redundant ones
        $stats = [];

        // Users count
        $stats['users'] = User::query()
            ->when($schoolId, fn ($query) => $query->whereHas('schools', fn ($schools) => $schools->whereKey($schoolId)))
            ->count();

        // Student and teacher counts (reuse teacher count for active)
        $stats['students'] = Student::query()->count();

        $teacherCounts = Teacher::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active', ['active'])
            ->first();
        $stats['teachers'] = (int) ($teacherCounts->total ?? 0);
        $stats['active_teachers'] = (int) ($teacherCounts->active ?? 0);

        // Exam counts (reuse total for published)
        $examCounts = Exam::query()
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published')
            ->first();
        $stats['exams'] = (int) ($examCounts->total ?? 0);
        $stats['published_exams'] = (int) ($examCounts->published ?? 0);

        $stats['active_classes'] = $timetableStats['active_classes'] ?? 0;
        $stats['today_schedules'] = $timetableStats['today_schedules'] ?? 0;

        $stats['roles'] = Role::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->count();

        $stats['login_today'] = LoginActivity::query()
            ->withoutGlobalScopes()
            ->whereDate('created_at', today())
            ->count();

        $stats['activities'] = Activity::query()->count();

        return view('modules.dashboard.index', [
            'school' => app(SchoolContext::class)->school(),
            'absentToday' => $absentToday,
            'stats' => $stats,
            'teacherAttendanceSummary' => [
                'today' => TeacherAttendance::query()->whereDate('attendance_date', today())->count(),
            ],
            'feeStats' => $feeStats,
            'upcomingEvents' => $upcomingEvents,
            'documentStats' => $documentStats,
            'recentLogins' => LoginActivity::query()
                ->withoutGlobalScopes()
                ->with('user')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
