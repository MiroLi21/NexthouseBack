<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        Permission::create(['name' => 'Administrator']);
        Permission::create(['name' => 'dashboard']);
        Permission::create(['name' => 'add user']);
        Permission::create(['name' => 'edit user']);
        Permission::create(['name' => 'delete user']);
        Permission::create(['name' => 'show users']);

        $permissions = Permission::all();

        // Roles
        $adminRole = Role::create(['name' => 'Administrator']);
        $adminRole->syncPermissions($permissions);

        $superAdmin = Role::create(['name' => 'Super-Admin']);
        $superAdmin->syncPermissions($permissions);

        // Admin user
        $admin = User::create([
            'name' => 'admin',
            'user_name' => 'admin',
            'password' => Hash::make('Admin@1234'),
            'email' => 'admin@nexthouse.com',
            'active' => 1,
        ]);

        $admin->assignRole($superAdmin);
    }
}
