<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverTripStudentsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pickup_order' => $this['pickup_order'],
            'drop_order' => $this['drop_order'],
        ];
    }
}
