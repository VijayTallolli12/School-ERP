<?php

namespace Database\Seeders;

use App\Modules\Notifications\Models\Notification;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Seed a long, multi-line test notification so the mobile app's
     * notification detail screen can be verified end-to-end.
     *
     * The list endpoint previews the message with Str::limit(..., 80);
     * the detail endpoint returns the full message. This seeder is
     * idempotent (firstOrCreate on the stable title).
     */
    public function run(): void
    {
        $message = implode("\n", [
            'Dear Student,',
            '',
            'This is a sample long multi-line notification used to verify that the mobile app detail screen renders the complete message instead of the 80-character list preview.',
            '',
            'Points to note:',
            '1. The notifications list screen shows a short preview (max 80 characters).',
            '2. The notification detail screen fetches the full message from the backend, so nothing is cut off.',
            '3. Multi-line text, numbers and special characters like 100% & #tag are preserved end to end.',
            '',
            'Regards,',
            'School ERP Team',
        ]);

        $notification = Notification::query()->firstOrCreate(
            ['title' => 'Long Multi-line Test Notification'],
            [
                'school_id' => 1,
                'message' => $message,
                'type' => 'announcement',
                'priority' => 'high',
                'target_type' => 'students',
                'status' => 'sent',
                'created_by' => 1,
                'sent_at' => now(),
            ],
        );

        // Attach the notification to every student user (idempotent).
        // Resolve via the students table (user_id) rather than spatie roles,
        // which are team-scoped and not resolved in a bare seeder context.
        $studentUserIds = Student::query()
            ->where('school_id', 1)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->all();

        $notification->users()->syncWithoutDetaching(
            collect($studentUserIds)->mapWithKeys(fn ($uid) => [
                $uid => [
                    'delivery_status' => 'delivered',
                    'updated_at' => now(),
                ],
            ])->all(),
        );
    }
}
