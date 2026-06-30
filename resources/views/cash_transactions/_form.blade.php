@php
    $transaction = $transaction ?? null;
    $selectedType = old('type', $transaction?->type ?? 'manual_add');
    $selectedEntryType = old('cash_entry_type');

    if (! $selectedEntryType && $transaction?->exists) {
        $selectedEntryType = match (true) {
            (bool) $transaction->customer_id => 'customer',
            (bool) $transaction->supplier_id => 'supplier',
            (bool) $transaction->tailor_id => 'tailor',
            (bool) $transaction->computer_man_id => 'computer',
            (bool) $transaction->carry_man_id => 'carry_man',
            (bool) $transaction->garey_man_id => 'garey_man',
            default => '',
        };
    }
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Cash Type</label>
        <select name="type" id="cash-type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="manual_add" @selected($selectedType === 'manual_add')>Add money</option>
            <option value="collection" @selected($selectedType === 'collection')>Collection</option>
            <option value="manual_out" @selected($selectedType === 'manual_out')>Cash out</option>
        </select>
        @error('type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
        <input type="text" name="payment_method" value="{{ old('payment_method', $transaction?->payment_method) }}"
            placeholder="Cash, bKash, bank..."
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('payment_method')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Cash Entry Type</label>
        <select name="cash_entry_type" id="cash-entry-type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No related party</option>
            <option value="customer" @selected($selectedEntryType === 'customer')>Customer</option>
            <option value="supplier" @selected($selectedEntryType === 'supplier')>Supplier</option>
            <option value="tailor" @selected($selectedEntryType === 'tailor')>Tailor</option>
            <option value="computer" @selected($selectedEntryType === 'computer')>Computer Man</option>
            <option value="carry_man" @selected($selectedEntryType === 'carry_man')>Carry Man</option>
            <option value="garey_man" @selected($selectedEntryType === 'garey_man')>Garey Man</option>
        </select>
        @error('cash_entry_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="customer">
        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
        <select name="customer_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(old('customer_id', $transaction?->customer_id) == $customer->id)>
                    {{ $customer->full_name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('customer_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="supplier">
        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
        <select name="supplier_id" id="cash-supplier" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                        data-currency="{{ $supplier->currency }}"
                        data-due="{{ $supplier->due }}"
                        @selected(old('supplier_id', $transaction?->supplier_id) == $supplier->id)>
                    {{ $supplier->name }}{{ $supplier->phone ? ' - '.$supplier->phone : '' }} ({{ $supplier->currency }})
                </option>
            @endforeach
        </select>
        @error('supplier_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div id="supplier-amount-field" class="hidden">
        <label id="supplier-amount-label" class="block text-sm font-medium text-gray-700 mb-1">Supplier Amount</label>
        <input type="number" step="0.01" min="0.01" name="supplier_amount"
               value="{{ old('supplier_amount', $transaction?->supplier_amount ?? ($transaction?->supplier_id ? $transaction?->amount : null)) }}"
               class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        <p id="supplier-due-help" class="text-xs text-gray-500 mt-1"></p>
        @error('supplier_amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="tailor">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tailor</label>
        <select name="tailor_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No tailor</option>
            @foreach($tailors ?? [] as $tailor)
                <option value="{{ $tailor->id }}" @selected(old('tailor_id', $transaction?->tailor_id) == $tailor->id)>
                    {{ $tailor->name }}
                </option>
            @endforeach
        </select>
        @error('tailor_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="computer">
        <label class="block text-sm font-medium text-gray-700 mb-1">Computer Man</label>
        <select name="computer_man_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No computer man</option>
            @foreach($computerMen ?? [] as $computerMan)
                <option value="{{ $computerMan->id }}" @selected(old('computer_man_id', $transaction?->computer_man_id) == $computerMan->id)>
                    {{ $computerMan->name }}{{ $computerMan->phone ? ' - '.$computerMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('computer_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="carry_man">
        <label class="block text-sm font-medium text-gray-700 mb-1">Carry Man</label>
        <select name="carry_man_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No carry man</option>
            @foreach($carryMen ?? [] as $carryMan)
                <option value="{{ $carryMan->id }}" @selected(old('carry_man_id', $transaction?->carry_man_id) == $carryMan->id)>
                    {{ $carryMan->name }}{{ $carryMan->phone ? ' - '.$carryMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('carry_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div data-party-field="garey_man">
        <label class="block text-sm font-medium text-gray-700 mb-1">Garey Man</label>
        <select name="garey_man_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">No garey man</option>
            @foreach($gareyMen ?? [] as $gareyMan)
                <option value="{{ $gareyMan->id }}" @selected(old('garey_man_id', $transaction?->garey_man_id) == $gareyMan->id)>
                    {{ $gareyMan->name }}{{ $gareyMan->phone ? ' - '.$gareyMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('garey_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
document.addEventListener('DOMContentLoaded', () => {
    const cashType = document.getElementById('cash-type');
    const cashDirection = document.getElementById('cash-direction');
    const cashEntryType = document.getElementById('cash-entry-type');
    const supplierSelect = document.getElementById('cash-supplier');
    const supplierAmountField = document.getElementById('supplier-amount-field');
    const supplierAmountInput = supplierAmountField?.querySelector('input');
    const supplierAmountLabel = document.getElementById('supplier-amount-label');
    const supplierDueHelp = document.getElementById('supplier-due-help');
    const partyFields = document.querySelectorAll('[data-party-field]');

    function syncCashDirection() {
        cashDirection.value = cashType.value === 'manual_out' ? 'out' : 'in';
    }

    function setSelectState(select, isActive) {
        select.disabled = !isActive;

        if (!select.tomselect) {
            return;
        }

        if (isActive) {
            select.tomselect.enable();
            select.tomselect.refreshOptions(false);
        } else {
            select.tomselect.clear(true);
            select.tomselect.disable();
        }
    }

    function syncPartyFields() {
        const selected = cashEntryType.value;

        partyFields.forEach((field) => {
            const isActive = field.dataset.partyField === selected;
            const select = field.querySelector('select');

            field.classList.toggle('hidden', !isActive);
            field.classList.toggle('relative', isActive);
            field.classList.toggle('z-40', isActive);
            setSelectState(select, isActive);
        });

        syncSupplierAmount();
    }

    function syncSupplierAmount() {
        const show = cashEntryType.value === 'supplier' && cashType.value === 'manual_out';
        const option = supplierSelect?.options[supplierSelect.selectedIndex];
        const currency = option?.dataset.currency || 'supplier currency';
        const due = Number(option?.dataset.due || 0);

        supplierAmountField?.classList.toggle('hidden', !show);
        if (supplierAmountInput) {
            supplierAmountInput.disabled = !show;
        }
        if (supplierAmountLabel) {
            supplierAmountLabel.textContent = `Supplier Amount (${currency})`;
        }
        if (supplierDueHelp) {
            supplierDueHelp.textContent = option?.value
                ? `Current due: ${currency} ${due.toFixed(2)}`
                : 'Select a supplier to see their currency and due.';
        }
    }

    cashType?.addEventListener('change', () => {
        syncCashDirection();
        syncSupplierAmount();
    });
    cashEntryType?.addEventListener('change', syncPartyFields);
    supplierSelect?.addEventListener('change', syncSupplierAmount);

    requestAnimationFrame(() => {
        syncCashDirection();
        syncPartyFields();
    });
});
</script>
@endpush
