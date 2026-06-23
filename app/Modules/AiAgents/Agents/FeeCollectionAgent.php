<?php

namespace App\Modules\AiAgents\Agents;

use App\Core\Tenant\SchoolContext;
use App\Modules\Fees\Services\FeeService;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Support\Facades\DB;

class FeeCollectionAgent implements AgentInterface
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
        private readonly FeeService $feeService,
    ) {}

    public function name(): string
    {
        return 'fee_collection';
    }

    public function description(): string
    {
        return 'Finds students with overdue fees and sends reminders via in-app notifications.';
    }

    public function permissions(): array
    {
        return ['fees.view', 'fees.collect'];
    }

    public function config(): array
    {
        return [
            'label' => 'Fee Collection Agent',
            'icon' => 'cash',
            'color' => 'primary',
            'tags' => ['Reminders', 'Notifications', 'Report'],
            'params' => [
                'days' => [
                    'label' => 'Overdue Period',
                    'type' => 'select',
                    'options' => [
                        ['value' => 30, 'label' => '30 Days'],
                        ['value' => 60, 'label' => '60 Days'],
                        ['value' => 90, 'label' => '90 Days'],
                    ],
                    'default' => 30,
                ],
            ],
        ];
    }

    public function validateParams(array $params): array
    {
        return [
            'days' => (int) ($params['days'] ?? 30),
        ];
    }

    public function preview(array $params): array
    {
        $days = $params['days'];

        $items = $this->feeService->pendingFeeItemsQuery()
            ->with([
                'studentFee.student.currentSession.classSection.schoolClass',
                'studentFee.student.currentSession.classSection.section',
                'feeCategory',
                'studentFee.student.parents.user',
            ])
            ->get();

        $grouped = [];
        $totalOutstanding = 0;

        foreach ($items as $item) {
            $student = $item->studentFee->student;
            $studentId = $student->id;

            if (!isset($grouped[$studentId])) {
                $grouped[$studentId] = [
                    'student_id' => $studentId,
                    'name' => $student->full_name,
                    'class' => $student->currentSession->first()?->classSection?->display_name ?? 'N/A',
                    'balance' => 0,
                    'items' => [],
                    'parents' => $student->parents->map(fn ($p) => [
                        'id' => $p->id,
                        'name' => $p->full_name,
                        'user_id' => $p->user_id,
                    ])->toArray(),
                ];
            }

            $balance = $item->balance;
            $grouped[$studentId]['balance'] += $balance;
            $grouped[$studentId]['items'][] = [
                'category' => $item->feeCategory?->name ?? 'N/A',
                'amount' => (float) $item->amount,
                'due_date' => $item->due_date?->format('d-m-Y'),
                'balance' => $balance,
            ];
            $totalOutstanding += $balance;
        }

        return [
            'days' => $days,
            'student_count' => count($grouped),
            'total_outstanding' => round($totalOutstanding, 2),
            'students' => array_values($grouped),
        ];
    }

    public function execute(array $params): array
    {
        $preview = $this->preview($params);
        $now = now();
        $schoolId = $this->schoolContext->id();
        $userId = auth()->id();
        $results = [];
        $notificationsCreated = 0;

        DB::beginTransaction();
        try {
            foreach ($preview['students'] as $student) {
                $reminderText = $this->generateReminder($student['name'], $student['balance']);

                $notification = Notification::create([
                    'school_id' => $schoolId,
                    'title' => "Fee Reminder - {$student['name']}",
                    'message' => $reminderText,
                    'type' => 'fee_reminder',
                    'priority' => 'high',
                    'status' => 'sent',
                    'target_type' => 'parents',
                    'channel' => 'in_app',
                    'sent_at' => $now,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                $pivotData = [];
                foreach ($student['parents'] as $parent) {
                    if ($parent['user_id']) {
                        $pivotData[$parent['user_id']] = [
                            'is_read' => false,
                            'delivery_status' => 'delivered',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if (!empty($pivotData)) {
                    $notification->users()->syncWithoutDetaching($pivotData);
                    $notificationsCreated++;
                }

                $results[] = [
                    'student_id' => $student['student_id'],
                    'name' => $student['name'],
                    'class' => $student['class'],
                    'outstanding' => $student['balance'],
                    'reminder_status' => !empty($pivotData) ? 'sent' : 'no_parents',
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'days' => $params['days'],
            'student_count' => $preview['student_count'],
            'total_outstanding' => $preview['total_outstanding'],
            'results' => $results,
            'notifications_created' => $notificationsCreated,
        ];
    }

    private function generateReminder(string $studentName, float $balance): string
    {
        return "Dear Parent,\n\nFee balance of ₹" . number_format($balance, 2) . " is pending for {$studentName}.\nPlease pay before the due date to avoid late fees.\n\nThank you,\nSchool ERP";
    }
}
