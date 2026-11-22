<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::where('email', 'test@example.com')->update([
            'role' => User::ROLE_MEMBER,
            'status' => User::STATUS_DISABLED,
            'member_id' => null,
        ]);

        $admin = config('membership.initial_admin');
        $email = trim((string) ($admin['email'] ?? ''));
        $password = (string) ($admin['password'] ?? '');

        if ($email === '' || $password === '') {
            $this->command?->warn('Initial admin was not seeded. Set INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD in .env first.');

            return;
        }

        $user = User::firstOrNew(['email' => $email, 'role' => User::ROLE_ADMIN]);
        $user->forceFill([
            'name' => $admin['name'] ?? 'Puihaha Administrator',
            'member_id' => null,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => Hash::make($password),
        ])->save();
    }
}
