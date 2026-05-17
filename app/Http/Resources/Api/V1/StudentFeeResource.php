<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFeeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->whenLoaded('items', function () {
            return $this->items->map(function ($item) {
                $paid = (float) ($item->paid_sum ?? 0);
                $balance = max(0, (float) $item->amount - $paid);

                return [
                    'id' => $item->id,
                    'fee_category_id' => $item->fee_category_id,
                    'fee_category' => $item->feeCategory?->name,
                    'amount' => (float) $item->amount,
                    'due_date' => $item->due_date?->format('Y-m-d'),
                    'paid' => $paid,
                    'balance' => $balance,
                    'status' => $balance <= 0.009 ? 'paid' : ($paid > 0 ? 'partial' : 'pending'),
                ];
            });
        });

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student_name' => $this->whenLoaded('student', fn() => $this->student->full_name),
            'admission_no' => $this->whenLoaded('student', fn() => $this->student->admission_no),
            'academic_year_id' => $this->academic_year_id,
            'academic_year' => $this->whenLoaded('academicYear', fn() => $this->academicYear?->name),
            'fee_structure_id' => $this->fee_structure_id,
            'status' => $this->status,
            'assigned_at' => $this->assigned_at?->toISOString(),
            'items' => $items,
            'total_amount' => $items ? $items->sum('amount') : null,
            'total_paid' => $items ? $items->sum('paid') : null,
            'total_balance' => $items ? $items->sum('balance') : null,
        ];
    }
}