<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:login {username} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test login with username and password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');

        $this->info("Testing login for: {$username}");

        // Check if user exists
        $user = \App\Models\User::where('username', $username)->first();

        if (!$user) {
            $this->error("User not found!");
            return 1;
        }

        $this->info("User found: {$user->name}");
        $this->info("Email: {$user->email}");
        $this->info("Active: " . ($user->active ? 'YES' : 'NO'));
        $this->info("Has password: " . (!empty($user->password) ? 'YES' : 'NO'));

        // Test Auth::attempt
        $credentials = [
            'username' => $username,
            'password' => $password,
        ];

        if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
            $this->info("✓ Login successful with USERNAME!");
            \Illuminate\Support\Facades\Auth::logout();
            return 0;
        } else {
            $this->error("✗ Login with username failed!");

            // Try with email
            $credentials = [
                'email' => $user->email,
                'password' => $password,
            ];

            if (\Illuminate\Support\Facades\Auth::attempt($credentials)) {
                $this->info("✓ Login with EMAIL successful!");
                \Illuminate\Support\Facades\Auth::logout();
                return 0;
            } else {
                $this->error("✗ Login with email also failed!");

                // Check password manually
                if (\Illuminate\Support\Facades\Hash::check($password, $user->password)) {
                    $this->info("Password is CORRECT, but Auth::attempt is failing");
                } else {
                    $this->error("Password is INCORRECT");
                }
            }

            return 1;
        }
    }
}
