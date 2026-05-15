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
        'view products',
        'create products',
        'edit products',
        'delete products',
        'manage customers',
        'view customers',
        'create customers',
        'edit customers',
        'delete customers',
        'manage suppliers',
        'view suppliers',
        'create suppliers',
        'edit suppliers',
        'delete suppliers',
        'manage purchases',
        'view purchases',
        'create purchases',
        'edit purchases',
        'delete purchases',
        'manage purchase returns',
        'view purchase returns',
        'create purchase returns',
        'edit purchase returns',
        'delete purchase returns',
        'approve purchase returns',
        'manage stock',
        'view stock',
        'create stock',
        'edit stock',
        'delete stock',
        'distribute stock',
        'manage sales',
        'manage sale returns',
        'manage expenses',
        'manage cash',
        'manage dues',
        'manage cloth sewings',
        'view cloth sewings',
        'create cloth sewings',
        'edit cloth sewings',
        'delete cloth sewings',
        'manage received cloths',
        'view received cloths',
        'create received cloths',
        'edit received cloths',
        'delete received cloths',
        'manage sales men',
        'view sales men',
        'create sales men',
        'edit sales men',
        'delete sales men',
        'view sales',
        'create sales',
        'edit sales',
        'delete sales',
        'view sale returns',
        'create sale returns',
        'edit sale returns',
        'delete sale returns',
        'approve sale returns',
        'view expenses',
        'create expenses',
        'edit expenses',
        'delete expenses',
        'view cash',
        'create cash',
        'edit cash',
        'delete cash',
        'view dues',
        'create dues',
        'edit dues',
        'delete dues',
        'view users',
        'create users',
        'edit users',
        'delete users',
        'view roles',
        'create roles',
        'edit roles',
        'delete roles',
        'view permissions',
        'create permissions',
        'edit permissions',
        'delete permissions',
        'view shops',
        'create shops',
        'edit shops',
        'delete shops',
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
