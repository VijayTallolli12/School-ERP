<?php

namespace App\Listeners;

use App\Events\AgentExecutionCompleted;
use App\Events\AttendanceMarked;
use App\Events\ExamPublished;
use App\Events\FeeReminderGenerated;
use App\Events\HomeworkAssigned;
use App\Events\TeacherAttendanceMarked;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class SendPushNotification
{
    public function __construct(
        private readonly PushNotificationService $pushService,
    ) {}

    public function handle(AttendanceMarked|HomeworkAssigned|ExamPublished|FeeReminderGenerated|AgentExecutionCompleted|TeacherAttendanceMarked $event): void
    {
        try {
            [$title, $body, $userIds] = match ($event::class) {
                AttendanceMarked::class => [
                    'Attendance Updated',
                    $this->formatAttendanceMessage($event),
                    $this->resolveStudentAndParentUserIds($event->studentId),
                ],
                TeacherAttendanceMarked::class => [
                    'Attendance Updated',
                    $this->formatTeacherAttendanceMessage($event),
                    $this->resolveTeacherUserIds($event->teacherId),
                ],
                HomeworkAssigned::class => [
                    'New Homework',
                    $event->title,
                    $this->resolveClassStudentUserIds($event->classSectionId),
                ],
                ExamPublished::class => [
                    'Exam Published',
                    $event->examName,
                    $this->resolveClassStudentUserIds($event->classSectionId),
                ],
                FeeReminderGenerated::class => [
                    'Fee Reminder',
                    "Fee of {$event->amountDue} is due.",
                    [$event->parentUserId],
                ],
                AgentExecutionCompleted::class => [
                    'Agent Completed',
                    "{$event->agentName}: {$event->status}",
                    [],
                ],
            };

            if (empty($userIds)) {
                return;
            }

            $this->pushService->sendToUsers($userIds, $title, $body);
        } catch (\Throwable $e) {
            Log::error('Failed to send push notification', [
                'event' => $event::class,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function formatAttendanceMessage(AttendanceMarked $event): string
    {
        $name = $event->studentName ?: "Student #{$event->studentId}";
        $time = $event->markedAt ?: now()->format('h:i A');
        return "{$name} marked " . strtoupper($event->status) . " at {$time}.";
    }

    private function formatTeacherAttendanceMessage(TeacherAttendanceMarked $event): string
    {
        $name = $event->teacherName ?: "Teacher #{$event->teacherId}";
        $time = $event->markedAt ?: now()->format('h:i A');
        return "{$name} marked " . strtoupper($event->status) . " at {$time}.";
    }

    private function resolveStudentAndParentUserIds(int $studentId): array
    {
        $student = \App\Modules\Students\Models\Student::with('parents.user')->find($studentId);
        if (! $student) {
            return [];
        }

        $userIds = [];

        if ($student->user_id) {
            $userIds[] = $student->user_id;
        }

        foreach ($student->parents as $guardian) {
            if ($guardian->user_id) {
                $userIds[] = $guardian->user_id;
            }
        }

        return array_values(array_unique($userIds));
    }

    private function resolveTeacherUserIds(int $teacherId): array
    {
        return User::query()
            ->whereHas('teacher', fn ($q) => $q->where('id', $teacherId))
            ->pluck('id')
            ->all();
    }

    private function resolveClassStudentUserIds(int $classSectionId): array
    {
        return User::query()
            ->whereHas('student.sessions', fn ($q) => $q
                ->where('class_section_id', $classSectionId)
                ->where('status', 'active')
            )
            ->pluck('id')
            ->all();
    }
}
