<?php

namespace App\Modules\Students\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGuardian extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'user_id',
        'relation',
        'name',
        'phone',
        'email',
        'occupation',
        'is_primary',
        'can_pickup',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'can_pickup' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
