<x-app-layout>
    <x-slot name="header">Edit Manual Due</x-slot>

    <form method="POST" action="{{ route('dues.update', $manualDue) }}" class="max-w-3xl bg-white border border-gray-200 rounded-xl p-5 space-y-4"
        x-data="{ partyType: '{{ old('party_type', $manualDue->party_type) }}' }">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Party Type</label>
                <select name="party_type" x-model="partyType" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="customer" @selected(old('party_type', $manualDue->party_type) === 'customer')>Customer</option>
                    <option value="supplier" @selected(old('party_type', $manualDue->party_type) === 'supplier')>Supplier</option>
                    <option value="tailor" @selected(old('party_type', $manualDue->party_type) === 'tailor')>Tailor</option>
                    <option value="computer_man" @selected(old('party_type', $manualDue->party_type) === 'computer_man')>Computer Man</option>
                    <option value="carry_man" @selected(old('party_type', $manualDue->party_type) === 'carry_man')>Carry Man</option>
                    <option value="garey_man" @selected(old('party_type', $manualDue->party_type) === 'garey_man')>Garey Man</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment</label>
                <select name="adjustment_type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="add" @selected(old('adjustment_type', $manualDue->adjustment_type ?? 'add') === 'add')>Add Due</option>
                    <option value="subtract" @selected(old('adjustment_type', $manualDue->adjustment_type ?? 'add') === 'subtract')>Minus Due</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount</label>
                <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $manualDue->amount) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            </div>
            <div x-show="partyType === 'customer'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <select name="customer_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $manualDue->customer_id) == $customer->id)>{{ $customer->full_name }} - Due: {{ number_format($customer->due ?? 0, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="partyType === 'supplier'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <select name="supplier_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id', $manualDue->supplier_id) == $supplier->id)>{{ $supplier->name }} - Due: {{ number_format($supplier->due ?? 0, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="partyType === 'tailor'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Tailor</label>
                <select name="tailor_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select tailor</option>
                    @foreach($tailors as $tailor)
                        <option value="{{ $tailor->id }}" @selected(old('tailor_id', $manualDue->tailor_id) == $tailor->id)>{{ $tailor->name }}{{ $tailor->phone ? ' - '.$tailor->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="partyType === 'computer_man'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Computer Man</label>
                <select name="computer_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select computer man</option>
                    @foreach($computerMen as $computerMan)
                        <option value="{{ $computerMan->id }}" @selected(old('computer_man_id', $manualDue->computer_man_id) == $computerMan->id)>{{ $computerMan->name }}{{ $computerMan->phone ? ' - '.$computerMan->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="partyType === 'carry_man'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Carry Man</label>
                <select name="carry_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select carry man</option>
                    @foreach($carryMen as $carryMan)
                        <option value="{{ $carryMan->id }}" @selected(old('carry_man_id', $manualDue->carry_man_id) == $carryMan->id)>{{ $carryMan->name }}{{ $carryMan->phone ? ' - '.$carryMan->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="partyType === 'garey_man'">
                <label class="block text-sm font-medium text-gray-700 mb-1">Garey Man</label>
                <select name="garey_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <option value="">Select garey man</option>
                    @foreach($gareyMen as $gareyMan)
                        <option value="{{ $gareyMan->id }}" @selected(old('garey_man_id', $manualDue->garey_man_id) == $gareyMan->id)>{{ $gareyMan->name }}{{ $gareyMan->phone ? ' - '.$gareyMan->phone : '' }}</option>
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
