<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverEtaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'trip_id' => $this['trip_id'],
            'current_location' => $this['current_location'],
            'eta' => $this['eta'],
        ];
    }
}
