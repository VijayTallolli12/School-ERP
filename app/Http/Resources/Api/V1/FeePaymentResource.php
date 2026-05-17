<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeePaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'student_id' => $this->student_id,
            'student_name' => $this->whenLoaded('student', fn() => $this->student->full_name),
            'admission_no' => $this->whenLoaded('student', fn() => $this->student->admission_no),
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => $this->whenLoaded('academicYear', fn() => $this->academicYear?->name),
            'payment_mode' => $this->payment_mode,
            'amount' => (float) $this->amount,
            'remarks' => $this->remarks,
            'paid_on' => $this->paid_on?->format('Y-m-d'),
            'collected_by_name' => $this->whenLoaded('collector', fn() => $this->collector?->name),
            'items' => FeePaymentItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}