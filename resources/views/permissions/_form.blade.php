@csrf
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 max-w-xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Permission Name</label>
        <input name="name" value="{{ old('name', $permission->name) }}" class="w-full border-gray-300 rounded-lg">
        <x-input-error :messages="$errors->get('name')" class="mt-1" />
    </div>
    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button>
        <a href="{{ route('permissions.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
    </div>
</div>
