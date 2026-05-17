<?php

namespace App\Modules\Fees\Models;

use App\Core\Tenant\BelongsToSchool;
use Database\Factories\FeeCategoryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeCategory extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function structureItems(): HasMany
    {
        return $this->hasMany(FeeStructureItem::class);
    }

    public function studentFeeItems(): HasMany
    {
        return $this->hasMany(StudentFeeItem::class);
    }

    public static function defaultCodes(): array
    {
        return [
            'tuition' => 'Tuition',
            'transport' => 'Transport',
            'hostel' => 'Hostel',
            'exam' => 'Exam Fees',
            'miscellaneous' => 'Miscellaneous',
        ];
    }

    protected static function newFactory(): Factory
    {
        return FeeCategoryFactory::new();
    }
}
