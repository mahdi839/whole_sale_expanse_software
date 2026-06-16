<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'manage carry men',
            'view carry men',
            'create carry men',
            'edit carry men',
            'delete carry men',
            'manage carry man work logs',
            'view carry man work logs',
            'create carry man work logs',
            'edit carry man work logs',
            'delete carry man work logs',
            'manage computer men',
            'view computer men',
            'create computer men',
            'edit computer men',
            'delete computer men',
            'manage computer man work logs',
            'view computer man work logs',
            'create computer man work logs',
            'edit computer man work logs',
            'delete computer man work logs',
            'manage garey men',
            'view garey men',
            'create garey men',
            'edit garey men',
            'delete garey men',
            'manage garey man work logs',
            'view garey man work logs',
            'create garey man work logs',
            'edit garey man work logs',
            'delete garey man work logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Role::where('name', 'Super Admin')->first()?->givePermissionTo($permissions);
    }

    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
