<x-app-layout>
    <x-slot name="header">Manual Due</x-slot>

    <div class="space-y-4" x-data="{ partyType: '{{ old('party_type', 'customer') }}', customerOpen: false, supplierOpen: false }"
        @customer-created.window="customerOpen = false"
        @supplier-created.window="supplierOpen = false">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Add Manual Due</h2>
            <form method="POST" action="{{ route('dues.store') }}" class="grid grid-cols-1 lg:grid-cols-6 gap-3">
                @csrf
                <div>
                    <label class="block text-xs text-gray-400 mb-1">Party</label>
                    <select name="party_type" x-model="partyType" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="customer">Customer</option>
                        <option value="supplier">Supplier</option>
                        <option value="tailor">Tailor</option>
                        <option value="computer_man">Computer Man</option>
                        <option value="carry_man">Carry Man</option>
                        <option value="garey_man">Garey Man</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Adjustment</label>
                    <select name="adjustment_type" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="add" @selected(old('adjustment_type', 'add') === 'add')>Add Due</option>
                        <option value="subtract" @selected(old('adjustment_type') === 'subtract')>Minus Due</option>
                    </select>
                    @error('adjustment_type')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'customer'">
                    <label class="block text-xs text-gray-400 mb-1">Customer</label>
                    <div class="flex gap-2">
                        <select name="customer_id" id="customer-select" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                            <option value="">Select customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-due="{{ $customer->due ?? 0 }}" @selected(old('customer_id') == $customer->id)>{{ $customer->full_name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="customerOpen = true" class="h-10 w-10 shrink-0 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">+</button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Present due: <span id="customer-present-due" class="font-semibold text-red-600">0.00</span></p>
                    @error('customer_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'supplier'">
                    <label class="block text-xs text-gray-400 mb-1">Supplier</label>
                    <div class="flex gap-2">
                        <select name="supplier_id" id="supplier-select" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" data-due="{{ $supplier->due ?? 0 }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}{{ $supplier->phone ? ' - '.$supplier->phone : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="supplierOpen = true" class="h-10 w-10 shrink-0 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">+</button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Present due: <span id="supplier-present-due" class="font-semibold text-red-600">0.00</span></p>
                    @error('supplier_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'tailor'">
                    <label class="block text-xs text-gray-400 mb-1">Tailor</label>
                    <select name="tailor_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">Select tailor</option>
                        @foreach($tailors as $tailor)
                            <option value="{{ $tailor->id }}" @selected(old('tailor_id') == $tailor->id)>{{ $tailor->name }}{{ $tailor->phone ? ' - '.$tailor->phone : '' }}</option>
                        @endforeach
                    </select>
                    @error('tailor_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'computer_man'">
                    <label class="block text-xs text-gray-400 mb-1">Computer Man</label>
                    <select name="computer_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">Select computer man</option>
                        @foreach($computerMen as $computerMan)
                            <option value="{{ $computerMan->id }}" @selected(old('computer_man_id') == $computerMan->id)>{{ $computerMan->name }}{{ $computerMan->phone ? ' - '.$computerMan->phone : '' }}</option>
                        @endforeach
                    </select>
                    @error('computer_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'carry_man'">
                    <label class="block text-xs text-gray-400 mb-1">Carry Man</label>
                    <select name="carry_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">Select carry man</option>
                        @foreach($carryMen as $carryMan)
                            <option value="{{ $carryMan->id }}" @selected(old('carry_man_id') == $carryMan->id)>{{ $carryMan->name }}{{ $carryMan->phone ? ' - '.$carryMan->phone : '' }}</option>
                        @endforeach
                    </select>
                    @error('carry_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'garey_man'">
                    <label class="block text-xs text-gray-400 mb-1">Garey Man</label>
                    <select name="garey_man_id" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">Select garey man</option>
                        @foreach($gareyMen as $gareyMan)
                            <option value="{{ $gareyMan->id }}" @selected(old('garey_man_id') == $gareyMan->id)>{{ $gareyMan->name }}{{ $gareyMan->phone ? ' - '.$gareyMan->phone : '' }}</option>
                        @endforeach
                    </select>
                    @error('garey_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs text-gray-400 mb-1">Date</label>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                </div>

                <div class="lg:col-span-5">
                    <label class="block text-xs text-gray-400 mb-1">Note</label>
                    <input type="text" name="note" value="{{ old('note') }}" placeholder="Reason or bill information"
                        class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                </div>

                <div class="flex items-end">
                    <button class="w-full h-10 px-4 bg-blue-600 text-white rounded-lg text-sm">Save</button>
                </div>
            </form>
        </div>

        @include('dues.partials.filters', [
            'route' => route('dues.manual'),
            'exportRoute' => route('dues.manual.export', request()->query()),
            'filters' => $filters,
            'placeholder' => 'Reference, party name, phone, note...',
        ])

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b"><h2 class="text-sm font-semibold text-gray-800">Manual Dues</h2></div>
            @include('dues.partials.manual_table', ['manualDues' => $manualDues])
        </div>

        <div x-show="customerOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form id="quick-customer-form" class="w-full max-w-md bg-white rounded-xl p-5 space-y-3">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-800">New Customer</h3>
                    <button type="button" @click="customerOpen = false" class="text-gray-400">x</button>
                </div>
                <input name="full_name" required placeholder="Customer name" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <input name="phone" placeholder="Phone" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <input name="alternative_phone" placeholder="Alternative phone" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <button class="w-full h-10 bg-blue-600 text-white rounded-lg text-sm">Save Customer</button>
            </form>
        </div>

        <div x-show="supplierOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <form id="quick-supplier-form" class="w-full max-w-md bg-white rounded-xl p-5 space-y-3">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-semibold text-gray-800">New Supplier</h3>
                    <button type="button" @click="supplierOpen = false" class="text-gray-400">x</button>
                </div>
                <input name="name" required placeholder="Supplier name" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <input name="phone" placeholder="Phone" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <textarea name="address" placeholder="Address" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg"></textarea>
                <button class="w-full h-10 bg-blue-600 text-white rounded-lg text-sm">Save Supplier</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const token = document.querySelector('meta[name="csrf-token"]').content;

    function formatMoney(value) {
        return (Number(value) || 0).toFixed(2);
    }

    function updatePresentDue(selectId, outputId) {
        const select = document.getElementById(selectId);
        const output = document.getElementById(outputId);
        const selectedOption = select?.selectedOptions?.[0];

        if (output) {
            output.textContent = formatMoney(selectedOption?.dataset?.due);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updatePresentDue('customer-select', 'customer-present-due');
        updatePresentDue('supplier-select', 'supplier-present-due');
    });

    document.getElementById('customer-select')?.addEventListener('change', () => {
        updatePresentDue('customer-select', 'customer-present-due');
    });

    document.getElementById('supplier-select')?.addEventListener('change', () => {
        updatePresentDue('supplier-select', 'supplier-present-due');
    });

    async function postQuick(form, url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: new FormData(form)
        });

        if (!response.ok) {
            alert('Could not save. Please check required fields.');
            return null;
        }

        return await response.json();
    }

    document.getElementById('quick-customer-form')?.addEventListener('submit', async function (event) {
        event.preventDefault();
        const customer = await postQuick(this, '{{ route('customers.store') }}');
        if (!customer) return;
        const select = document.getElementById('customer-select');
        const option = new Option(customer.full_name + (customer.phone ? ' - ' + customer.phone : ''), customer.id, true, true);
        option.dataset.due = '0';
        select.add(option);
        updatePresentDue('customer-select', 'customer-present-due');
        window.dispatchEvent(new CustomEvent('customer-created'));
        this.reset();
    });

    document.getElementById('quick-supplier-form')?.addEventListener('submit', async function (event) {
        event.preventDefault();
        const supplier = await postQuick(this, '{{ route('suppliers.store') }}');
        if (!supplier) return;
        const select = document.getElementById('supplier-select');
        const option = new Option(supplier.name + (supplier.phone ? ' - ' + supplier.phone : ''), supplier.id, true, true);
        option.dataset.due = '0';
        select.add(option);
        updatePresentDue('supplier-select', 'supplier-present-due');
        window.dispatchEvent(new CustomEvent('supplier-created'));
        this.reset();
    });
    </script>
    @endpush
</x-app-layout>
