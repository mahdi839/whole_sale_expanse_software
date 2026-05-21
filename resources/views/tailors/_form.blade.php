<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $tailor->name) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $tailor->phone) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
    <textarea name="address" rows="3"
        class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('address', $tailor->address) }}</textarea>
    @error('address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
        <input type="file" name="profile_picture" accept="image/*"
            class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg file:h-10 file:border-0 file:bg-gray-100 file:px-3 file:text-sm">
        @if($tailor->profile_picture)
            <a href="{{ asset('storage/'.$tailor->profile_picture) }}" target="_blank" class="inline-block mt-1 text-xs text-blue-600 hover:underline">View current picture</a>
        @endif
        @error('profile_picture')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">NID / Passport Picture</label>
        <input type="file" name="document_path" accept="image/*"
            class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg file:h-10 file:border-0 file:bg-gray-100 file:px-3 file:text-sm">
        @if($tailor->document_path)
            <a href="{{ asset('storage/'.$tailor->document_path) }}" target="_blank" class="inline-block mt-1 text-xs text-blue-600 hover:underline">View current document</a>
        @endif
        @error('document_path')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
