@php($worker = $worker ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $worker?->name) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $worker?->phone) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
    <textarea name="address" rows="3"
        class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('address', $worker?->address) }}</textarea>
    @error('address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

@if($hasDocumentNo ?? false)
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">NID / Passport No</label>
        <input type="text" name="nid_passport_no" value="{{ old('nid_passport_no', $worker?->nid_passport_no) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('nid_passport_no')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Paid</label>
        <input type="number" step="0.01" min="0" name="total_paid" value="{{ old('total_paid', $worker?->total_paid ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('total_paid')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Due</label>
        <input type="number" step="0.01" min="0" name="total_due" value="{{ old('total_due', $worker?->total_due ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('total_due')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Advance</label>
        <input type="number" step="0.01" min="0" name="advance" value="{{ old('advance', $worker?->advance ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('advance')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
