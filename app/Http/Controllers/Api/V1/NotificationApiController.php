<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\NotificationResource;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends ApiBaseController
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepo,
        private readonly NotificationService $notificationService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'sometimes|nullable|in:' . implode(',', array_keys(Notification::types())),
            'priority' => 'sometimes|nullable|in:' . implode(',', array_keys(Notification::priorities())),
            'status' => 'sometimes|nullable|in:' . implode(',', array_keys(Notification::statuses())),
            'per_page' => 'sometimes|integer|min:5|max:100',
        ]);

        $user = $request->user();
        $query = $user->notifications()->with('creator:id,name');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $paginator = $query->orderByDesc('id')->paginate($request->integer('per_page', 15));

        return $this->paginated(
            paginator: $paginator->through(fn (Notification $n) => new NotificationResource($n)),
            message: 'Notifications retrieved.'
        );
    }

    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->wherePivot('is_read', false)
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return $this->success([
            'unread_count' => $notifications->count(),
            'notifications' => NotificationResource::collection($notifications),
        ], 'Unread notifications retrieved.');
    }

    public function markRead(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $notification = $user->notifications()->where('notifications.id', $id)->first();

        if (! $notification) {
            return $this->notFound('Notification not found.');
        }

        $this->notificationService->markRead($notification, $user->id);

        return $this->success([
            'notification' => new NotificationResource($notification->fresh()),
        ], 'Notification marked as read.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllRead($request->user()->id);

        return $this->success(message: 'All notifications marked as read.');
    }

    public function announcements(Request $request): JsonResponse
    {
        $banner = $this->notificationRepo->announcementBannerQuery()->first();

        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $announcements = Notification::query()
            ->where('type', 'announcement')
            ->where('status', 'sent')
            ->with('creator:id,name')
            ->orderByDesc('id')
            ->limit($request->integer('limit', 10))
            ->get();

        return $this->success([
            'banner' => $banner ? new NotificationResource($banner) : null,
            'announcements' => NotificationResource::collection($announcements),
        ], 'Announcements retrieved.');
    }
}