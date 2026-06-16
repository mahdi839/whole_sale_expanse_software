@php($record = $workLog ?? $carryManWorkLog ?? null)

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <div class="flex items-center justify-between gap-3 mb-1">
            <label class="block text-sm font-medium text-gray-700">Carry Man</label>
            <a href="{{ route('carry-men.create') }}" class="text-xs text-blue-600 hover:underline">Add Carry Man</a>
        </div>
        <select name="carry_man_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select carry man</option>
            @foreach($carryMen as $carryMan)
                <option value="{{ $carryMan->id }}" @selected((string) old('carry_man_id', $record?->carry_man_id) === (string) $carryMan->id)>
                    {{ $carryMan->name }}{{ $carryMan->phone ? ' - '.$carryMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('carry_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($record?->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Memo No</label>
        <input type="text" name="memo_no" value="{{ old('memo_no', $record?->memo_no) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('memo_no')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Marka</label>
        <input type="text" name="marka" value="{{ old('marka', $record?->marka) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('marka')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Document Image</label>
        <input type="file" name="document_path" accept="image/*"
            class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg file:h-10 file:border-0 file:bg-gray-100 file:px-3 file:text-sm">
        @if($record?->document_path)
            <a href="{{ asset('storage/'.$record->document_path) }}" target="_blank" class="inline-block mt-1 text-xs text-blue-600 hover:underline">View current document</a>
        @endif
        @error('document_path')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4" data-total-calculator data-qty-field="total_unit_kg" data-rate-field="rate_per_kg">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bale Qty</label>
        <input type="number" step="0.01" min="0" name="bale_qty" value="{{ old('bale_qty', $record?->bale_qty ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('bale_qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Unit (KG)</label>
        <input type="number" step="0.01" min="0" name="total_unit_kg" value="{{ old('total_unit_kg', $record?->total_unit_kg ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('total_unit_kg')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Rate Per KG</label>
        <input type="number" step="0.01" min="0" name="rate_per_kg" value="{{ old('rate_per_kg', $record?->rate_per_kg ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('rate_per_kg')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
