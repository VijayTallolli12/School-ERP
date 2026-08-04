<?php

namespace App\Modules\Admissions\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmissionDocument extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $fillable = [
        'school_id',
        'admission_id',
        'document_type',
        'document_name',
        'file_path',
        'verified',
        'verified_at',
        'verified_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public static function documentTypes(): array
    {
        return [
            'birth_certificate' => 'Birth Certificate',
            'address_proof' => 'Address Proof',
            'marksheet' => 'Previous Marksheet',
            'transfer_certificate' => 'Transfer Certificate',
            'photo' => 'Passport Photo',
            'other' => 'Other',
        ];
    }
}
