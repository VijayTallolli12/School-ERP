<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverTripDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'trip' => $this['trip'],
            'route' => $this['route'],
            'vehicle' => $this['vehicle'],
            'stops' => $this['stops'],
        ];
    }
}
