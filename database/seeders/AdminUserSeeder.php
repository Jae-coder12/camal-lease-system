<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('admin.name'));
        $email = trim((string) config('admin.email'));
        $username = trim((string) config('admin.username'));
        $password = (string) config('admin.password');

        if ($name === '') {
            $name = trim((string) $this->prompt('Administrator name', 'Market Administrator'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = trim((string) $this->prompt('Administrator email address'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Set a valid ADMIN_EMAIL in .env, or run db:seed interactively and enter a valid email address.');
        }

        if (!preg_match('/^[A-Za-z0-9_-]{3,40}$/', $username)) {
            $localPart = strstr($email, '@', true);
            $username = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $localPart), '-_');
            $username = substr($username, 0, 33) . '-admin';
        }
        if (!preg_match('/^[A-Za-z0-9_-]{3,40}$/', $username)) {
            $username = trim((string) $this->prompt('Administrator username', 'market-admin'));
        }
        if (!preg_match('/^[A-Za-z0-9_-]{3,40}$/', $username)) {
            throw new RuntimeException('ADMIN_USERNAME must be 3–40 letters, numbers, dashes, or underscores.');
        }

        $existing = User::where('email', $email)->first();
        if ($existing) {
            if (!$existing->isAdmin()) {
                throw new RuntimeException('ADMIN_EMAIL already belongs to a non-administrator account. Choose a different ADMIN_EMAIL.');
            }

            $this->command?->info('Configured administrator already exists; existing account credentials were left unchanged.');
            return;
        }

        if (strlen($password) < 12) {
            $password = $this->promptPassword();
        }

        if (User::where('username', $username)->exists()) {
            throw new RuntimeException('ADMIN_USERNAME is already assigned to another account. Choose a unique ADMIN_USERNAME.');
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'role' => 'admin',
        ]);

        $this->command?->info('Administrator account created from the configured environment credentials. Username: ' . $username);
    }

    private function prompt(string $question, ?string $default = null): ?string
    {
        if (!$this->command) {
            throw new RuntimeException('Administrator settings are missing. Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before seeding.');
        }

        return $this->command->ask($question, $default);
    }

    private function promptSecret(string $question): ?string
    {
        if (!$this->command) {
            throw new RuntimeException('Set ADMIN_PASSWORD in .env to a unique password of at least 12 characters before seeding.');
        }

        return $this->command->secret($question);
    }

    private function promptPassword(): string
    {
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $password = (string) $this->promptSecret('Administrator password (minimum 12 characters)');
            if (strlen($password) < 12) {
                $this->command?->warn('Password must be at least 12 characters. Try again.');
                continue;
            }

            $confirmation = (string) $this->promptSecret('Confirm administrator password');
            if (hash_equals($password, $confirmation)) {
                return $password;
            }

            $this->command?->warn('Passwords did not match. Try again.');
        }

        throw new RuntimeException('Could not confirm a valid administrator password after 3 attempts. No account was created. Set ADMIN_PASSWORD in .env and retry.');
    }
}
