<?php

namespace App\Models;

use App\Core\Tenant\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'status',
        'failure_reason',
        'logged_in_at',
        'logged_out_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
