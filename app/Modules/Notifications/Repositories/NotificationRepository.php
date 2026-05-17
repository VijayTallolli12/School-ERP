<?php

namespace App\Modules\Notifications\Repositories;

use App\Core\Base\BaseRepository;
use App\Modules\Notifications\Models\Notification;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    public function dataTableQuery(): Builder
    {
        return $this->query()
            ->with(['creator'])
            ->withCount([
                'users as user_count',
                'users as unread_count' => fn ($q) => $q->where('notification_user.is_read', false),
            ]);
    }

    public function markAsSent(Notification $notification): void
    {
        $notification->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(Notification $notification): void
    {
        $notification->update(['status' => 'failed']);
    }

    public function attachUsers(Notification $notification, array $userIds): void
    {
        $pivot = [];
        foreach ($userIds as $uid) {
            $pivot[$uid] = ['delivery_status' => 'pending', 'created_at' => now(), 'updated_at' => now()];
        }
        $notification->users()->syncWithoutDetaching($pivot);
    }

    public function dashboardStats(): array
    {
        $base = $this->query();

        return [
            'total_sent' => (clone $base)->where('status', 'sent')->count(),
            'pending' => (clone $base)->where('status', 'draft')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
            'unread_count' => (clone $base)->whereHas('users', fn ($q) => $q->where('notification_user.is_read', false))->count(),
        ];
    }

    public function bellQuery(int $userId): Builder
    {
        return $this->query()
            ->where('status', 'sent')
            ->whereHas('users', fn ($q) => $q->where('notification_user.user_id', $userId))
            ->with(['users' => fn ($q) => $q->where('notification_user.user_id', $userId)])
            ->latest()
            ->limit(10);
    }

    public function markUserRead(Notification $notification, int $userId): void
    {
        $notification->users()->updateExistingPivot($userId, [
            'is_read' => true,
            'read_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function markAllUserRead(int $userId): void
    {
        // Get all unread sent notification IDs for this user
        $ids = $this->query()
            ->where('status', 'sent')
            ->whereHas('users', fn ($q) => $q->where('notification_user.user_id', $userId)->where('notification_user.is_read', false))
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            \DB::table('notification_user')
                ->whereIn('notification_id', $ids)
                ->where('user_id', $userId)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }

    public function announcementBannerQuery(): Builder
    {
        return $this->query()
            ->where('type', 'announcement')
            ->where('status', 'sent')
            ->latest('sent_at');
    }
}