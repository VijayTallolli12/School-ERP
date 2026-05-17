<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_id' => $this->exam_id,
            'exam_name' => $this->whenLoaded('exam', fn() => $this->exam?->exam_name),
            'exam_type' => $this->whenLoaded('exam', fn() => $this->exam?->exam_type),
            'exam_date' => $this->whenLoaded('exam', fn() => $this->exam?->exam_date?->format('Y-m-d')),
            'subject' => $this->whenLoaded('exam.subject', fn() => $this->exam?->subject?->name),
            'maximum_marks' => $this->whenLoaded('exam', fn() => $this->exam?->maximum_marks),
            'pass_marks' => $this->whenLoaded('exam', fn() => $this->exam?->pass_marks),
            'student_id' => $this->student_id,
            'student_name' => $this->whenLoaded('student', fn() => $this->student->full_name),
            'admission_no' => $this->whenLoaded('student', fn() => $this->student->admission_no),
            'marks_obtained' => $this->marks_obtained,
            'grade' => $this->grade,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'percentage' => $this->whenLoaded('exam', function () {
                if (!$this->exam) return null;
                return $this->exam->maximum_marks > 0
                    ? round(($this->marks_obtained / $this->exam->maximum_marks) * 100, 2)
                    : null;
            }),
        ];
    }
}