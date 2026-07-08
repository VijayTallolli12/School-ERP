<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\PermissionRegistrar;

class DebugAuth extends Command
{
    protected $signature = 'debug:auth-http';
    protected $description = 'Debug auth via HTTP simulation';

    public function handle(): int
    {
        $this->info('=== HTTP SIMULATION ===');

        $users = [
            'admin@example.com' => 'School Admin',
            'aisha.khan@example.com' => 'Teacher',
            'superadmin@example.com' => 'Super Admin',
            'john.doe@example.com' => 'Parent',
        ];

        $appUrl = config('app.url');

        foreach ($users as $email => $role) {
            $this->warn("\n--- Testing: $email ($role) ---");
            
            // Step 1: Login
            $loginResponse = Http::asForm()->withoutVerifying()->post("$appUrl/login", [
                'email' => $email,
                'password' => 'password',
            ]);
            
            $this->line("Login status: " . $loginResponse->status());
            
            // Get session cookies
            $cookies = $loginResponse->cookies();
            $this->line("Cookies: " . json_encode($cookies->toArray()));
            
            // Step 2: Follow redirect to dashboard
            if ($loginResponse->status() === 302) {
                $redirectUrl = $loginResponse->header('Location');
                $this->line("Redirect URL: $redirectUrl");
                
                $dashboardResponse = Http::withOptions(['cookies' => $cookies])->withoutVerifying()->get($redirectUrl);
                $this->line("Dashboard status: " . $dashboardResponse->status());
                $this->line("Dashboard body (first 500 chars): " . substr($dashboardResponse->body(), 0, 500));
                
                if ($dashboardResponse->status() === 403) {
                    $this->error("    => 403 FORBIDDEN for $email ($role)");
                } else {
                    $this->info("    => OK for $email ($role)");
                }
            }
        }

        return Command::SUCCESS;
    }
}
