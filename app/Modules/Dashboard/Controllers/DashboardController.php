<?php

namespace App\Modules\Dashboard\Controllers;

use App\Core\Tenant\SchoolContext;
use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use App\Models\User;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Exams\Models\Exam;
use App\Modules\Timetable\Services\TimetableService;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $schoolId = app(SchoolContext::class)->id();

        $feeStats = null;
        if (auth()->user()?->can('fees.view')) {
            $feeStats = app(FeeService::class)->dashboardFeeStats();
        }

        $timetableStats = null;
        if (auth()->user()?->can('timetable.view')) {
            $timetableService = app(TimetableService::class);
            $timetableStats = [
                'today_schedules' => $timetableService->todaySchedulesCount(),
                'active_classes' => $timetableService->activeClassCount(),
            ];
        }

        return view('modules.dashboard.index', [
            'school' => app(SchoolContext::class)->school(),
            'stats' => [
                'users' => User::query()->when($schoolId, fn ($query) => $query->whereHas('schools', fn ($schools) => $schools->whereKey($schoolId)))->count(),
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'active_teachers' => Teacher::query()->where('status', 'active')->count(),
                'exams' => Exam::query()->count(),
                'published_exams' => Exam::query()->where('is_published', true)->count(),
                'active_classes' => $timetableStats['active_classes'] ?? 0,
                'today_schedules' => $timetableStats['today_schedules'] ?? 0,
                'roles' => Role::query()->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))->count(),
                'login_today' => LoginActivity::query()->withoutGlobalScopes()->whereDate('created_at', today())->count(),
                'activities' => Activity::query()->count(),
            ],
            'teacherAttendanceSummary' => [
                'today' => TeacherAttendance::query()->whereDate('attendance_date', today())->count(),
            ],
            'feeStats' => $feeStats,
            'recentLogins' => LoginActivity::query()
                ->withoutGlobalScopes()
                ->with('user')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
