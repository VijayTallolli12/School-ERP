<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pivot = $this->whenPivotLoaded('notification_user', fn() => [
            'is_read' => (bool) ($this->pivot->is_read ?? false),
            'read_at' => $this->pivot->read_at ?? null,
            'delivery_status' => $this->pivot->delivery_status ?? null,
        ]);

        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->message,
            'message' => $this->message,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'priority' => $this->priority,
            'status' => $this->status,
            'target_type' => $this->target_type,
            'channel' => $this->channel,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];

        if ($pivot) {
            $data['is_read'] = $pivot['is_read'];
            $data['read_at'] = $pivot['read_at'];
            $data['delivery_status'] = $pivot['delivery_status'];
        }

        return $data;
    }
}