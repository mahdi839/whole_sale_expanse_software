@csrf
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <input name="name" value="{{ old('name', $shop->name) }}" class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
            <input name="code" value="{{ old('code', $shop->code) }}" class="w-full border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input name="phone" value="{{ old('phone', $shop->phone) }}" class="w-full border-gray-300 rounded-lg">
        </div>
        <label class="flex items-center gap-2 mt-7">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $shop->is_active ?? true))>
            <span class="text-sm">Active</span>
        </label>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
        <textarea name="address" rows="3" class="w-full border-gray-300 rounded-lg">{{ old('address', $shop->address) }}</textarea>
    </div>
    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button>
        <a href="{{ route('shops.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
    </div>
</div>
