<?php

namespace App\Modules\Auth\Services;

use App\Core\Tenant\SchoolContext;
use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;

class LoginActivityService
{
    public function recordSuccess(Request $request, User $user): void
    {
        LoginActivity::query()->create([
            'school_id' => app(SchoolContext::class)->id() ?: $user->current_school_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'status' => 'success',
            'logged_in_at' => now(),
        ]);

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();
    }

    public function recordFailure(Request $request, ?string $reason = null): void
    {
        LoginActivity::query()->withoutGlobalScopes()->create([
            'email' => $request->string('email')->toString(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    public function recordLogout(Request $request, User $user): void
    {
        LoginActivity::query()
            ->where('user_id', $user->id)
            ->where('status', 'success')
            ->whereNull('logged_out_at')
            ->latest()
            ->first()
            ?->update(['logged_out_at' => now()]);
    }
}
