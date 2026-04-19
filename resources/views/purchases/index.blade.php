<x-app-layout>
    <x-slot name="header">Purchases</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="GET" action="{{ route('purchases.index') }}">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, supplier, product..." class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">

                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">

                    <select name="purchase_status" class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">
                        <option value="">Purchase status</option>
                        <option value="received" @selected(($filters['purchase_status'] ?? '') === 'received')>Received</option>
                        <option value="partial" @selected(($filters['purchase_status'] ?? '') === 'partial')>Partial</option>
                        <option value="pending" @selected(($filters['purchase_status'] ?? '') === 'pending')>Pending</option>
                        <option value="ordered" @selected(($filters['purchase_status'] ?? '') === 'ordered')>Ordered</option>
                    </select>

                    <select name="payment_status" class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">
                        <option value="">Payment status</option>
                        <option value="due" @selected(($filters['payment_status'] ?? '') === 'due')>Due</option>
                        <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Paid</option>
                        <option value="partial" @selected(($filters['payment_status'] ?? '') === 'partial')>Partial</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="h-9 px-4 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
                    <button class="h-9 px-4 bg-cyan-600 text-white rounded-lg text-sm" type="button">
                        <a href="{{ route('purchases.index') }}">Reset</a>
                    </button>
                    <span class="flex-1"></span>
                    <a href="{{ route('purchases.export.csv', request()->query()) }}" class="h-9 px-4 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm flex items-center gap-1">⬇ CSV</a>
                    <a href="{{ route('purchases.create') }}" class="h-9 px-4 bg-blue-600 text-white rounded-lg text-sm flex items-center gap-1">+ New Purchase</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Purchases</p>
                <p class="text-xl font-semibold">{{ number_format($totals->total_purchases ?? 0) }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Grand Total</p>
                <p class="text-xl font-semibold text-green-600">৳{{ number_format($totals->total_amount ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Paid</p>
                <p class="text-xl font-semibold text-blue-600">৳{{ number_format($totals->total_paid ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Due</p>
                <p class="text-xl font-semibold text-red-600">৳{{ number_format($totals->total_due ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Supplier</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Seller Store</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Products</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400 hidden lg:table-cell">Purchased By</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Grand Total</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">
                                        {{ $purchase->reference }}
                                    </a>
                                    <div class="text-xs text-gray-400 mt-1">{{ optional($purchase->date)->format('d M Y') }}</div>
                                </td>

                                <td class="px-5 py-3">
                                    {{ $purchase->supplier?->name ?? ($purchase->seller_store_name ?: '—') }}
                                </td>

                                  <td class="px-5 py-3">
                                    {{ $purchase->seller_store_name ?: '—' }}
                                </td>

                                <td class="px-5 py-3 text-xs text-gray-600">
                                    @foreach($purchase->items->take(2) as $item)
                                        {{ $item->product->product_name ?? 'Unknown' }} (x{{ number_format($item->qty, 2) }})<br>
                                    @endforeach
                                    @if($purchase->items->count() > 2)
                                        +{{ $purchase->items->count() - 2 }} more
                                    @endif
                                </td>

                                <td class="px-5 py-3 hidden lg:table-cell">
                                    {{ $purchase->purchased_by }}
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-green-600">
                                    ৳{{ number_format($purchase->grand_total, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-blue-600">
                                    ৳{{ number_format($purchase->paid_amount, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-red-600">
                                    ৳{{ number_format($purchase->due_amount, 2) }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-xs font-medium w-fit',
                                            'bg-green-50 text-green-700' => $purchase->purchase_status === 'received',
                                            'bg-amber-50 text-amber-700' => $purchase->purchase_status === 'partial',
                                            'bg-gray-100 text-gray-600' => $purchase->purchase_status === 'pending',
                                            'bg-blue-50 text-blue-700' => $purchase->purchase_status === 'ordered',
                                        ])>{{ ucfirst($purchase->purchase_status) }}</span>

                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-xs font-medium w-fit',
                                            'bg-green-50 text-green-700' => $purchase->payment_status === 'paid',
                                            'bg-amber-50 text-amber-700' => $purchase->payment_status === 'partial',
                                            'bg-red-50 text-red-700' => $purchase->payment_status === 'due',
                                        ])>{{ ucfirst($purchase->payment_status) }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('purchases.edit', $purchase) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete purchase?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-20 text-center text-gray-400">
                                    No purchases found.
                                    <a href="{{ route('purchases.create') }}" class="text-blue-600">Create first purchase</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchases->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>