<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\UserDevice;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends ApiBaseController
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_type' => ['nullable', 'string', 'max:50'],
            'platform' => ['nullable', 'string', 'max:50'],
            'device_token' => ['required', 'string'],
        ]);

        $device = UserDevice::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'device_token' => $validated['device_token'],
            ],
            [
                'device_type' => $validated['device_type'] ?? null,
                'platform' => $validated['platform'] ?? null,
                'last_seen_at' => now(),
            ]
        );

        return $this->success([
            'device' => [
                'id' => $device->id,
                'device_type' => $device->device_type,
                'platform' => $device->platform,
                'last_seen_at' => $device->last_seen_at?->toISOString(),
            ],
        ], 'Device registered successfully.');
    }

    public function unregister(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_token' => ['required', 'string'],
        ]);

        $deleted = UserDevice::query()
            ->where('user_id', $request->user()->id)
            ->where('device_token', $validated['device_token'])
            ->delete();

        if ($deleted === 0) {
            return $this->notFound('Device not found.');
        }

        return $this->success(message: 'Device unregistered successfully.');
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $bellData = $this->notificationService->bellData($userId);

        return $this->success([
            'unread_count' => $bellData['unread_count'],
        ], 'Unread count retrieved.');
    }
}
