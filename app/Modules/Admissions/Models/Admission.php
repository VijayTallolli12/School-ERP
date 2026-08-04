<?php

namespace App\Modules\Admissions\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\AcademicYear;
use App\Models\User;
use App\Modules\Academics\Models\ClassSection;
use App\Modules\Students\Models\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'admission_no',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'blood_group',
        'religion',
        'category',
        'caste',
        'nationality',
        'mother_tongue',
        'aadhar_no',
        'current_address',
        'permanent_address',
        'class_section_id',
        'academic_year_id',
        'status',
        'source',
        'guardian_name',
        'guardian_relation',
        'guardian_phone',
        'guardian_email',
        'guardian_occupation',
        'remarks',
        'applied_on',
        'verified_at',
        'verified_by',
        'approved_at',
        'approved_by',
        'converted_at',
        'converted_by',
        'student_id',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'applied_on' => 'date',
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classSection(): BelongsTo
    {
        return $this->belongsTo(ClassSection::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmissionDocument::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([$this->first_name, $this->middle_name, $this->last_name])->filter()->implode(' '));
    }

    public static function statuses(): array
    {
        return [
            'enquiry' => 'Enquiry',
            'application' => 'Application',
            'verified' => 'Verified',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'converted' => 'Converted',
        ];
    }

    public static function sources(): array
    {
        return [
            'walk_in' => 'Walk-in',
            'website' => 'Website',
            'referral' => 'Referral',
            'phone' => 'Phone',
            'other' => 'Other',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function getSourceLabelAttribute(): string
    {
        return self::sources()[$this->source] ?? $this->source;
    }
}
