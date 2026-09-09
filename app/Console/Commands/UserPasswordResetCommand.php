<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserPasswordResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email : The email address of the user} {password : The new password to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset password for any User (HR, Manager, Employee, Admin) and activate login access.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        $password = (string) $this->argument('password');

        $this->info("Looking for user with email: {$email}...");

        $user = User::on('tenant')->where('email', $email)->first();

        if (! $user && Schema::connection('tenant')->hasColumn('users', 'personal_email')) {
            $user = User::on('tenant')->where('personal_email', $email)->first();
        }

        if (! $user) {
            $user = User::on('mysql')->where('email', $email)->first();
        }

        if (! $user) {
            $this->error("User with email '{$email}' not found in current database!");
            return Command::FAILURE;
        }

        $user->password = Hash::make($password);
        $user->raw_password = $password;
        $user->login_allowed = true;
        $user->is_active = true;

        if (Schema::connection($user->getConnectionName() ?: 'tenant')->hasColumn('users', 'password_changed_notice')) {
            $user->password_changed_notice = false;
        }

        $user->save();

        $this->info("✔ Successfully updated password for: {$user->name} ({$user->email})");
        $this->info("✔ Role: " . ucfirst($user->role ?? 'User'));
        $this->info("✔ Login Allowed: YES");
        $this->info("✔ Active: YES");
        $this->info("✔ Password hash set and clean (no double-hashing).");

        return Command::SUCCESS;
    }
}
