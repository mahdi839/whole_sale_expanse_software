{{-- Shared by create.blade.php and edit.blade.php --}}
@php $customer = $customer ?? null; @endphp

{{-- Full Name --}}
<div class="space-y-1.5">
    <label for="full_name" class="block text-sm font-medium text-gray-700">
        Full Name <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="full_name"
        name="full_name"
        value="{{ old('full_name', $customer?->full_name) }}"
        placeholder="e.g. Rahim Uddin"
        class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
               @error('full_name') border-red-400 bg-red-50 focus:ring-red-400
               @else border-gray-200 focus:ring-blue-500 @enderror
               focus:outline-none focus:ring-2 focus:border-transparent"
    />
    @error('full_name')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Phone --}}
<div class="space-y-1.5">
    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
    <input
        type="text"
        id="phone"
        name="phone"
        value="{{ old('phone', $customer?->phone) }}"
        placeholder="e.g. 01700-000000"
        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    />
    @error('phone')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Alternative Phone --}}
<div class="space-y-1.5">
    <label for="alternative_phone" class="block text-sm font-medium text-gray-700">Alternative Phone</label>
    <input
        type="text"
        id="alternative_phone"
        name="alternative_phone"
        value="{{ old('alternative_phone', $customer?->alternative_phone) }}"
        placeholder="e.g. 01800-000000"
        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    />
    @error('alternative_phone')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Image --}}
<div class="space-y-1.5">
    <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
    @if($customer?->image)
        <div class="mb-2 flex items-center gap-3">
            <img src="{{ asset('storage/'.$customer->image) }}" alt="{{ $customer->full_name }}" class="w-14 h-14 rounded-full object-cover border border-gray-200">
            <span class="text-xs text-gray-500">Current image</span>
        </div>
    @endif
    <input
        type="file"
        id="image"
        name="image"
        accept="image/*"
        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    />
    @error('image')
        <p class="flex items-center gap-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

{{-- Address --}}
<div class="space-y-1.5">
    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
    <textarea
        id="address"
        name="address"
        rows="3"
        placeholder="Customer address"
        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    >{{ old('address', $customer?->address) }}</textarea>
    @error('address')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
