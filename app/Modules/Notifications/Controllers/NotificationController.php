<?php

namespace App\Modules\Notifications\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Repositories\NotificationRepositoryInterface;
use App\Modules\Notifications\Requests\StoreNotificationRequest;
use App\Modules\Notifications\Requests\UpdateNotificationRequest;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly NotificationService $service,
    ) {}

    public function index(): View
    {
        return view('modules.notifications.index', [
            'types' => Notification::types(),
            'priorities' => Notification::priorities(),
            'targetTypes' => Notification::targetTypes(),
            'channels' => Notification::channels(),
            'statuses' => Notification::statuses(),
        ]);
    }

    public function dashboard(): View
    {
        return view('modules.notifications.dashboard', [
            'stats' => $this->service->dashboardStats(),
        ]);
    }

    public function data(): JsonResponse
    {
        return DataTables::of($this->notifications->dataTableQuery(request()->only(['type', 'status'])))
            ->addColumn('type_label', fn (Notification $notification) => e($notification->type_label))
            ->addColumn('priority_badge', fn (Notification $notification) => $notification->priority_badge)
            ->addColumn('status_badge', fn (Notification $notification) => $notification->status_badge)
            ->addColumn('target_label', fn (Notification $notification) => e($notification->target_label))
            ->addColumn('channel_label', fn (Notification $notification) => e($notification->channel_label))
            ->addColumn('user_count', fn (Notification $notification) => $notification->user_count ?? 0)
            ->addColumn('unread_count', fn (Notification $notification) => $notification->unread_count ?? 0)
            ->addColumn('created_by_name', fn (Notification $notification) => e($notification->creator?->name ?? '-'))
            ->addColumn('actions', fn (Notification $notification) => view('modules.notifications._actions', compact('notification'))->render())
            ->rawColumns(['priority_badge', 'status_badge', 'actions'])
            ->toJson();
    }

    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $notification = $this->service->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Notification created successfully.',
            'data' => $notification,
        ]);
    }

    public function show(Notification $notification): JsonResponse
    {
        $notification->load(['creator', 'users'])->loadCount(['users as unread_count' => fn ($q) => $q->where('notification_user.is_read', false)]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'priority' => $notification->priority,
                'status' => $notification->status,
                'target_type' => $notification->target_type,
                'channel' => $notification->channel,
                'scheduled_at' => $notification->scheduled_at?->format('Y-m-d\TH:i'),
                'sent_at' => $notification->sent_at?->toDateTimeString(),
                'user_count' => $notification->users->count(),
                'unread_count' => $notification->unread_count,
            ],
        ]);
    }

    public function update(UpdateNotificationRequest $request, Notification $notification): JsonResponse
    {
        $notification = $this->service->update($notification, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Notification updated successfully.',
            'data' => $notification,
        ]);
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $this->service->delete($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully.',
        ]);
    }

    public function send(Notification $notification): JsonResponse
    {
        $this->authorize('send', $notification);

        $this->service->send($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully to '.$notification->fresh()->users->count().' recipients.',
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->dashboardStats(),
        ]);
    }

    public function bell(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->bellData(auth()->id()),
        ]);
    }

    public function markRead(Notification $notification): JsonResponse
    {
        $this->service->markRead($notification, auth()->id());

        return response()->json([
            'success' => true,
            'unread_count' => $this->service->bellData(auth()->id())['unread_count'],
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $this->service->markAllRead(auth()->id());

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }
}