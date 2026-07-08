<?php

namespace App\Modules\Exams\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'grade_scales';

    protected $fillable = [
        'name',
        'grade',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'is_fail',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'min_percentage' => 'decimal:2',
            'max_percentage' => 'decimal:2',
            'grade_point' => 'decimal:2',
            'is_fail' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
