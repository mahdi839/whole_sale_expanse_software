@php
    $cheque = $cheque ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
        @if(auth()->user()->canManageAllShops())
            <select name="shop_id" id="cheque-shop-id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <option value="">Select shop</option>
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}" @selected(old('shop_id', $cheque?->shop_id) == $shop->id)>{{ $shop->name }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="shop_id" id="cheque-shop-id" value="{{ auth()->user()->shop_id }}">
            <div class="w-full h-10 px-3 flex items-center text-sm bg-gray-100 border border-gray-200 rounded-lg">{{ auth()->user()->shop?->name ?? 'No shop assigned' }}</div>
        @endif
        @error('shop_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Cheque No</label>
        <input name="cheque_no" value="{{ old('cheque_no', $cheque?->cheque_no) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('cheque_no')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
        <select name="customer_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select customer</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" data-shop-id="{{ $customer->shop_id }}" @selected(old('customer_id', $cheque?->customer_id) == $customer->id)>
                    {{ $customer->full_name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('customer_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bank</label>
        <input name="bank" value="{{ old('bank', $cheque?->bank) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('bank')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $cheque?->amount) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date</label>
        <input type="date" name="issue_date" value="{{ old('issue_date', optional($cheque?->issue_date)->format('Y-m-d') ?? now()->toDateString()) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('issue_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Deposit Date</label>
        <input type="date" name="deposit_date" value="{{ old('deposit_date', optional($cheque?->deposit_date)->format('Y-m-d')) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('deposit_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="pending" @selected(old('status', $cheque?->status ?? 'pending') === 'pending')>Pending</option>
            <option value="received" @selected(old('status', $cheque?->status) === 'received')>Received</option>
        </select>
        @error('status')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Documents</label>
        <input type="file" name="documents" class="w-full h-10 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @if($cheque?->documents)
            <a href="{{ Storage::url($cheque->documents) }}" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Current document</a>
        @endif
        @error('documents')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

@if(auth()->user()->canManageAllShops())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const shop = document.getElementById('cheque-shop-id');
            const customer = document.querySelector('select[name="customer_id"]');
            if (!shop || !customer) return;

            const options = [...customer.options].slice(1).map(option => ({
                value: option.value,
                text: option.textContent,
                shopId: option.dataset.shopId,
                selected: option.selected,
            }));
            const syncCustomers = () => {
                const selected = customer.value;
                customer.innerHTML = '<option value="">Select customer</option>';
                options.filter(option => !shop.value || option.shopId === shop.value).forEach(data => {
                    const option = new Option(data.text, data.value, false, data.value === selected || data.selected);
                    option.dataset.shopId = data.shopId;
                    customer.add(option);
                    data.selected = false;
                });
            };
            shop.addEventListener('change', syncCustomers);
            syncCustomers();
        });
    </script>
@endif

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
    <textarea name="note" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note', $cheque?->note) }}</textarea>
    @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
