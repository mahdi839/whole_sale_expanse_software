<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sales Man Name</label>
        <input type="text" name="name" value="{{ old('name', $salesMan->name) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $salesMan->phone) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Joining Date</label>
        <input type="date" name="joining_date" value="{{ old('joining_date', optional($salesMan->joining_date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('joining_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
        <textarea name="address" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('address', $salesMan->address) }}</textarea>
        @error('address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
