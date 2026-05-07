<x-app-layout>
    <x-slot name="header">Edit Manual Due</x-slot>

    <form method="POST" action="{{ route('dues.update', $manualDue) }}" class="max-w-3xl bg-white border border-gray-200 rounded-xl p-5 space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Party Type</label>
                <select name="party_type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="customer" @selected(old('party_type', $manualDue->party_type) === 'customer')>Customer</option>
                    <option value="supplier" @selected(old('party_type', $manualDue->party_type) === 'supplier')>Supplier</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $manualDue->amount) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select name="customer_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $manualDue->customer_id) == $customer->id)>{{ $customer->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $manualDue->supplier_id) == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" value="{{ old('date', optional($manualDue->date)->format('Y-m-d')) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
            <textarea name="note" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note', $manualDue->note) }}</textarea>
        </div>
        <div class="flex gap-2">
            <button class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm">Update</button>
            <a href="{{ route('dues.index') }}" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm inline-flex items-center">Cancel</a>
        </div>
    </form>
</x-app-layout>
