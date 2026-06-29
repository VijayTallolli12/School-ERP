<?php

namespace App\Modules\AiAgents\Agents;

use App\Core\Tenant\SchoolContext;
use App\Modules\Library\Models\BookIssue;
use App\Modules\Library\Models\FineSetting;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Students\Models\Student;
use App\Modules\Teachers\Models\Teacher;
use Illuminate\Support\Facades\DB;

class LibraryAgent implements AgentInterface
{
    public function __construct(
        private readonly SchoolContext $schoolContext,
    ) {}

    public function name(): string
    {
        return 'library';
    }

    public function description(): string
    {
        return 'Finds overdue books, identifies borrowers, calculates fines, notifies parents/teachers, and generates an overdue report.';
    }

    public function permissions(): array
    {
        return ['library.view', 'library.create'];
    }

    public function config(): array
    {
        return [
            'label' => 'Library Agent',
            'icon' => 'book',
            'color' => 'warning',
            'tags' => ['Overdue', 'Notifications', 'Report'],
            'params' => [
                'days' => [
                    'label' => 'Minimum Overdue Days',
                    'type' => 'select',
                    'options' => [
                        ['value' => 1, 'label' => '1 Day'],
                        ['value' => 7, 'label' => '7 Days'],
                        ['value' => 14, 'label' => '14 Days'],
                        ['value' => 30, 'label' => '30 Days'],
                    ],
                    'default' => 1,
                ],
            ],
        ];
    }

    public function validateParams(array $params): array
    {
        return [
            'days' => (int) ($params['days'] ?? 1),
        ];
    }

    public function preview(array $params): array
    {
        $days = $params['days'];
        $schoolId = $this->schoolContext->id();
        $now = now();

        $issues = $this->loadOverdueIssues($schoolId, $days);

        $overdueItems = [];
        $totalFineAmount = 0;

        foreach ($issues as $issue) {
            $borrower = $issue->issueable;
            $borrowerName = $borrower?->full_name ?? 'Unknown';

            $totalFineAmount += $issue->_calculated_fine;

            $overdueItems[] = [
                'issue_id' => $issue->id,
                'book_id' => $issue->book_id,
                'book_title' => $issue->book?->title ?? 'Unknown',
                'book_isbn' => $issue->book?->isbn ?? '',
                'borrower_id' => $borrower?->id,
                'borrower_name' => $borrowerName,
                'borrower_type' => $issue->issueable_type === Student::class ? 'student' : 'teacher',
                'issue_date' => $issue->issue_date?->format('Y-m-d'),
                'due_date' => $issue->due_date?->format('Y-m-d'),
                'days_overdue' => $issue->_overdue_days,
                'fine_amount' => $issue->_calculated_fine,
            ];
        }

        return [
            'days' => $days,
            'total_overdue_books' => count($overdueItems),
            'total_borrowers' => collect($overdueItems)->pluck('borrower_id')->unique()->count(),
            'total_fine_amount' => round($totalFineAmount, 2),
            'items' => $overdueItems,
        ];
    }

    public function execute(array $params): array
    {
        $days = $params['days'];
        $schoolId = $this->schoolContext->id();
        $now = now();
        $userId = auth()->id();

        $issues = $this->loadOverdueIssues($schoolId, $days);

        $results = [];
        $notificationsCreated = 0;

        DB::beginTransaction();
        try {
            foreach ($issues as $issue) {
                $borrower = $issue->issueable;
                $borrowerName = $borrower?->full_name ?? 'Unknown';
                $borrowerType = $issue->issueable_type === Student::class ? 'student' : 'teacher';

                $borrowerRecipients = $this->resolveRecipients($issue);

                $notificationMessage = $this->generateOverdueMessage(
                    $borrowerName,
                    $issue->book?->title ?? 'Unknown',
                    $issue->_overdue_days,
                    $issue->_calculated_fine
                );

                $notification = Notification::create([
                    'school_id' => $schoolId,
                    'title' => "Book Overdue - {$issue->book?->title}",
                    'message' => $notificationMessage,
                    'type' => 'overdue_alert',
                    'priority' => 'high',
                    'status' => 'sent',
                    'target_type' => $borrowerType === 'student' ? 'parents' : 'teachers',
                    'channel' => 'in_app',
                    'sent_at' => $now,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                $pivotData = [];
                foreach ($borrowerRecipients as $uid) {
                    if ($uid) {
                        $pivotData[$uid] = [
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
                    'issue_id' => $issue->id,
                    'book_title' => $issue->book?->title ?? 'Unknown',
                    'borrower_name' => $borrowerName,
                    'borrower_type' => $borrowerType,
                    'days_overdue' => $issue->_overdue_days,
                    'fine_amount' => $issue->_calculated_fine,
                    'notification_status' => !empty($pivotData) ? 'sent' : 'no_recipient',
                ];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'days' => $params['days'],
            'total_overdue_books' => count($results),
            'total_borrowers' => collect($results)->pluck('borrower_name')->unique()->count(),
            'total_fine_amount' => round(collect($results)->sum('fine_amount'), 2),
            'results' => $results,
            'notifications_created' => $notificationsCreated,
            'records_processed' => count($results),
        ];
    }

    private function loadOverdueIssues(int $schoolId, int $minDays): iterable
    {
        $now = now();

        $fineSetting = FineSetting::query()
            ->where('school_id', $schoolId)
            ->where('status', 'active')
            ->first();

        $finePerDay = (float) ($fineSetting?->fine_per_day ?? 1.00);
        $maxFine = $fineSetting?->max_fine !== null ? (float) $fineSetting->max_fine : null;
        $graceDays = (int) ($fineSetting?->grace_period_days ?? 0);

        $issues = BookIssue::query()
            ->where('school_id', $schoolId)
            ->where('status', 'issued')
            ->where('due_date', '<', $now)
            ->with(['book', 'issueable'])
            ->get()
            ->filter(function ($issue) use ($now, $minDays, $graceDays, $finePerDay, $maxFine) {
                $overdueDays = $issue->due_date->diffInDays($now, false);

                if ($overdueDays < $minDays) {
                    return false;
                }

                $fine = 0;
                if ($overdueDays > $graceDays) {
                    $fine = ($overdueDays - $graceDays) * $finePerDay;
                    if ($maxFine !== null) {
                        $fine = min($fine, $maxFine);
                    }
                    $fine = round($fine, 2);
                }

                $issue->_overdue_days = $overdueDays;
                $issue->_calculated_fine = $fine;

                return true;
            });

        return $issues;
    }

    private function resolveRecipients(BookIssue $issue): array
    {
        $borrower = $issue->issueable;
        $recipients = [];

        if ($issue->issueable_type === Student::class && $borrower) {
            $parents = $borrower->parents()->with('user')->get();
            foreach ($parents as $parent) {
                if ($parent->user_id) {
                    $recipients[] = $parent->user_id;
                }
            }
        } elseif ($issue->issueable_type === Teacher::class && $borrower) {
            if ($borrower->user_id) {
                $recipients[] = $borrower->user_id;
            }
        }

        return array_unique(array_filter($recipients));
    }

    private function generateOverdueMessage(string $borrowerName, string $bookTitle, int $daysOverdue, float $fine): string
    {
        return "Dear {$borrowerName},\n\nThe book \"{$bookTitle}\" is overdue by {$daysOverdue} days.\nPlease return the book at the earliest.\nFine accrued: ₹" . number_format($fine, 2) . ".\n\nThank you,\nSchool ERP";
    }
}
