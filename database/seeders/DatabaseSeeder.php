<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Run core seeders
        $this->call([
            DefaultPermissionsAndRolesSeeder::class,
            SubscriberRoleAndPlansSeeder::class,
        ]);

        // 2. Create default Super Admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@catasky.com'],
            [
                'name' => 'Super Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('Super Admin');

        // 3. Create Test User
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );

        // 4. Seed demo users for testing roles (superadmin/admin/staff/vendor/subscriber)
        if (class_exists(\Database\Seeders\DemoUsersSeeder::class)) {
            $this->call(\Database\Seeders\DemoUsersSeeder::class);
        }

        // 5. Seed realistic high-performance catalog/share demo products.
        $this->call(HighPerformanceDemoProductSeeder::class);
    }
}
