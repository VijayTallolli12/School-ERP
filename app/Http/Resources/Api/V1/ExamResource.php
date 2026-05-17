<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => $this->whenLoaded('academicYear', fn() => $this->academicYear?->name),
            'class_section_id' => $this->class_section_id,
            'class' => $this->whenLoaded('classSection', fn() => $this->classSection?->schoolClass?->name),
            'section' => $this->whenLoaded('classSection', fn() => $this->classSection?->section?->name),
            'subject_id' => $this->subject_id,
            'subject' => $this->whenLoaded('subject', fn() => $this->subject?->name),
            'exam_name' => $this->exam_name,
            'exam_type' => $this->exam_type,
            'exam_date' => $this->exam_date?->format('Y-m-d'),
            'maximum_marks' => $this->maximum_marks,
            'pass_marks' => $this->pass_marks,
            'status' => $this->status,
            'is_published' => $this->is_published,
        ];
    }
}