<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([PermissionSeeder::class]);

        User::factory()->superadmin()->create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
        ]);

        // User::factory()->manager()->create([
        //     'name' => 'Manager',
        //     'email' => 'manager@test.com',
        // ]);

        // $this->call([BuildingSeeder::class]);
    }
}
