<x-app-layout>
    <x-slot name="header">Due Management</x-slot>

    <div class="space-y-4" x-data="{ partyType: 'customer', customerOpen: false, supplierOpen: false }"
        @customer-created.window="customerOpen = false"
        @supplier-created.window="supplierOpen = false">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Customer Due</p>
                <p class="text-xl font-semibold text-red-600">৳{{ number_format($totals['customer_due'], 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Supplier Due</p>
                <p class="text-xl font-semibold text-amber-600">৳{{ number_format($totals['supplier_due'], 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Sale Wise Due</p>
                <p class="text-xl font-semibold text-blue-600">৳{{ number_format($totals['sale_due'], 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Purchase Wise Due</p>
                <p class="text-xl font-semibold text-violet-600">৳{{ number_format($totals['purchase_due'], 2) }}</p>
            </div>
        </div>

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
                                <option value="{{ $customer->id }}">{{ $customer->full_name }}{{ $customer->phone ? ' - '.$customer->phone : '' }}</option>
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
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}{{ $supplier->phone ? ' - '.$supplier->phone : '' }}</option>
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

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b"><h2 class="text-sm font-semibold text-gray-800">Customer Wise Due</h2></div>
                @include('dues.partials.customer_table', ['rows' => $customerDues])
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b"><h2 class="text-sm font-semibold text-gray-800">Supplier Wise Due</h2></div>
                @include('dues.partials.supplier_table', ['rows' => $supplierDues])
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b"><h2 class="text-sm font-semibold text-gray-800">Sale Wise Due</h2></div>
                @include('dues.partials.sale_table', ['rows' => $saleDues])
            </div>

            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b"><h2 class="text-sm font-semibold text-gray-800">Purchase Wise Due</h2></div>
                @include('dues.partials.purchase_table', ['rows' => $purchaseDues])
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b"><h2 class="text-sm font-semibold text-gray-800">Manual Dues</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-gray-50 border-b">
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Party</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($manualDues as $due)
                            <tr>
                                <td class="px-5 py-3"><span class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">{{ $due->reference }}</span></td>
                                <td class="px-5 py-3">{{ $due->party_type === 'customer' ? $due->customer?->full_name : $due->supplier?->name }}</td>
                                <td class="px-5 py-3 text-right text-red-600 font-semibold">৳{{ number_format($due->amount, 2) }}</td>
                                <td class="px-5 py-3">{{ optional($due->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('dues.edit', $due) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">Edit</a>
                                    <form method="POST" action="{{ route('dues.destroy', $due) }}" class="inline" onsubmit="return confirm('Delete manual due?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No manual dues found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($manualDues->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $manualDues->links() }}</div>@endif
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
