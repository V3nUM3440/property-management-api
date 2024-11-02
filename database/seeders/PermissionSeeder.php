<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'view roles']);
        Permission::create(['name' => 'add roles']);
        Permission::create(['name' => 'edit roles']);
        Permission::create(['name' => 'delete roles']);

        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'add users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        Permission::create(['name' => 'view buildings']);
        Permission::create(['name' => 'add buildings']);
        Permission::create(['name' => 'edit buildings']);
        Permission::create(['name' => 'delete buildings']);

        Permission::create(['name' => 'view units']);
        Permission::create(['name' => 'view units others']);
        Permission::create(['name' => 'add units']);
        Permission::create(['name' => 'edit units']);
        Permission::create(['name' => 'edit units others']);
        Permission::create(['name' => 'delete units']);
        Permission::create(['name' => 'delete units others']);

        Permission::create(['name' => 'view partitions']);
        Permission::create(['name' => 'add partitions']);
        Permission::create(['name' => 'edit partitions']);
        Permission::create(['name' => 'delete partitions']);

        Permission::create(['name' => 'view contracts']);
        Permission::create(['name' => 'add contracts']);
        Permission::create(['name' => 'edit contracts']);
        Permission::create(['name' => 'delete contracts']);

        Permission::create(['name' => 'view tenants']);
        Permission::create(['name' => 'add tenants']);
        Permission::create(['name' => 'edit tenants']);
        Permission::create(['name' => 'delete tenants']);

        Permission::create(['name' => 'view payments']);
        Permission::create(['name' => 'add payments']);
        Permission::create(['name' => 'edit payments']);
        Permission::create(['name' => 'delete payments']);

        Permission::create(['name' => 'view security deposits']);
        Permission::create(['name' => 'add security deposits']);
        Permission::create(['name' => 'edit security deposits']);
        Permission::create(['name' => 'delete security deposits']);

        // create roles and assign created permissions
        Role::create(['name' => 'super-admin'])
        ->givePermissionTo(Permission::all());
    }
}
