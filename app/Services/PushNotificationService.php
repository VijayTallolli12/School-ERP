<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private ?string $serverKey;
    private bool $enabled;

    public function __construct()
    {
        $this->serverKey = config('services.fcm.server_key');
        $this->enabled = config('services.fcm.enabled', false);
    }

    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $tokens = UserDevice::query()
            ->where('user_id', $userId)
            ->whereNotNull('device_token')
            ->pluck('device_token')
            ->all();

        if (empty($tokens)) {
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): bool
    {
        if (empty($userIds)) {
            return false;
        }

        $tokens = UserDevice::query()
            ->whereIn('user_id', $userIds)
            ->whereNotNull('device_token')
            ->pluck('device_token')
            ->all();

        if (empty($tokens)) {
            return false;
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (! $this->enabled || ! $this->serverKey) {
            return false;
        }

        $payload = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        try {
            $response = Http::timeout(config('services.fcm.timeout', 10))
                ->withHeaders([
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://fcm.googleapis.com/fcm/send', $payload);

            $success = $response->successful();

            if (! $success) {
                Log::warning('FCM topic send failed', [
                    'topic' => $topic,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::error('FCM topic send exception', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function sendToTokens(array $tokens, string $title, string $body, array $data = []): bool
    {
        if (! $this->enabled || ! $this->serverKey || empty($tokens)) {
            return false;
        }

        $payload = [
            'registration_ids' => array_values($tokens),
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
            'data' => $data,
        ];

        try {
            $response = Http::timeout(config('services.fcm.timeout', 10))
                ->withHeaders([
                    'Authorization' => 'key=' . $this->serverKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://fcm.googleapis.com/fcm/send', $payload);

            $success = $response->successful();

            if (! $success) {
                Log::warning('FCM send failed', [
                    'token_count' => count($tokens),
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            Log::error('FCM send exception', [
                'token_count' => count($tokens),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
