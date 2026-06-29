<?php

namespace App\Listeners;

use App\Events\AgentExecutionCompleted;
use App\Events\AttendanceMarked;
use App\Events\ExamPublished;
use App\Events\FeeReminderGenerated;
use App\Events\HomeworkAssigned;
use App\Events\TeacherAttendanceMarked;
use Illuminate\Support\Facades\Log;

class LogNotificationActivity
{
    public function handle(AttendanceMarked|HomeworkAssigned|ExamPublished|FeeReminderGenerated|AgentExecutionCompleted|TeacherAttendanceMarked $event): void
    {
        $context = match ($event::class) {
            AttendanceMarked::class => [
                'type' => 'attendance_marked',
                'student_id' => $event->studentId,
                'student_name' => $event->studentName,
                'status' => $event->status,
                'date' => $event->date,
                'marked_at' => $event->markedAt,
            ],
            TeacherAttendanceMarked::class => [
                'type' => 'teacher_attendance_marked',
                'teacher_id' => $event->teacherId,
                'teacher_name' => $event->teacherName,
                'status' => $event->status,
                'date' => $event->date,
                'marked_at' => $event->markedAt,
            ],
            HomeworkAssigned::class => [
                'type' => 'homework_assigned',
                'homework_id' => $event->homeworkId,
                'title' => $event->title,
                'class_section_id' => $event->classSectionId,
            ],
            ExamPublished::class => [
                'type' => 'exam_published',
                'exam_id' => $event->examId,
                'exam_name' => $event->examName,
                'class_section_id' => $event->classSectionId,
            ],
            FeeReminderGenerated::class => [
                'type' => 'fee_reminder_generated',
                'student_fee_id' => $event->studentFeeId,
                'student_id' => $event->studentId,
                'amount_due' => $event->amountDue,
            ],
            AgentExecutionCompleted::class => [
                'type' => 'agent_execution_completed',
                'execution_id' => $event->executionId,
                'agent_name' => $event->agentName,
                'status' => $event->status,
            ],
        };

        Log::info('Notification activity', $context);
    }
}
