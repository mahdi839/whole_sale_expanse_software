<x-app-layout>
    <x-slot name="header">Purchases</x-slot>

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
            <form method="GET" action="{{ route('purchases.index') }}">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-2.5 mb-3.5">

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Product, code, ref, memo…"
                               class="w-full pl-9 pr-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                      placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>
                    </div>

                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                           class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                  text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>

                    <input type="text" name="seller_store_name" value="{{ $filters['seller_store_name'] ?? '' }}"
                           placeholder="Seller / Store"
                           class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                  placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>

                    <input type="text" name="purchased_by" value="{{ $filters['purchased_by'] ?? '' }}"
                           placeholder="Purchased by"
                           class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                  placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>

                    <select name="purchase_status"
                            class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                        <option value="">Purchase status</option>
                        <option value="received" {{ ($filters['purchase_status'] ?? '') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="partial"  {{ ($filters['purchase_status'] ?? '') === 'partial'  ? 'selected' : '' }}>Partial</option>
                        <option value="pending"  {{ ($filters['purchase_status'] ?? '') === 'pending'  ? 'selected' : '' }}>Pending</option>
                        <option value="ordered"  {{ ($filters['purchase_status'] ?? '') === 'ordered'  ? 'selected' : '' }}>Ordered</option>
                    </select>

                    <select name="payment_status"
                            class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                        <option value="">Payment status</option>
                        <option value="due"     {{ ($filters['payment_status'] ?? '') === 'due'     ? 'selected' : '' }}>Due</option>
                        <option value="paid"    {{ ($filters['payment_status'] ?? '') === 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                            class="h-9 px-4 text-sm font-medium text-white bg-gray-800 border border-gray-200
                                   rounded-lg  transition">
                        Filter
                    </button>

                    <a href="{{ route('purchases.index') }}"
                       class="h-9 px-4 inline-flex items-center text-sm font-medium text-white
                              bg-cyan-600 rounded-lg hover:bg-cyan-700 transition">
                        Reset
                    </a>

                    <span class="flex-1"></span>

                    <a href="{{ route('purchases.export.csv', request()->query()) }}"
                       class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-green-700
                              bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16"/>
                        </svg>
                        Download CSV
                    </a>

                    <a href="{{ route('purchases.create') }}"
                       class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-white
                              bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Purchase
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['label' => 'Total Purchases', 'value' => number_format($totals->total_purchases ?? 0),           'accent' => false],
                ['label' => 'Total Qty',        'value' => number_format($totals->total_qty ?? 0, 2),             'accent' => false],
                ['label' => 'Subtotal',         'value' => '৳'.number_format($totals->total_subtotal ?? 0, 2),   'accent' => false],
                ['label' => 'Grand Total',      'value' => '৳'.number_format($totals->total_amount ?? 0, 2),     'accent' => true],
            ] as $card)
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">{{ $card['label'] }}</p>
                <p class="text-2xl font-semibold {{ $card['accent'] ? 'text-green-600' : 'text-gray-800' }}">
                    {{ $card['value'] }}
                </p>
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
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Seller / Store</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden lg:table-cell">Product</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden xl:table-cell">Purchased by</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Qty</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Total</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-gray-50/60 transition-colors">

                                <td class="px-5 py-3.5 align-top">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-mono font-medium">
                                        {{ $purchase->reference }}
                                    </span>
                                    @if($purchase->cash_memo)
                                        <p class="text-xs text-gray-400 mt-1">{{ $purchase->cash_memo }}</p>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <p class="font-medium text-gray-800">{{ $purchase->seller_store_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ optional($purchase->date)->format('d M Y') }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top hidden lg:table-cell">
                                    <p class="font-medium text-gray-800">{{ $purchase->product_name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $purchase->product_code ?: '—' }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top text-gray-500 hidden xl:table-cell">
                                    {{ $purchase->purchased_by }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-gray-700 tabular-nums">
                                    {{ number_format($purchase->qty, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-green-600 tabular-nums hidden md:table-cell">
                                    ৳{{ number_format($purchase->grand_total, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'inline-flex items-center w-fit px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-50 text-green-700'  => $purchase->purchase_status === 'received',
                                            'bg-amber-50 text-amber-700'  => $purchase->purchase_status === 'partial',
                                            'bg-gray-100 text-gray-600'   => $purchase->purchase_status === 'pending',
                                            'bg-blue-50 text-blue-700'    => $purchase->purchase_status === 'ordered',
                                        ])>{{ ucfirst($purchase->purchase_status) }}</span>

                                        <span @class([
                                            'inline-flex items-center w-fit px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-50 text-green-700'  => $purchase->payment_status === 'paid',
                                            'bg-amber-50 text-amber-700'  => $purchase->payment_status === 'partial',
                                            'bg-red-50 text-red-700'      => $purchase->payment_status === 'due',
                                        ])>{{ ucfirst($purchase->payment_status) }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                           class="inline-flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium
                                                  text-blue-700 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                                              onsubmit="return confirm('Delete purchase {{ addslashes($purchase->reference) }}?')">
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
                                <td colspan="8" class="px-5 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                                            <path d="M16 3v4M8 3v4M2 11h20"/>
                                        </svg>
                                        <p class="text-sm">No purchases found.
                                            <a href="{{ route('purchases.create') }}" class="text-blue-600 hover:underline">Add your first purchase.</a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchases->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>

        @if($purchases->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $purchases->firstItem() }}–{{ $purchases->lastItem() }}
                of {{ number_format($purchases->total()) }} purchases
            </p>
        @endif

    </div>
</x-app-layout>