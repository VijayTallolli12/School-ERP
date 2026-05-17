<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->whenLoaded('student', fn() => $this->student->full_name),
            'admission_no' => $this->whenLoaded('student', fn() => $this->student->admission_no),
            'class_section_id' => $this->class_section_id,
            'class' => $this->whenLoaded('classSection', fn() => $this->classSection?->schoolClass?->name),
            'section' => $this->whenLoaded('classSection', fn() => $this->classSection?->section?->name),
            'academic_year_id' => $this->academic_year_id,
            'attendance_date' => $this->attendance_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'remarks' => $this->remarks,
            'marked_by_name' => $this->whenLoaded('markedBy', fn() => $this->markedBy?->name),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}