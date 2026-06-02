@php
    $advance = $advance ?? null;
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
        <select name="employee_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select employee</option>
            @foreach($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $advance?->employee_id) == $employee->id)>
                    {{ $employee->name }}{{ $employee->phone ? ' - '.$employee->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('employee_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Advance Month</label>
        <select name="advance_month" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            @foreach($months as $month)
                <option value="{{ $month->format('Y-m') }}" @selected(old('advance_month', optional($advance?->advance_month)->format('Y-m') ?? now()->format('Y-m')) === $month->format('Y-m'))>
                    {{ $month->format('F Y') }}
                </option>
            @endforeach
        </select>
        @error('advance_month')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
        <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount', $advance?->amount) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>
