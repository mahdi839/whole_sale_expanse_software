@php
    $transaction = $transaction ?? null;
    $selectedType = old('type', $transaction?->type ?? 'manual_add');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select name="type" id="cash-type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="manual_add" @selected($selectedType === 'manual_add')>Add money</option>
            <option value="collection" @selected($selectedType === 'collection')>Collection</option>
            <option value="manual_out" @selected($selectedType === 'manual_out')>Cash out</option>
        </select>
        @error('type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $transaction?->amount) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($transaction?->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
        <input type="text" name="payment_method" value="{{ old('payment_method', $transaction?->payment_method) }}"
            placeholder="Cash, bKash, bank..."
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('payment_method')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
        <select name="customer_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $transaction?->customer_id) == $customer->id)>
                    {{ $customer->full_name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('customer_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
        <select name="supplier_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $transaction?->supplier_id) == $supplier->id)>
                    {{ $supplier->name }}{{ $supplier->phone ? ' - '.$supplier->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sales Man</label>
        <select name="sales_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No sales man</option>
            @foreach($salesMen as $salesMan)
                <option value="{{ $salesMan->id }}" @selected(old('sales_man_id', $transaction?->sales_man_id) == $salesMan->id)>
                    {{ $salesMan->name }}{{ $salesMan->phone ? ' - '.$salesMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('sales_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tailor</label>
        <select name="tailor_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No tailor</option>
            @foreach($tailors ?? [] as $tailor)
                <option value="{{ $tailor->id }}" @selected(old('tailor_id', $transaction?->tailor_id) == $tailor->id)>
                    {{ $tailor->name }}
                </option>
            @endforeach
        </select>
        @error('tailor_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<input type="hidden" name="direction" id="cash-direction" value="{{ old('direction', $transaction?->direction ?? 'in') }}">

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
    <textarea name="note" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note', $transaction?->note) }}</textarea>
    @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>

@push('scripts')
<script>
document.getElementById('cash-type')?.addEventListener('change', function () {
    document.getElementById('cash-direction').value = this.value === 'manual_out' ? 'out' : 'in';
});
</script>
@endpush
