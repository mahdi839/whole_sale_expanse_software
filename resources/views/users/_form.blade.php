@csrf
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input name="name" value="{{ old('name', $user->name) }}" class="w-full border-gray-300 rounded-lg">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border-gray-300 rounded-lg">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input type="password" name="password" class="w-full border-gray-300 rounded-lg">
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Shop</label>
            <select name="shop_id" class="w-full border-gray-300 rounded-lg">
                <option value="">No shop</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}" @selected(old('shop_id', $user->shop_id) == $shop->id)>{{ $shop->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_admin" value="1" @checked(old('is_admin', $user->is_admin)) class="rounded border-gray-300">
        <span class="text-sm text-gray-700">Legacy admin flag / full access</span>
    </label>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 mb-2">Roles</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($roles as $role)
                    <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, old('roles', $user->roles->pluck('name')->all())))>
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold text-gray-800 mb-2">Direct Permissions</h3>
            @include('shared._permission_groups', ['selectedPermissionNames' => $user->permissions->pluck('name')->all()])
        </div>
    </div>

    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button>
        <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
    </div>
</div>
