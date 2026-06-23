<?php

namespace App\Modules\AiAgents\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentExecution extends Model
{
    protected $table = 'agent_executions';

    protected $fillable = [
        'agent_name',
        'executed_by',
        'status',
        'started_at',
        'completed_at',
        'records_processed',
        'result_summary',
        'error_message',
        'input_params',
        'output_data',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'input_params' => 'array',
            'output_data' => 'array',
            'records_processed' => 'integer',
        ];
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'executed_by');
    }
}
