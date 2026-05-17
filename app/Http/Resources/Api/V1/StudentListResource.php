<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentListResource extends JsonResource
{
    /**
     * Minimal student representation for list endpoints.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $session = $this->relationLoaded('sessions')
            ? $this->sessions->where('status', 'active')->first()
            : null;

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'admission_no' => $this->admission_no,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'status' => $this->status,
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'class' => $session?->classSection?->schoolClass?->name,
            'section' => $session?->classSection?->section?->name,
            'roll_no' => $session?->roll_no,
        ];
    }
}