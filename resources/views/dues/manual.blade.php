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
                    </select>
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'customer'">
                    <label class="block text-xs text-gray-400 mb-1">Customer</label>
                    <div class="flex gap-2">
                        <select name="customer_id" id="customer-select" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                            <option value="">Select customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->full_name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="customerOpen = true" class="h-10 w-10 shrink-0 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">+</button>
                    </div>
                    @error('customer_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="lg:col-span-2" x-show="partyType === 'supplier'">
                    <label class="block text-xs text-gray-400 mb-1">Supplier</label>
                    <div class="flex gap-2">
                        <select name="supplier_id" id="supplier-select" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                            <option value="">Select supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}{{ $supplier->phone ? ' - '.$supplier->phone : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="supplierOpen = true" class="h-10 w-10 shrink-0 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">+</button>
                    </div>
                    @error('supplier_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
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
                    <button class="w-full h-10 px-4 bg-blue-600 text-white rounded-lg text-sm">Add Due</button>
                </div>
            </form>
        </div>

        @include('dues.partials.filters', [
            'route' => route('dues.manual'),
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
                <input name="email" placeholder="Email" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                <button class="w-full h-10 bg-blue-600 text-white rounded-lg text-sm">Save Supplier</button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    const token = document.querySelector('meta[name="csrf-token"]').content;

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
        select.add(new Option(customer.full_name + (customer.phone ? ' - ' + customer.phone : ''), customer.id, true, true));
        window.dispatchEvent(new CustomEvent('customer-created'));
        this.reset();
    });

    document.getElementById('quick-supplier-form')?.addEventListener('submit', async function (event) {
        event.preventDefault();
        const supplier = await postQuick(this, '{{ route('suppliers.store') }}');
        if (!supplier) return;
        const select = document.getElementById('supplier-select');
        select.add(new Option(supplier.name + (supplier.phone ? ' - ' + supplier.phone : ''), supplier.id, true, true));
        window.dispatchEvent(new CustomEvent('supplier-created'));
        this.reset();
    });
    </script>
    @endpush
</x-app-layout>
