@php
    $employee = $employee ?? null;
    $labelClass = 'block text-sm font-medium text-gray-700 mb-1';
    $inputClass = 'w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="{{ $labelClass }}">Name</label>
        <input type="text" name="name" value="{{ old('name', $employee?->name) }}" class="{{ $inputClass }}">
        @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $employee?->phone) }}" class="{{ $inputClass }}">
        @error('phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}"> Passport or NID Number</label>
        <input type="text" name="documents" value="{{ old('documents', $employee?->documents) }}" class="{{ $inputClass }}">
        @error('documents')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">Employment Type</label>
        <input type="text" name="employment_type" value="{{ old('employment_type', $employee?->employment_type) }}" placeholder="Sales men, manager, distributor..." class="{{ $inputClass }}">
        @error('employment_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">Joining Date</label>
        <input type="date" name="joining_date" value="{{ old('joining_date', optional($employee?->joining_date)->format('Y-m-d') ?? now()->toDateString()) }}" class="{{ $inputClass }}">
        @error('joining_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="{{ $labelClass }}">Monthly Salary Amount</label>
        <input type="number" name="salary_amount" step="0.01" min="0" value="{{ old('salary_amount', $employee?->salary_amount) }}" class="{{ $inputClass }}">
        @error('salary_amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="md:col-span-2">
        <label class="{{ $labelClass }}">Address</label>
        <textarea name="address" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('address', $employee?->address) }}</textarea>
        @error('address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
