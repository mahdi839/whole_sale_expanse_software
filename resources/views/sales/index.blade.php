<x-app-layout>
    <x-slot name="header">Sales</x-slot>

    <div class="space-y-4">

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter card --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="GET" action="{{ route('sales.index') }}">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-2.5 mb-3.5">

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Product, ref, memo…"
                               class="w-full pl-9 pr-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                      placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>
                    </div>

                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                           class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                  text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>

                    <select name="purchase_status"
                            class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                        <option value="">Sale status</option>
                        @foreach(['received'=>'Received','partial'=>'Partial','pending'=>'Pending','ordered'=>'Ordered'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($filters['purchase_status'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>

                    <select name="payment_status"
                            class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                        <option value="">Payment status</option>
                        @foreach(['due'=>'Due','paid'=>'Paid','partial'=>'Partial'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($filters['payment_status'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                            class="h-9 px-4 text-sm font-medium text-white bg-gray-800 rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('sales.index') }}"
                       class="h-9 px-4 inline-flex items-center text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition">
                        Reset
                    </a>
                    <span class="flex-1"></span>
                    <a href="{{ route('sales.export', request()->query()) }}"
                       class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-green-700
                              bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16"/>
                        </svg>
                        Download CSV
                    </a>
                    <a href="{{ route('sales.create') }}"
                       class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Sale
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            @foreach([
                ['label' => 'Total Sales',   'value' => number_format($totals->total_sales ?? 0),          'color' => 'text-gray-800'],
                ['label' => 'Total Qty',     'value' => number_format($totals->total_qty ?? 0, 2),          'color' => 'text-gray-800'],
                ['label' => 'Grand Total',   'value' => '৳'.number_format($totals->total_amount ?? 0, 2),  'color' => 'text-blue-600'],
                ['label' => 'Total Paid',    'value' => '৳'.number_format($totals->total_paid ?? 0, 2),    'color' => 'text-green-600'],
                ['label' => 'Total Due',     'value' => '৳'.number_format($totals->total_due ?? 0, 2),     'color' => 'text-red-600'],
            ] as $card)
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">{{ $card['label'] }}</p>
                <p class="text-xl font-semibold {{ $card['color'] }}">{{ $card['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide whitespace-nowrap">Reference</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Customer</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden lg:table-cell">Product</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Qty</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Total</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Paid</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Due</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50/60 transition-colors">

                                <td class="px-5 py-3.5 align-top">
                                    <a href="{{ route('sales.show', $sale) }}"
                                       class="inline-block px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-mono font-medium hover:bg-violet-100 transition">
                                        {{ $sale->reference }}
                                    </a>
                                    @if($sale->cash_memo)
                                        <p class="text-xs text-gray-400 mt-1">{{ $sale->cash_memo }}</p>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <p class="font-medium text-gray-800">{{ $sale->customer?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ optional($sale->date)->format('d M Y') }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top hidden lg:table-cell">
                                    <p class="font-medium text-gray-800">{{ $sale->product_name ?? '—' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $sale->product_code ?: '—' }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-gray-700 tabular-nums">
                                    {{ number_format($sale->qty, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-blue-600 tabular-nums hidden md:table-cell">
                                    ৳{{ number_format($sale->grand_total, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-green-600 tabular-nums hidden md:table-cell">
                                    ৳{{ number_format($sale->paid, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-red-600 tabular-nums hidden md:table-cell">
                                    ৳{{ number_format($sale->due, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'inline-flex items-center w-fit px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-50 text-green-700' => $sale->purchase_status === 'received',
                                            'bg-amber-50 text-amber-700' => $sale->purchase_status === 'partial',
                                            'bg-gray-100 text-gray-600'  => $sale->purchase_status === 'pending',
                                            'bg-blue-50 text-blue-700'   => $sale->purchase_status === 'ordered',
                                        ])>{{ ucfirst($sale->purchase_status) }}</span>

                                        <span @class([
                                            'inline-flex items-center w-fit px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-50 text-green-700' => $sale->payment_status === 'paid',
                                            'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                                            'bg-red-50 text-red-700'     => $sale->payment_status === 'due',
                                        ])>{{ ucfirst($sale->payment_status) }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('sales.edit', $sale) }}"
                                           class="inline-flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium
                                                  text-blue-700 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                                              onsubmit="return confirm('Delete sale {{ addslashes($sale->reference) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium
                                                           text-red-700 bg-red-50 border border-red-100 rounded-lg hover:bg-red-100 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                        </svg>
                                        <p class="text-sm">No sales found.
                                            <a href="{{ route('sales.create') }}" class="text-blue-600 hover:underline">Add your first sale.</a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>

        @if($sales->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $sales->firstItem() }}–{{ $sales->lastItem() }}
                of {{ number_format($sales->total()) }} sales
            </p>
        @endif

    </div>
</x-app-layout>