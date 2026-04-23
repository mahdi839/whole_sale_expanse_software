<x-app-layout>
    <x-slot name="header">Sales</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('sales.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Reference, customer, memo..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full"
                    >

                    <select
                        name="payment_status"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full"
                    >
                        <option value="">Payment status</option>
                        <option value="due" @selected(request('payment_status') == 'due')>Due</option>
                        <option value="paid" @selected(request('payment_status') == 'paid')>Paid</option>
                        <option value="partial" @selected(request('payment_status') == 'partial')>Partial</option>
                    </select>

                    <select
                        name="status"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full"
                    >
                        <option value="">Sale status</option>
                        <option value="success" @selected(request('status') == 'success')>Success</option>
                        <option value="returned" @selected(request('status') == 'returned')>Returned</option>
                    </select>
                </div>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button
                            type="submit"
                            class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm w-full sm:w-auto"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('sales.index') }}"
                            class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center w-full sm:w-auto"
                        >
                            Reset
                        </a>
                    </div>

                    <div class="sm:ml-auto flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <a
                            href="{{ route('sales.export', request()->query()) }}"
                            class="h-10 px-4 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm inline-flex items-center justify-center gap-1 w-full sm:w-auto"
                        >
                            ⬇ CSV
                        </a>

                        <a
                            href="{{ route('sales.create') }}"
                            class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center gap-1 w-full sm:w-auto"
                        >
                            + New Sale
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Totals summary --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Sales</p>
                <p class="text-xl font-semibold text-gray-800">{{ number_format($totals->total_sales ?? 0) }}</p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Grand Total</p>
                <p class="text-xl font-semibold text-blue-600 break-words">
                    ৳{{ number_format($totals->total_amount ?? 0, 2) }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Paid</p>
                <p class="text-xl font-semibold text-green-600 break-words">
                    ৳{{ number_format($totals->total_paid ?? 0, 2) }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Due</p>
                <p class="text-xl font-semibold text-red-600 break-words">
                    ৳{{ number_format($totals->total_due ?? 0, 2) }}
                </p>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @forelse($sales as $sale)
                <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a
                                href="{{ route('sales.show', $sale) }}"
                                class="inline-flex items-center px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono break-all"
                            >
                                {{ $sale->reference }}
                            </a>

                            <div class="text-xs text-gray-400 mt-1">
                                {{ $sale->created_at->format('d M Y') }}
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="text-xs text-gray-400">Grand Total</p>
                            <p class="text-sm font-semibold text-blue-600">
                                ৳{{ number_format($sale->grand_total, 2) }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 mb-1">Customer</p>
                        <p class="text-sm font-medium text-gray-800 break-words">
                            {{ $sale->customer?->full_name ?? '—' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 mb-1">Products</p>
                        <div class="text-xs text-gray-600 space-y-1">
                            @foreach($sale->items->take(2) as $item)
                                <div class="break-words">
                                    {{ $item->product->product_name }} (x{{ $item->qty }})
                                </div>
                            @endforeach

                            @if($sale->items->count() > 2)
                                <div class="text-gray-400">
                                    +{{ $sale->items->count() - 2 }} more
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Paid</p>
                            <p class="mt-1 font-medium text-green-600 break-words">
                                ৳{{ number_format($sale->paid, 2) }}
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Due</p>
                            <p class="mt-1 font-medium text-red-600 break-words">
                                ৳{{ number_format($sale->due, 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span @class([
                            'px-2 py-1 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $sale->payment_status === 'paid',
                            'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                            'bg-red-50 text-red-700' => $sale->payment_status === 'due',
                        ])>
                            {{ ucfirst($sale->payment_status) }}
                        </span>

                        <span @class([
                            'px-2 py-1 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $sale->status === 'success',
                            'bg-orange-50 text-orange-700' => $sale->status === 'returned',
                            'bg-gray-100 text-gray-600' => blank($sale->status),
                        ])>
                            {{ $sale->status ? ucfirst($sale->status) : '—' }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <a
                            href="{{ route('sales.edit', $sale) }}"
                            class="px-3 py-2 text-xs bg-blue-50 text-blue-700 rounded-lg text-center"
                        >
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="{{ route('sales.destroy', $sale) }}"
                            onsubmit="return confirm('Delete sale?')"
                        >
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
                    No sales found.
                    <a href="{{ route('sales.create') }}" class="text-blue-600 hover:underline">Create first sale</a>
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Products</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Grand Total</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Payment</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3">
                                    <a
                                        href="{{ route('sales.show', $sale) }}"
                                        class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono"
                                    >
                                        {{ $sale->reference }}
                                    </a>
                                </td>

                                <td class="px-5 py-3">
                                    {{ $sale->customer?->full_name ?? '—' }}<br>
                                    <span class="text-xs text-gray-400">{{ $sale->created_at->format('d M Y') }}</span>
                                </td>

                                <td class="px-5 py-3 text-xs text-gray-600">
                                    @foreach($sale->items->take(2) as $item)
                                        {{ $item->product->product_name }} (x{{ $item->qty }})<br>
                                    @endforeach
                                    @if($sale->items->count() > 2)
                                        +{{ $sale->items->count() - 2 }} more
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-blue-600">
                                    ৳{{ number_format($sale->grand_total, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-green-600">
                                    ৳{{ number_format($sale->paid, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-red-600">
                                    ৳{{ number_format($sale->due, 2) }}
                                </td>

                                <td class="px-5 py-3">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-50 text-green-700' => $sale->payment_status === 'paid',
                                        'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                                        'bg-red-50 text-red-700' => $sale->payment_status === 'due',
                                    ])>
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-50 text-green-700' => $sale->status === 'success',
                                        'bg-orange-50 text-orange-700' => $sale->status === 'returned',
                                        'bg-gray-100 text-gray-600' => blank($sale->status),
                                    ])>
                                        {{ $sale->status ? ucfirst($sale->status) : '—' }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <a
                                            href="{{ route('sales.edit', $sale) }}"
                                            class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('sales.destroy', $sale) }}"
                                            onsubmit="return confirm('Delete sale?')"
                                        >
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
                                    No sales found.
                                    <a href="{{ route('sales.create') }}" class="text-blue-600 hover:underline">Create first sale</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>