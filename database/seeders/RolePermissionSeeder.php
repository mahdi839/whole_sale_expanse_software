<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'manage users',
        'manage roles',
        'manage permissions',
        'manage shops',
        'manage products',
        'manage customers',
        'manage suppliers',
        'manage purchases',
        'manage purchase returns',
        'manage stock',
        'distribute stock',
        'manage sales',
        'manage sale returns',
        'manage expenses',
    ];

    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        $shopExecutive = Role::firstOrCreate(['name' => 'Shop Executive', 'guard_name' => 'web']);
        $shopExecutive->syncPermissions([
            'manage customers',
            'manage sales',
            'manage sale returns',
        ]);
    }
}
