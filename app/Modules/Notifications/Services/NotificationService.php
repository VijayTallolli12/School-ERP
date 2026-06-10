<?php

namespace App\Modules\Notifications\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\User;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly SchoolContext $schoolContext,
    ) {}

    public function create(array $data): Notification
    {
        $data['school_id'] = $this->schoolContext->id();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        $notification = $this->notifications->create($data);

        if ($data['status'] === 'sent') {
            $this->dispatch($notification);
        }

        return $notification;
    }

    public function update(Notification $notification, array $data): Notification
    {
        $data['updated_by'] = auth()->id();

        $wasStatusChanged = $notification->status !== $data['status'];

        $notification = $this->notifications->update($notification, $data);

        if ($wasStatusChanged && $data['status'] === 'sent') {
            $this->dispatch($notification);
        }

        return $notification;
    }

    public function delete(Notification $notification): void
    {
        $notification->users()->detach();
        $notification->delete();
    }

    public function send(Notification $notification): void
    {
        if ($notification->status === 'sent') {
            return;
        }

        $this->dispatch($notification);
        $this->notifications->markAsSent($notification);
    }

    public function dashboardStats(): array
    {
        return $this->notifications->dashboardStats();
    }

    public function bellData(int $userId): array
    {
        $notifications = $this->notifications->bellQuery($userId)->get();

        $unreadCount = $notifications->filter(fn ($n) => ! ($n->users->first()?->pivot->is_read ?? true))->count();

        $items = $notifications->map(function (Notification $notification) {
            $pivot = $notification->users->first()?->pivot;

            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => \Illuminate\Support\Str::limit($notification->message, 80),
                'type' => $notification->type,
                'type_label' => $notification->type_label,
                'priority' => $notification->priority,
                'is_read' => (bool) ($pivot?->is_read ?? false),
                'sent_at' => $notification->sent_at?->diffForHumans(),
                'read_at' => $pivot?->read_at,
            ];
        });

        return [
            'unread_count' => $unreadCount,
            'notifications' => $items,
        ];
    }

    public function markRead(Notification $notification, int $userId): void
    {
        $this->notifications->markUserRead($notification, $userId);
    }

    public function markAllRead(int $userId): void
    {
        $this->notifications->markAllUserRead($userId);
    }

    public function announcementBanner(): ?Notification
    {
        return $this->notifications->announcementBannerQuery()->first();
    }

    private function dispatch(Notification $notification): void
    {
        $userIds = $this->resolveTargetUserIds($notification->target_type);

        if (empty($userIds)) {
            // No users matched; mark as sent anyway with zero recipients
            $notification->update(['sent_at' => now()]);

            return;
        }

        $this->notifications->attachUsers($notification, $userIds);

        // Placeholder for actual email/SMS delivery — mark as delivered for in-app channel
        $now = now();
        $pivotData = [];
        foreach ($userIds as $uid) {
            $pivotData[$uid] = [
                'delivery_status' => 'delivered',
                'updated_at' => $now,
            ];
        }
        $notification->users()->syncWithoutDetaching($pivotData);

        $notification->update(['sent_at' => $now]);
    }

    private function resolveTargetUserIds(string $targetType): array
    {
        $schoolId = $this->schoolContext->id();
        $query = User::query()->whereHas('schools', fn ($q) => $q->whereKey($schoolId));

        return match ($targetType) {
            'all' => $query->pluck('id')->all(),
            'students' => $query->whereHas('roles', fn ($q) => $q->where('name', 'Student'))->pluck('id')->all(),
            'parents' => $query->whereHas('roles', fn ($q) => $q->where('name', 'Parent'))->pluck('id')->all(),
            'teachers' => $query->whereHas('roles', fn ($q) => $q->where('name', 'Teacher'))->pluck('id')->all(),
            'staff' => $query->whereHas('roles', fn ($q) => $q->where('name', 'Staff'))->pluck('id')->all(),
            'admins' => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin']))->pluck('id')->all(),
            default => [],
        };
    }
}