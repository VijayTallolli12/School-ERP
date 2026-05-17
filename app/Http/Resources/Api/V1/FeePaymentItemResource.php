<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeePaymentItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fee_payment_id' => $this->fee_payment_id,
            'student_fee_item_id' => $this->student_fee_item_id,
            'fee_category' => $this->whenLoaded('studentFeeItem', fn() => $this->studentFeeItem?->feeCategory?->name),
            'amount' => (float) $this->amount,
        ];
    }
}