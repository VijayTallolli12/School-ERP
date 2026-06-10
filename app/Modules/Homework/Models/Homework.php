<?php

namespace App\Modules\Homework\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Academics\Models\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Homework extends Model
{
    use BelongsToSchool, HasFactory, SoftDeletes;

    protected $table = 'homework';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'class_section_id',
        'subject_id',
        'title',
        'description',
        'assigned_date',
        'due_date',
        'attachment',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if (! $this->attachment) {
            return null;
        }

        return Storage::disk('public')->url($this->attachment);
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public static function statuses(): array
    {
        return ['active', 'inactive'];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
