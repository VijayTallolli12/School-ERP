<?php

namespace App\Modules\AiAssistant\Models;

use App\Core\Tenant\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiQueryLog extends Model
{
    use BelongsToSchool, HasFactory;

    protected $table = 'ai_query_logs';

    protected $fillable = [
        'user_id',
        'role',
        'intent',
        'question',
        'parameters',
        'response_summary',
        'status',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
