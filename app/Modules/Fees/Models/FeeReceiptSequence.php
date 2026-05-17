<?php

namespace App\Modules\Fees\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeReceiptSequence extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'last_number' => 'integer',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
