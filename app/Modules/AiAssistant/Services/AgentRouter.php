<?php

namespace App\Modules\AiAssistant\Services;

/**
 * Routes action intents (destructive operations) to their executor.
 *
 * Query tools are resolved through the ErpToolRegistry; this router only
 * maps actions that require confirmation (payroll.generate, attendance.notify,
 * fee.send_reminders, exam.publish, notification.send, homework.create,
 * transport.assign).
 */
class AgentRouter
{
    private const DESTRUCTIVE_INTENTS = [
        'payroll.generate',
        'attendance.notify',
        'fee.send_reminders',
        'exam.publish',
        'notification.send',
        'homework.create',
        'transport.assign',
    ];

    private const ROUTES = [
        'payroll.generate' => [
            'type' => 'agent',
            'agent' => 'payroll',
            'label' => 'Payroll Agent',
            'params' => ['month', 'year'],
        ],
        'attendance.notify' => [
            'type' => 'agent',
            'agent' => 'attendance',
            'label' => 'Attendance Agent',
            'params' => ['date'],
        ],
        'fee.send_reminders' => [
            'type' => 'agent',
            'agent' => 'fee_collection',
            'label' => 'Fee Collection Agent',
            'params' => ['days'],
        ],
        'exam.publish' => [
            'type' => 'service',
            'service' => 'exam',
            'method' => 'publish',
            'params' => ['exam_id'],
        ],
        'notification.send' => [
            'type' => 'service',
            'service' => 'notification',
            'method' => 'create',
            'params' => ['title', 'message', 'target_type'],
        ],
        'homework.create' => [
            'type' => 'service',
            'service' => 'homework',
            'method' => 'create',
            'params' => ['class_section_id', 'subject_id', 'title', 'due_date'],
        ],
        'transport.assign' => [
            'type' => 'service',
            'service' => 'transport',
            'method' => 'createAssignment',
            'params' => ['route_id', 'student_ids'],
        ],
    ];

    public function route(string $intent): ?array
    {
        if (!array_key_exists($intent, self::ROUTES)) {
            return null;
        }

        $route = self::ROUTES[$intent];
        $route['confirmation_required'] = in_array($intent, self::DESTRUCTIVE_INTENTS, true);

        return $route;
    }

    public function isDestructive(string $intent): bool
    {
        return in_array($intent, self::DESTRUCTIVE_INTENTS, true);
    }

    public static function getSupportedIntents(): array
    {
        $intents = [];
        foreach (self::ROUTES as $key => $route) {
            $intents[$key] = [
                'type' => $route['type'],
                'destructive' => in_array($key, self::DESTRUCTIVE_INTENTS, true),
            ];
        }
        return $intents;
    }
}
