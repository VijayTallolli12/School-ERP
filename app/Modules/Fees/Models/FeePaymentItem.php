<?php

namespace App\Modules\Fees\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePaymentItem extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'fee_payment_id',
        'student_fee_item_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function feePayment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class);
    }

    public function studentFeeItem(): BelongsTo
    {
        return $this->belongsTo(StudentFeeItem::class);
    }
}
