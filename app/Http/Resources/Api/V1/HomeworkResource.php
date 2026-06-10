<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_name' => $this->whenLoaded('subject', fn () => $this->subject?->name),
            'title' => $this->title,
            'description' => $this->description,
            'assigned_date' => $this->assigned_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'attachment_url' => $this->attachment_url,
            'status' => $this->status,
        ];
    }
}
