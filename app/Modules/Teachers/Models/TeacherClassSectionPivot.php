<?php

namespace App\Modules\Teachers\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for the teacher_class_section table.
 *
 * Uses BelongsToSchool to auto-populate school_id when
 * Teacher::classSections()->attach() or sync() is called.
 * The school_id is derived from the parent Teacher model.
 */
class TeacherClassSectionPivot extends Pivot
{
    use BelongsToSchool;

    protected $table = 'teacher_class_section';

    public $incrementing = false;

    protected $fillable = [
        'teacher_id',
        'class_section_id',
        'school_id',
        'is_class_teacher',
    ];

    protected function casts(): array
    {
        return [
            'is_class_teacher' => 'boolean',
        ];
    }
}