<x-app-layout>
    <x-slot name="header">Purchases</x-slot>

    <div class="space-y-4">

        {{-- Top bar --}}
        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-3">

            <form method="GET" action="{{ route('purchases.index') }}"
                  class="w-full space-y-3">

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Product, code, ref, memo..."
                               class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
                    </div>

                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                           class="w-full px-3.5 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>

                    <input type="text" name="seller_store_name" value="{{ $filters['seller_store_name'] ?? '' }}"
                           placeholder="Seller / Store"
                           class="w-full px-3.5 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>

                    <input type="text" name="purchased_by" value="{{ $filters['purchased_by'] ?? '' }}"
                           placeholder="Purchased by"
                           class="w-full px-3.5 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>

                    <select name="purchase_status"
                            class="w-full px-3.5 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Purchase Status</option>
                        <option value="received" {{ ($filters['purchase_status'] ?? '') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="partial" {{ ($filters['purchase_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="pending" {{ ($filters['purchase_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="ordered" {{ ($filters['purchase_status'] ?? '') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                    </select>

                    <select name="payment_status"
                            class="w-full px-3.5 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Payment Status</option>
                        <option value="due" {{ ($filters['payment_status'] ?? '') === 'due' ? 'selected' : '' }}>Due</option>
                        <option value="paid" {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-lg
                                   text-gray-700 hover:bg-gray-50 transition">
                        Filter
                    </button>

                    <a href="{{ route('purchases.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Reset
                    </a>

                    <a href="{{ route('purchases.export.csv', request()->query()) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-green-700
                              bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16"/>
                        </svg>
                        Download CSV
                    </a>

                    <a href="{{ route('purchases.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white
                              bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Purchase
                    </a>
                </div>
            </form>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Purchases</p>
                <p class="text-2xl font-semibold text-gray-800">{{ number_format($totals->total_purchases ?? 0) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Qty</p>
                <p class="text-2xl font-semibold text-gray-800">{{ number_format($totals->total_qty ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Subtotal</p>
                <p class="text-2xl font-semibold text-gray-800">৳{{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Grand Total</p>
                <p class="text-2xl font-semibold text-green-600">৳{{ number_format($totals->total_amount ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="text-left px-5 py-3 font-medium text-gray-500 whitespace-nowrap">Reference</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Seller / Store</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Product</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden xl:table-cell">Purchased By</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Qty</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500 hidden md:table-cell">Total</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Status</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3 align-top">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-mono font-medium">
                                            {{ $purchase->reference }}
                                        </span>
                                        @if($purchase->cash_memo)
                                            <p class="text-xs text-gray-400">Memo: {{ $purchase->cash_memo }}</p>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-3 align-top">
                                    <div class="font-medium text-gray-800">{{ $purchase->seller_store_name }}</div>
                                    <p class="text-xs text-gray-500 mt-1">{{ optional($purchase->date)->format('d M Y') }}</p>
                                </td>

                                <td class="px-5 py-3 align-top hidden lg:table-cell">
                                    <div class="font-medium text-gray-800">{{ $purchase->product_name }}</div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $purchase->product_code ?: '—' }}</p>
                                </td>

                                <td class="px-5 py-3 text-gray-600 hidden xl:table-cell align-top">
                                    {{ $purchase->purchased_by }}
                                </td>

                                <td class="px-5 py-3 text-right text-gray-700 align-top">
                                    {{ number_format($purchase->qty, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-green-600 hidden md:table-cell align-top">
                                    ৳{{ number_format($purchase->grand_total, 2) }}
                                </td>

                                <td class="px-5 py-3 align-top">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $purchase->purchase_status === 'received' ? 'bg-green-50 text-green-700' : '' }}
                                            {{ $purchase->purchase_status === 'partial' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                            {{ $purchase->purchase_status === 'pending' ? 'bg-gray-100 text-gray-700' : '' }}
                                            {{ $purchase->purchase_status === 'ordered' ? 'bg-blue-50 text-blue-700' : '' }}">
                                            {{ ucfirst($purchase->purchase_status) }}
                                        </span>

                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $purchase->payment_status === 'paid' ? 'bg-green-50 text-green-700' : '' }}
                                            {{ $purchase->payment_status === 'partial' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                            {{ $purchase->payment_status === 'due' ? 'bg-red-50 text-red-700' : '' }}">
                                            {{ ucfirst($purchase->payment_status) }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-5 py-3 align-top">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                                  text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                                              onsubmit="return confirm('Delete purchase {{ addslashes($purchase->reference) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                                           text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                                            <path d="M16 3v4M8 3v4M2 11h20"/>
                                        </svg>
                                        <p class="text-sm">
                                            No purchases found.
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
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>

        @if($purchases->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $purchases->firstItem() }}–{{ $purchases->lastItem() }}
                of {{ $purchases->total() }} purchases
            </p>
        @endif

    </div>
</x-app-layout>