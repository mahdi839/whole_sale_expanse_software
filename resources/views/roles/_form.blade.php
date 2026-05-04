@csrf
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
        <input name="name" value="{{ old('name', $role->name) }}" class="w-full border-gray-300 rounded-lg">
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div>
        <h3 class="text-sm font-semibold text-gray-800 mb-2">Permissions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($permissions as $permission)
                <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $role->permissions->pluck('name')->all())))>
                    {{ $permission->name }}
                </label>
            @endforeach
        </div>
    </div>
    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button>
        <a href="{{ route('roles.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
    </div>
</div>
