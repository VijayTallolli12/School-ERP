<?php

namespace App\Modules\Library\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookIssue extends Model
{
    use BelongsToSchool, SoftDeletes;

    protected $table = 'library_issues';

    protected $fillable = [
        'school_id',
        'book_id',
        'issueable_type',
        'issueable_id',
        'issue_date',
        'due_date',
        'return_date',
        'fine_amount',
        'fine_paid',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'return_date' => 'date',
            'fine_amount' => 'decimal:2',
            'fine_paid' => 'boolean',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function issueable(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
