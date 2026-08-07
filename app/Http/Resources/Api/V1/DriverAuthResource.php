<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverAuthResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'token' => $this['token'],
            'token_type' => $this['token_type'],
            'user' => $this['user'],
            'school_id' => $this['school_id'],
            'driver' => $this['driver'],
        ];
    }
}
