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
        $this->call([
            RolesAndPermissionsSeeder::class,
            TestSeeder::class,
        ]);

        // Create an Admin user
        $admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@uitm.edu.my',
            'password' => bcrypt('asdfasdf'),
        ]);
        $admin->assignRole('system_admin');

        // Create a Test User
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
