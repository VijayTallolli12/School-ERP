<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverTripStudentStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'trip_student' => $this['trip_student'],
        ];
    }
}
