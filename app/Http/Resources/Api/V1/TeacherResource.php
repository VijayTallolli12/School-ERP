<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'qualification' => $this->qualification,
            'experience_years' => $this->experience_years,
            'joining_date' => $this->joining_date?->format('Y-m-d'),
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'photo_url' => $this->photo_path ? asset('storage/' . $this->photo_path) : null,
            'status' => $this->status,

            'subjects' => $this->whenLoaded('subjects', fn() => $this->subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])),
            'class_sections' => $this->whenLoaded('classSections', fn() => $this->classSections->map(fn($cs) => [
                'id' => $cs->id,
                'class' => $cs->schoolClass?->name,
                'section' => $cs->section?->name,
                'is_class_teacher' => (bool) ($cs->pivot->is_class_teacher ?? false),
            ])),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}