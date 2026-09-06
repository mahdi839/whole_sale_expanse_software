@php
    $transaction = $transaction ?? null;
    $selectedEntryType = old('entry_type');

    if (! $selectedEntryType && $transaction?->exists) {
        $selectedEntryType = $transaction->entry_type ?: '';
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
        @if(auth()->user()->canManageAllShops())
            <select name="shop_id" required class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <option value="">Select shop</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}" @selected(old('shop_id', $transaction?->shop_id) == $shop->id)>{{ $shop->displayLabel() }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="shop_id" value="{{ auth()->user()->shop_id }}">
            <div class="h-10 px-3 flex items-center text-sm bg-gray-100 border border-gray-200 rounded-lg">{{ auth()->user()->shop?->displayLabel() ?? 'No shop assigned' }}</div>
        @endif
        @error('shop_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
        <select name="direction" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="in" @selected(old('direction', $transaction?->direction) === 'in')>Bank In</option>
            <option value="out" @selected(old('direction', $transaction?->direction) === 'out')>Bank Out</option>
        </select>
        @error('direction')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
        <input type="text" name="bank_name" value="{{ old('bank_name', $transaction?->bank_name) }}"
            placeholder="e.g. Dutch Bangla Bank"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('bank_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bank Details</label>
        <input type="text" name="bank_details" value="{{ old('bank_details', $transaction?->bank_details) }}"
            placeholder="Account, branch, transaction no"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('bank_details')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (BDT)</label>
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
        <label class="block text-sm font-medium text-gray-700 mb-1">Entry Type</label>
        <select name="entry_type" id="bank-entry-type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No related party</option>
            <option value="customer" @selected($selectedEntryType === 'customer')>Customer</option>
            <option value="supplier" @selected($selectedEntryType === 'supplier')>Supplier</option>
        </select>
        @error('entry_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="customer">
        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
        <select name="customer_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $transaction?->customer_id) == $customer->id)>
                    {{ $customer->displayLabel() }}
                </option>
            @endforeach
        </select>
        @error('customer_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="supplier">
        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
        <select name="supplier_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $transaction?->supplier_id) == $supplier->id)>
                    {{ $supplier->name }}{{ $supplier->phone ? ' - '.$supplier->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
        <textarea name="note" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note', $transaction?->note) }}</textarea>
        @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Document</label>
        <input type="file" name="document" class="w-full h-10 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @if($transaction?->document)
            <a href="{{ asset('storage/'.$transaction->document) }}" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Current document</a>
        @endif
        @error('document')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const entryType = document.getElementById('bank-entry-type');
    const partyFields = document.querySelectorAll('[data-party-field]');

    function setSelectState(select, isActive) {
        if (!select) return;
        select.disabled = !isActive;
        if (!select.tomselect) return;

        if (isActive) {
            select.tomselect.enable();
            select.tomselect.refreshOptions(false);
        } else {
            select.tomselect.clear(true);
            select.tomselect.disable();
        }
    }

    function syncPartyFields() {
        const selected = entryType?.value || '';

        partyFields.forEach((field) => {
            const isActive = field.dataset.partyField === selected;
            const select = field.querySelector('select');

            field.classList.toggle('hidden', !isActive);
            field.classList.toggle('relative', isActive);
            field.classList.toggle('z-40', isActive);
            setSelectState(select, isActive);
        });
    }

    entryType?.addEventListener('change', syncPartyFields);
    requestAnimationFrame(syncPartyFields);
});
</script>
@endpush
