<?php

namespace App\Modules\Teachers\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for the teacher_subject table.
 *
 * Uses BelongsToSchool to auto-populate school_id when Teacher::subjects()->attach()
 * or sync() is called. The school_id is derived from the parent Teacher model.
 */
class TeacherSubjectPivot extends Pivot
{
    use BelongsToSchool;

    protected $table = 'teacher_subject';

    public $incrementing = false;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'school_id',
    ];
}