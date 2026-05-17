<?php

namespace App\Modules\Fees\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentFeeItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_fee_id',
        'fee_category_id',
        'amount',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function studentFee(): BelongsTo
    {
        return $this->belongsTo(StudentFee::class);
    }

    public function feeCategory(): BelongsTo
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function paymentItems(): HasMany
    {
        return $this->hasMany(FeePaymentItem::class);
    }

    public function getPaidAmountAttribute(): float
    {
        if (array_key_exists('paid_sum', $this->attributes)) {
            return (float) ($this->attributes['paid_sum'] ?? 0);
        }

        return (float) $this->paymentItems()->whereHas('feePayment')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->amount - $this->paid_amount);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (! $this->due_date) {
            return false;
        }

        return (float) $this->balance > 0 && $this->due_date->isPast();
    }
}
