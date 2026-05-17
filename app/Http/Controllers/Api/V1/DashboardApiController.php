<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Tenant\SchoolContext;
use App\Models\LoginActivity;
use App\Models\User;
use App\Modules\Exams\Models\Exam;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use App\Modules\Teachers\Models\TeacherAttendance;
use App\Modules\Timetable\Services\TimetableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

class DashboardApiController extends ApiBaseController
{
    public function stats(): JsonResponse
    {
        $schoolId = app(SchoolContext::class)->id();
        $user = auth()->user();

        $data = [
            'students' => Student::query()->count(),
            'teachers' => Teacher::query()->count(),
            'active_teachers' => Teacher::query()->where('status', 'active')->count(),
            'exams' => Exam::query()->count(),
            'published_exams' => Exam::query()->where('is_published', true)->count(),
            'roles' => Role::query()->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))->count(),
            'login_today' => LoginActivity::query()->withoutGlobalScopes()->whereDate('created_at', today())->count(),
        ];

        // Fee stats (permission-gated)
        if ($user->can('fees.view')) {
            $data['fees'] = app(FeeService::class)->dashboardFeeStats();
        }

        // Timetable stats (permission-gated)
        if ($user->can('timetable.view')) {
            $timetableService = app(TimetableService::class);
            $data['timetable'] = [
                'today_schedules' => $timetableService->todaySchedulesCount(),
                'active_classes' => $timetableService->activeClassCount(),
            ];
        }

        // Teacher attendance today
        $data['teacher_attendance_today'] = TeacherAttendance::query()
            ->whereDate('attendance_date', today())
            ->count();

        return $this->success($data, 'Dashboard stats retrieved.');
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $activities = Activity::query()
            ->with('causer:id,name')
            ->latest()
            ->limit($request->integer('limit', 10))
            ->get()
            ->map(fn (Activity $a) => [
                'id' => $a->id,
                'description' => $a->description,
                'subject_type' => class_basename($a->subject_type),
                'causer_name' => $a->causer?->name,
                'created_at' => $a->created_at?->toISOString(),
            ]);

        return $this->success($activities, 'Recent activities retrieved.');
    }

    public function notifications(Request $request): JsonResponse
    {
        $notifications = app(NotificationRepositoryInterface::class)
            ->bellQuery($request->user()->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Notification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'priority' => $n->priority,
                'is_read' => (bool) ($n->pivot->is_read ?? false),
                'read_at' => $n->pivot->read_at ?? null,
                'created_at' => $n->created_at?->toISOString(),
            ]);

        return $this->success($notifications, 'Dashboard notifications retrieved.');
    }
}