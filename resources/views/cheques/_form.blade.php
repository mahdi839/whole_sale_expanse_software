@php
    $cheque = $cheque ?? null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                <option value="{{ $customer->id }}" @selected(old('customer_id', $cheque?->customer_id) == $customer->id)>
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

<div class="mt-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
    <textarea name="note" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note', $cheque?->note) }}</textarea>
    @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
</div>
