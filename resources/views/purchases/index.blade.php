<x-app-layout>
    <x-slot name="header">Purchases</x-slot>

    <div class="space-y-4">
        @if (session('success'))
            <div
                class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('purchases.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Reference, supplier, product..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">

                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">

                    <select name="purchase_status"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                        <option value="">Purchase status</option>
                        <option value="received" @selected(($filters['purchase_status'] ?? '') === 'received')>Received</option>
                        <option value="partial" @selected(($filters['purchase_status'] ?? '') === 'partial')>Partial</option>
                        <option value="pending" @selected(($filters['purchase_status'] ?? '') === 'pending')>Pending</option>
                        <option value="ordered" @selected(($filters['purchase_status'] ?? '') === 'ordered')>Ordered</option>
                    </select>

                    <select name="payment_status"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                        <option value="">Payment status</option>
                        <option value="due" @selected(($filters['payment_status'] ?? '') === 'due')>Due</option>
                        <option value="paid" @selected(($filters['payment_status'] ?? '') === 'paid')>Paid</option>
                        <option value="partial" @selected(($filters['payment_status'] ?? '') === 'partial')>Partial</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button type="submit"
                            class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm w-full sm:w-auto">
                            Filter
                        </button>

                        <a href="{{ route('purchases.index') }}"
                            class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center w-full sm:w-auto">
                            Reset
                        </a>
                    </div>

                    <div class="sm:ml-auto flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <a href="{{ route('purchases.export.csv', request()->query()) }}"
                            class="h-10 px-4 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm inline-flex items-center justify-center gap-1 w-full sm:w-auto">
                            ⬇ CSV
                        </a>

                        <a href="{{ route('purchases.create') }}"
                            class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center gap-1 w-full sm:w-auto">
                            + New Purchase
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Purchases</p>
                <p class="text-xl font-semibold text-gray-800">{{ number_format($totals->total_purchases ?? 0) }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Grand Total</p>
                @foreach($currencyTotals as $currencyTotal)
                    <p class="text-lg font-semibold text-green-600 break-words">
                        {{ $currencyTotal->currency }} {{ number_format($currencyTotal->total_amount, 2) }}
                    </p>
                @endforeach
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Paid</p>
                @foreach($currencyTotals as $currencyTotal)
                    <p class="text-lg font-semibold text-blue-600 break-words">
                        {{ $currencyTotal->currency }} {{ number_format($currencyTotal->total_paid, 2) }}
                    </p>
                @endforeach
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Due</p>
                @foreach($currencyTotals as $currencyTotal)
                    <p class="text-lg font-semibold text-red-600 break-words">
                        {{ $currencyTotal->currency }} {{ number_format($currencyTotal->total_due, 2) }}
                    </p>
                @endforeach
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Qty</p>
                <p class="text-xl font-semibold text-indigo-600 break-words">{{ number_format($totals->total_qty ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Stock</p>
                <p class="text-xl font-semibold text-cyan-600 break-words">{{ number_format($totals->total_stock ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @forelse($purchases as $purchase)
                <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('purchases.show', $purchase) }}"
                                class="inline-flex items-center px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono break-all">
                                {{ $purchase->reference }}
                            </a>
                            <div class="text-xs text-gray-400 mt-1">
                                {{ optional($purchase->date)->format('d M Y') }}
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-xs text-gray-400">Grand Total</p>
                            <p class="text-sm font-semibold text-green-600">
                                {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($purchase->grand_total, 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-2 text-sm">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Supplier</p>
                            <p class="font-medium text-gray-800 break-words">
                                {{ $purchase->supplier?->name ?? ($purchase->seller_store_name ?: '—') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-400 mb-1">Seller Store</p>
                            <p class="text-gray-700 break-words">
                                {{ $purchase->seller_store_name ?: '—' }}
                            </p>
                        </div>

                        <div class="text-xs text-gray-600 space-y-1">
                            @foreach ($purchase->items->take(2) as $item)
                                <div class="break-words">
                                    {{ $item->product->product_name ?? 'Unknown' }}
                                    (x{{ number_format($item->qty, 2) }})
                                    @ {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($item->price, 2) }}
                                </div>
                            @endforeach

                            @if ($purchase->items->count() > 2)
                                <div class="text-gray-400">
                                    +{{ $purchase->items->count() - 2 }} more
                                </div>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs text-gray-400 mb-1">Purchased By</p>
                            <p class="text-gray-700 break-words">
                                {{ $purchase->purchased_by ?: '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Paid</p>
                            <p class="mt-1 font-medium text-blue-600 break-words">
                                {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($purchase->paid_amount, 2) }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Due</p>
                            <p class="mt-1 font-medium text-red-600 break-words">
                                {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($purchase->due_amount, 2) }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Status</p>
                            <div class="mt-1">
                                <span @class([
                                    'px-2 py-0.5 rounded-full text-xs font-medium inline-flex',
                                    'bg-green-50 text-green-700' => $purchase->payment_status === 'paid',
                                    'bg-amber-50 text-amber-700' => $purchase->payment_status === 'partial',
                                    'bg-red-50 text-red-700' => $purchase->payment_status === 'due',
                                ])>
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span @class([
                            'px-2 py-1 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $purchase->purchase_status === 'received',
                            'bg-amber-50 text-amber-700' => $purchase->purchase_status === 'partial',
                            'bg-gray-100 text-gray-600' => $purchase->purchase_status === 'pending',
                            'bg-blue-50 text-blue-700' => $purchase->purchase_status === 'ordered',
                            'bg-red-50 text-red-700' => $purchase->purchase_status === 'returned',
                        ])>
                            {{ ucfirst($purchase->purchase_status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a href="{{ route('purchases.edit', $purchase) }}"
                            class="px-3 py-2 text-xs bg-blue-50 text-blue-700 rounded-lg text-center">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                            onsubmit="return confirm('Delete purchase?')">
                            @csrf
                            @method('DELETE')
                            <button class="w-full px-3 py-2 text-xs bg-red-50 text-red-700 rounded-lg">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-16 text-center text-gray-400">
                    No purchases found.
                    <a href="{{ route('purchases.create') }}" class="text-blue-600 hover:underline">
                        Create first purchase
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Supplier</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Seller Store</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Products</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Total Qty</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400 hidden lg:table-cell">
                                Purchased By</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Grand Total</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-left text-sm font-medium text-gray-400">Payment Status</th>
                            <th class="px-5 py-3 text-center text-sm font-medium text-gray-400">Purchase Status</th>
                            <th class="px-5 py-3 text-right text-sm font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3">
                                    <a href="{{ route('purchases.show', $purchase) }}"
                                        class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">
                                        {{ $purchase->reference }}
                                    </a>
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ optional($purchase->date)->format('d M Y') }}
                                    </div>
                                </td>

                                <td class="px-5 py-3">
                                    {{ $purchase->supplier?->name ?? ($purchase->seller_store_name ?: '—') }}
                                </td>

                                <td class="px-5 py-3">
                                    {{ $purchase->seller_store_name ?: '—' }}
                                </td>

                                <td class="px-5 py-3 text-xs text-gray-600">
                                    @foreach ($purchase->items->take(2) as $item)
                                        <div class="bg-blue-100 p-2 rounded-md ">
                                            {{ $item->product->product_name ?? 'Unknown' }}
                                            (x{{ number_format($item->qty, 2) }}) - 
                                            Unit Price: {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($item->price, 2) }}<br />
                                            Bale No: {{ $item->bale_no ?? '—' }}
                                        </div>
                                    @endforeach

                                    @if ($purchase->items->count() > 2)
                                        <div class="text-gray-400">
                                            +{{ $purchase->items->count() - 2 }} more
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-right text-indigo-600">
                                    {{ number_format($purchase->items->sum(fn($item) => (float) $item->qty), 2) }}
                                </td>

                                <td class="px-5 py-3 hidden lg:table-cell">
                                    {{ $purchase->purchased_by }}
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-green-600">
                                    {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($purchase->grand_total, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-blue-600">
                                    {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($purchase->paid_amount, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-red-600">
                                    {{ $purchase->supplier?->currency ?? 'BDT' }} {{ number_format($purchase->due_amount, 2) }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-xs font-medium w-fit',
                                            'bg-green-50 text-green-700' => $purchase->payment_status === 'paid',
                                            'bg-amber-50 text-amber-700' => $purchase->payment_status === 'partial',
                                            'bg-red-50 text-red-700' => $purchase->payment_status === 'due',
                                        ])>
                                            {{ ucfirst($purchase->payment_status) }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-3 text-center">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium w-fit',
                                        'bg-green-50 text-green-700' => $purchase->purchase_status === 'received',
                                        'bg-amber-50 text-amber-700' => $purchase->purchase_status === 'partial',
                                        'bg-gray-100 text-gray-600' => $purchase->purchase_status === 'pending',
                                        'bg-blue-50 text-blue-700' => $purchase->purchase_status === 'ordered',
                                        'bg-red-50 text-red-700' => $purchase->purchase_status === 'returned',
                                    ])>
                                        {{ ucfirst($purchase->purchase_status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                            class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                                            onsubmit="return confirm('Delete purchase?')">
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
                                <td colspan="12" class="px-5 py-20 text-center text-gray-400">
                                    No purchases found.
                                    <a href="{{ route('purchases.create') }}" class="text-blue-600 hover:underline">
                                        Create first purchase
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($purchases->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
