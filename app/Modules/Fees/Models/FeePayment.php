<?php

namespace App\Modules\Fees\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeePayment extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'academic_year_id',
        'receipt_number',
        'payment_mode',
        'amount',
        'remarks',
        'paid_on',
        'collected_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public static function paymentModes(): array
    {
        return [
            'cash' => 'Cash',
            'upi' => 'UPI',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeePaymentItem::class);
    }
}
