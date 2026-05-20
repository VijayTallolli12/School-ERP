<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class FixParentPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:parent-password {--email= : Email of the parent to reset (default: first Parent role user)}
                            {--password=password123 : New plain-text password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a test parent user password to a known value using Hash::make.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email');

        if ($email) {
            $user = User::where('email', $email)->first();
        } else {
            $user = User::role('Parent')->first();
        }

        if (! $user) {
            $this->error('No parent user found.');

            return self::FAILURE;
        }

        $plainPassword = $this->option('password');

        $this->info("User:  {$user->name} <{$user->email}>");
        $this->info("Old hash (first 60 chars): " . substr($user->password, 0, 60) . '...');

        // Directly assign a freshly bcrypt-hashed password
        $user->password = Hash::make($plainPassword);
        $user->save();

        // Verify immediately
        $user->refresh();
        $check = Hash::check($plainPassword, $user->password);

        $this->info("New hash (first 60 chars): " . substr($user->password, 0, 60) . '...');
        $this->info("Hash algorithm info: " . json_encode(password_get_info($user->password)));

        if ($check) {
            $this->info("✅ Password verified successfully. You can now login with:");
            $this->line("   Email:    {$user->email}");
            $this->line("   Password: {$plainPassword}");

            return self::SUCCESS;
        }

        $this->error('❌ Password verification FAILED after reset!');

        return self::FAILURE;
    }
}