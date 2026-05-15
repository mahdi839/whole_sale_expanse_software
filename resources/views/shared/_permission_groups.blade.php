@php
    $selectedPermissionNames = $selectedPermissionNames ?? [];
    $availablePermissionNames = $permissions->pluck('name')->all();

    $permissionGroups = [
        'People' => [
            'Customers' => ['manage customers', 'view customers', 'create customers', 'edit customers', 'delete customers'],
            'Suppliers' => ['manage suppliers', 'view suppliers', 'create suppliers', 'edit suppliers', 'delete suppliers'],
            'Sales Men' => ['manage sales men', 'view sales men', 'create sales men', 'edit sales men', 'delete sales men'],
        ],
        'Cloth' => [
            'Cloth Sewing' => ['manage cloth sewings', 'view cloth sewings', 'create cloth sewings', 'edit cloth sewings', 'delete cloth sewings'],
            'Received Cloth' => ['manage received cloths', 'view received cloths', 'create received cloths', 'edit received cloths', 'delete received cloths'],
        ],
        'Inventory' => [
            'Products' => ['manage products', 'view products', 'create products', 'edit products', 'delete products'],
            'Shops' => ['manage shops', 'view shops', 'create shops', 'edit shops', 'delete shops'],
            'Stock' => ['manage stock', 'view stock', 'create stock', 'edit stock', 'delete stock', 'distribute stock'],
        ],
        'Purchasing' => [
            'Purchases' => ['manage purchases', 'view purchases', 'create purchases', 'edit purchases', 'delete purchases'],
            'Purchase Returns' => ['manage purchase returns', 'view purchase returns', 'create purchase returns', 'edit purchase returns', 'delete purchase returns', 'approve purchase returns'],
        ],
        'Sales' => [
            'Sales' => ['manage sales', 'view sales', 'create sales', 'edit sales', 'delete sales'],
            'Sale Returns' => ['manage sale returns', 'view sale returns', 'create sale returns', 'edit sale returns', 'delete sale returns', 'approve sale returns'],
        ],
        'Accounts' => [
            'Expenses' => ['manage expenses', 'view expenses', 'create expenses', 'edit expenses', 'delete expenses'],
            'Cash Management' => ['manage cash', 'view cash', 'create cash', 'edit cash', 'delete cash'],
            'Due Management' => ['manage dues', 'view dues', 'create dues', 'edit dues', 'delete dues'],
        ],
        'Access Control' => [
            'Users' => ['manage users', 'view users', 'create users', 'edit users', 'delete users'],
            'Roles' => ['manage roles', 'view roles', 'create roles', 'edit roles', 'delete roles'],
            'Permissions' => ['manage permissions', 'view permissions', 'create permissions', 'edit permissions', 'delete permissions'],
        ],
    ];

    $permissionLabels = [
        'manage' => 'Full Access',
        'view' => 'Index / View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'approve' => 'Approve',
        'distribute' => 'Distribute',
    ];

    $groupedPermissionNames = collect($permissionGroups)->flatMap(fn ($section) => collect($section)->flatten())->all();
    $ungroupedPermissions = $permissions->filter(fn ($permission) => ! in_array($permission->name, $groupedPermissionNames, true));
@endphp

<div class="space-y-5">
    @foreach($permissionGroups as $sectionLabel => $modules)
        <div class="space-y-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $sectionLabel }}</h4>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-3">
                @foreach($modules as $moduleLabel => $modulePermissions)
                    @php
                        $visiblePermissions = collect($modulePermissions)->filter(fn ($permissionName) => in_array($permissionName, $availablePermissionNames, true));
                    @endphp
                    @if($visiblePermissions->isNotEmpty())
                        <div class="border border-gray-200 rounded-xl bg-white overflow-hidden">
                            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                                <h5 class="text-sm font-semibold text-gray-800">{{ $moduleLabel }}</h5>
                            </div>
                            <div class="p-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($visiblePermissions as $permissionName)
                                    @php
                                        $action = str($permissionName)->before(' ')->toString();
                                        $label = $permissionLabels[$action] ?? str($permissionName)->headline();
                                    @endphp
                                    <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                                        <input type="checkbox" name="permissions[]" value="{{ $permissionName }}" @checked(in_array($permissionName, old('permissions', $selectedPermissionNames)))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    @if($ungroupedPermissions->isNotEmpty())
        <div class="space-y-3">
            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Other</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($ungroupedPermissions as $permission)
                    <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $selectedPermissionNames)))>
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
