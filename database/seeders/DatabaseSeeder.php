<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call([PermissionSeeder::class]);

        // $superadmin = User::create([
        //     'name' => 'Super Admin',
        //     'email' => 'superadmin@test.com',
        //     'password' => '$2y$12$cOXwgFXQVCHtjQ8de4Lnb./K3GNdX7TnakS69eiFFEHQ/F8fEn.ma',
        // ]);
        // $superadmin->assignRole('super-admin');

        // $this->call([BuildingSeeder::class]);

        Permission::create(['name' => 'delete tenants']);
        Role::where('name', 'super-admin')->first()->givePermissionTo(Permission::all());
    }
}
