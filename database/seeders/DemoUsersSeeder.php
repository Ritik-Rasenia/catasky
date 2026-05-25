<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure roles & permissions exist (PermissionSeeder should be run first)

        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@example.com', 'role' => 'Super Admin'],
            ['name' => 'Subscriber User', 'email' => 'subscriber@example.com', 'role' => 'Subscriber'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('Passw0rd!'),
                ]
            );

            if (! $user->hasRole($u['role'])) {
                $user->assignRole($u['role']);
            }
        }
    }
}
