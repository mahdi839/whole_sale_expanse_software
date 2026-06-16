@php($record = $workLog ?? $gareyManWorkLog ?? null)

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <div class="flex items-center justify-between gap-3 mb-1">
            <label class="block text-sm font-medium text-gray-700">Garey Man</label>
            <a href="{{ route('garey-men.create') }}" class="text-xs text-blue-600 hover:underline">Add Garey Man</a>
        </div>
        <select name="garey_man_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select garey man</option>
            @foreach($gareyMen as $gareyMan)
                <option value="{{ $gareyMan->id }}" @selected((string) old('garey_man_id', $record?->garey_man_id) === (string) $gareyMan->id)>
                    {{ $gareyMan->name }}{{ $gareyMan->phone ? ' - '.$gareyMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('garey_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($record?->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4" data-total-calculator data-qty-field="qty" data-rate-field="rate_per_goj">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
        <input type="number" step="0.01" min="0" name="qty" value="{{ old('qty', $record?->qty ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
        <input type="text" name="unit" value="{{ old('unit', $record?->unit ?? 'goj') }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('unit')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Per Goj Rate</label>
        <input type="number" step="0.01" min="0" name="rate_per_goj" value="{{ old('rate_per_goj', $record?->rate_per_goj ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('rate_per_goj')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Rate</label>
        <input type="text" data-total-output value="{{ number_format((float) old('total_rate', $record?->total_rate ?? 0), 2, '.', '') }}" readonly
            class="w-full h-10 px-3 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-700">
    </div>
</div>

@push('scripts')
    @include('shared._work_log_total_script')
@endpush
