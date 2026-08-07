<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'location' => $this['location'],
        ];
    }
}
