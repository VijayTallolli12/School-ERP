<?php

namespace App\Modules\Students\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class StudentDocument extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'student_documents';

    protected $fillable = [
        'school_id',
        'student_id',
        'document_type',
        'title',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'issue_date',
        'expiry_date',
        'remarks',
        'uploaded_by',
        'updated_by',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // -- Scopes --

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeExpiring($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', today())
            ->where('expiry_date', '<=', today()->addDays($days));
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    // -- Accessors --

    public function getFileSizeFormattedAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getDownloadUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return route('admin.documents.download', $this->id);
    }

    public function getVerificationStatusLabelAttribute(): string
    {
        return $this->is_verified ? 'Verified' : 'Pending';
    }

    public function getVerificationStatusBadgeAttribute(): string
    {
        return $this->is_verified
            ? '<span class="badge bg-success">Verified</span>'
            : '<span class="badge bg-warning text-dark">Pending</span>';
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return static::documentTypes()[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    // -- Static helpers --

    public static function documentTypes(): array
    {
        return [
            // Personal
            'birth_certificate' => 'Birth Certificate',
            'aadhaar_card' => 'Aadhaar Card',
            'passport' => 'Passport',
            'student_photograph' => 'Student Photograph',
            // Academic
            'transfer_certificate' => 'Transfer Certificate (TC)',
            'bonafide_certificate' => 'Bonafide Certificate',
            'previous_marks_card' => 'Previous Marks Card',
            'migration_certificate' => 'Migration Certificate',
            // Medical
            'medical_certificate' => 'Medical Certificate',
            'vaccination_record' => 'Vaccination Record',
            'health_report' => 'Health Report',
            // Other
            'other' => 'Other',
        ];
    }

    public static function documentCategories(): array
    {
        return [
            'personal' => ['birth_certificate', 'aadhaar_card', 'passport', 'student_photograph'],
            'academic' => ['transfer_certificate', 'bonafide_certificate', 'previous_marks_card', 'migration_certificate'],
            'medical' => ['medical_certificate', 'vaccination_record', 'health_report'],
            'other' => ['other'],
        ];
    }

    public static function allowedMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }

    public static function allowedExtensions(): string
    {
        return 'pdf,jpg,jpeg,png,doc,docx';
    }

    public static function maxFileSize(): int
    {
        return 10 * 1024; // KB
    }

    public static function uploadDirectory(int $schoolId, int $studentId): string
    {
        return "student-documents/{$schoolId}/{$studentId}";
    }
}
