<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentGuardianResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'relation' => $this->relation,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'occupation' => $this->occupation,
            'is_primary' => (bool) $this->is_primary,
            'can_pickup' => (bool) $this->can_pickup,
        ];
    }
}