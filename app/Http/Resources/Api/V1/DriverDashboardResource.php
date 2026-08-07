<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'summary' => $this['summary'],
            'vehicle' => $this['vehicle'],
            'routes' => $this['routes'],
            'route_stops_count' => $this['route_stops_count'],
            'today_trips' => $this['today_trips'],
        ];
    }
}
