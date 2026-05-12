<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $userRole = Role::firstOrCreate(['name' => UserRole::USER->value]);
        $adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN->value]);
        $agentRole = Role::firstOrCreate(['name' => UserRole::AGENT->value]);

        $permissions = [
            'create auctions',
            'publish auctions',
            'close auctions',
            'view all transactions',
            'manage users',
            'access admin panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole->syncPermissions($permissions);
    }
}
