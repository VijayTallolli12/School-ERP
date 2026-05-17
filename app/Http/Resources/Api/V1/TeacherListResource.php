<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'employee_id' => $this->employee_id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'qualification' => $this->qualification,
            'status' => $this->status,
        ];
    }
}