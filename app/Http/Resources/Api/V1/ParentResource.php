<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'occupation' => $this->occupation,
            'address' => $this->address,
            'status' => $this->status,
            'user_id' => $this->user_id,

            'students' => $this->whenLoaded('students', function () {
                return $this->students->map(fn($student) => [
                    'id' => $student->id,
                    'full_name' => $student->full_name,
                    'admission_no' => $student->admission_no,
                    'relationship' => $student->pivot->relationship ?? null,
                    'is_primary' => (bool) ($student->pivot->is_primary ?? false),
                ]);
            }),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}