<x-app-layout>
    <x-slot name="header">Sales</x-slot>

    <div class="space-y-4">
        @if (session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('sales.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Reference, customer, cash memo..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">

                    <select name="payment_status"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                        <option value="">Payment status</option>
                        <option value="due" @selected(request('payment_status') == 'due')>Due</option>
                        <option value="paid" @selected(request('payment_status') == 'paid')>Paid</option>
                        <option value="partial" @selected(request('payment_status') == 'partial')>Partial</option>
                    </select>

                    <select name="status"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                        <option value="">Sale status</option>
                        <option value="success" @selected(request('status') == 'success')>Success</option>
                        <option value="returned" @selected(request('status') == 'returned')>Returned</option>
                    </select>

                    @if(auth()->user()->canManageAllShops())
                        <select name="shop_id" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            <option value="">All shops</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" @selected(request('shop_id') == $shop->id)>{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <button type="submit"
                            class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm w-full sm:w-auto">
                            Filter
                        </button>
                        <a href="{{ route('sales.index') }}"
                            class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center w-full sm:w-auto">
                            Reset
                        </a>
                    </div>
                    <div class="sm:ml-auto flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <a href="{{ route('sales.export', request()->query()) }}"
                            class="h-10 px-4 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm inline-flex items-center justify-center gap-1 w-full sm:w-auto">
                            ⬇ CSV
                        </a>
                        <a href="{{ route('sales.create') }}"
                            class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center gap-1 w-full sm:w-auto">
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
                    ৳{{ number_format($totals->total_amount ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Paid</p>
                <p class="text-xl font-semibold text-green-600 break-words">
                    ৳{{ number_format($totals->total_paid ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Due</p>
                <p class="text-xl font-semibold text-red-600 break-words">
                    ৳{{ number_format($totals->total_due ?? 0, 2) }}</p>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @forelse($sales as $sale)
                @php
                    $phone = $sale->customer?->phone ? preg_replace('/[^0-9]/', '', $sale->customer->phone) : null;
                    $waMessage = urlencode(
                        'Hello ' . ($sale->customer?->full_name ?? 'Customer') .
                        ', your invoice ' . $sale->reference .
                        '. Total: ৳' . number_format($sale->grand_total, 2) .
                        ', Paid: ৳' . number_format($sale->paid, 2) .
                        ', Due: ৳' . number_format($sale->due, 2)
                    );
                @endphp

                <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('sales.show', $sale) }}"
                                class="inline-flex items-center px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono break-all">
                                {{ $sale->reference }}
                            </a>
                            <div class="text-xs text-gray-400 mt-1">{{ $sale->created_at->format('d M Y') }}</div>
                        </div>

                        <details class="relative shrink-0">
                            <summary class="list-none cursor-pointer h-9 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100">
                                Actions
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </summary>

                            <div class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-30 overflow-hidden">
                                @if($phone)
                                    <a href="https://wa.me/{{ $phone }}?text={{ $waMessage }}" target="_blank"
                                       class="flex items-center gap-2 px-3 py-2 text-xs text-green-700 hover:bg-green-50">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 32 32">
                                            <path d="M16.04 3C9.41 3 4.02 8.39 4.02 15.02c0 2.12.56 4.19 1.62 6.01L4 29l8.18-1.61a12.02 12.02 0 0 0 3.86.64h.01c6.63 0 12.02-5.39 12.02-12.02C28.07 8.39 22.67 3 16.04 3zm0 22.84h-.01c-1.23 0-2.44-.22-3.58-.66l-.26-.1-4.86.96.98-4.73-.17-.28a9.84 9.84 0 0 1-1.51-5.22c0-5.42 4.41-9.83 9.84-9.83 2.63 0 5.1 1.03 6.96 2.89a9.77 9.77 0 0 1 2.88 6.95c0 5.42-4.41 9.83-9.84 9.83zm5.39-7.36c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.18.2-.35.22-.65.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.03-1.05 2.51s1.07 2.9 1.22 3.1c.15.2 2.11 3.22 5.11 4.52.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/>
                                        </svg>
                                        WhatsApp
                                    </a>
                                @endif

                                <a href="{{ route('sales.edit', $sale) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-blue-700 hover:bg-blue-50">Edit</a>
                                <a href="{{ route('sales.invoice', $sale) }}" target="_blank" class="flex items-center gap-2 px-3 py-2 text-xs text-purple-700 hover:bg-purple-50">PDF</a>
                                <a href="{{ route('sales.invoice', $sale) }}?print=1" target="_blank" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Print</a>

                                <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Delete sale?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="w-full text-left px-3 py-2 text-xs text-red-700 hover:bg-red-50">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </details>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 mb-1">Customer</p>
                        <p class="text-sm font-medium text-gray-800 break-words">
                            {{ $sale->customer?->full_name ?? '—' }}</p>
                    </div>

                    @if ($sale->bell_no || $sale->cash_memo)
                        <div class="flex flex-wrap gap-2 text-xs">
                            @if ($sale->cash_memo)
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded font-mono">Memo:
                                    {{ $sale->cash_memo }}</span>
                            @endif
                            @if ($sale->bell_no)
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 rounded font-mono">Bell:
                                    {{ $sale->bell_no }}</span>
                            @endif
                        </div>
                    @endif

                    <div>
                        <p class="text-xs text-gray-400 mb-1">Products</p>
                        <div class="space-y-1.5">
                            @foreach ($sale->items->take(2) as $item)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="text-gray-700 break-words min-w-0">
                                        {{ $item->product->product_name }}
                                        <span class="text-gray-400">×{{ $item->qty }}</span>
                                    </span>
                                    <span class="shrink-0 text-gray-500 font-mono">৳{{ number_format($item->price_on_sale, 2) }}/pc</span>
                                </div>
                            @endforeach
                            @if ($sale->items->count() > 2)
                                <div class="text-xs text-gray-400">+{{ $sale->items->count() - 2 }} more</div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Total</p>
                            <p class="mt-1 font-medium text-blue-600 break-words">৳{{ number_format($sale->grand_total, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Paid</p>
                            <p class="mt-1 font-medium text-green-600 break-words">৳{{ number_format($sale->paid, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Due</p>
                            <p class="mt-1 font-medium text-red-600 break-words">৳{{ number_format($sale->due, 2) }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span @class([
                            'px-2 py-1 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $sale->payment_status === 'paid',
                            'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                            'bg-red-50 text-red-700' => $sale->payment_status === 'due',
                        ])>{{ ucfirst($sale->payment_status) }}</span>

                        <span @class([
                            'px-2 py-1 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $sale->status === 'success',
                            'bg-orange-50 text-orange-700' => $sale->status === 'returned',
                            'bg-gray-100 text-gray-600' => blank($sale->status),
                        ])>{{ $sale->status ? ucfirst($sale->status) : '—' }}</span>
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
        <div class="hidden sm:block bg-white border border-gray-200 rounded-xl overflow-visible">
            <div class="overflow-x-auto overflow-y-visible">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Shop</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Products</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Grand Total</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Refs</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Payment</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sales as $sale)
                            @php
                                $phone = $sale->customer?->phone ? preg_replace('/[^0-9]/', '', $sale->customer->phone) : null;
                                $waMessage = urlencode(
                                    'Hello ' . ($sale->customer?->full_name ?? 'Customer') .
                                    ', your invoice ' . $sale->reference .
                                    '. Total: ৳' . number_format($sale->grand_total, 2) .
                                    ', Paid: ৳' . number_format($sale->paid, 2) .
                                    ', Due: ৳' . number_format($sale->due, 2)
                                );
                            @endphp

                            <tr class="hover:bg-gray-50/60">
                                <td class="px-4 py-3">
                                    <a href="{{ route('sales.show', $sale) }}"
                                        class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">
                                        {{ $sale->reference }}
                                    </a>
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-600">
                                    {{ $sale->shop?->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $sale->customer?->full_name ?? '—' }}<br>
                                    <span class="text-xs text-gray-400">{{ $sale->created_at->format('d M Y') }}</span>
                                </td>

                                <td class="px-4 py-3 text-xs text-gray-600">
                                    @foreach ($sale->items->take(2) as $item)
                                        <div>{{ $item->product->product_name }} <span class="text-gray-400">(×{{ $item->qty }})</span></div>
                                    @endforeach
                                    @if ($sale->items->count() > 2)
                                        <div class="text-gray-400">+{{ $sale->items->count() - 2 }} more</div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right text-xs">
                                    @foreach ($sale->items->take(2) as $item)
                                        <div class="text-gray-700 font-mono">
                                            ৳{{ number_format($item->price_on_sale, 2) }}</div>
                                    @endforeach
                                    @if ($sale->items->count() > 2)
                                        <div class="text-gray-300">—</div>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right font-medium text-blue-600">
                                    ৳{{ number_format($sale->grand_total, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right text-green-600">
                                    ৳{{ number_format($sale->paid, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right text-red-600">
                                    ৳{{ number_format($sale->due, 2) }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-0.5 text-xs">
                                        @if ($sale->cash_memo)
                                            <span class="text-gray-500 font-mono" title="Cash Memo">Cash Memo:
                                                {{ $sale->cash_memo }}</span>
                                        @endif
                                        @if ($sale->bell_no)
                                            <span class="text-amber-600 font-mono" title="Bell No">Bell No:
                                                {{ $sale->bell_no }}</span>
                                        @endif
                                        @if (!$sale->cash_memo && !$sale->bell_no)
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-50 text-green-700' => $sale->payment_status === 'paid',
                                        'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                                        'bg-red-50 text-red-700' => $sale->payment_status === 'due',
                                    ])>{{ ucfirst($sale->payment_status) }}</span>
                                </td>

                                <td class="px-4 py-3">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-50 text-green-700' => $sale->status === 'success',
                                        'bg-orange-50 text-orange-700' => $sale->status === 'returned',
                                        'bg-gray-100 text-gray-600' => blank($sale->status),
                                    ])>{{ $sale->status ? ucfirst($sale->status) : '—' }}</span>
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <details class="relative inline-block text-left">
                                        <summary class="list-none cursor-pointer h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                                            Actions
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path d="m6 9 6 6 6-6"/>
                                            </svg>
                                        </summary>

                                        <div class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden">
                                            @if($phone)
                                                <a href="https://wa.me/{{ $phone }}?text={{ $waMessage }}" target="_blank"
                                                   class="flex items-center gap-2 px-3 py-2 text-xs text-green-700 hover:bg-green-50">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 32 32">
                                                        <path d="M16.04 3C9.41 3 4.02 8.39 4.02 15.02c0 2.12.56 4.19 1.62 6.01L4 29l8.18-1.61a12.02 12.02 0 0 0 3.86.64h.01c6.63 0 12.02-5.39 12.02-12.02C28.07 8.39 22.67 3 16.04 3zm0 22.84h-.01c-1.23 0-2.44-.22-3.58-.66l-.26-.1-4.86.96.98-4.73-.17-.28a9.84 9.84 0 0 1-1.51-5.22c0-5.42 4.41-9.83 9.84-9.83 2.63 0 5.1 1.03 6.96 2.89a9.77 9.77 0 0 1 2.88 6.95c0 5.42-4.41 9.83-9.84 9.83zm5.39-7.36c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.18.2-.35.22-.65.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.18.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.05 1.03-1.05 2.51s1.07 2.9 1.22 3.1c.15.2 2.11 3.22 5.11 4.52.71.31 1.27.49 1.7.63.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/>
                                                    </svg>
                                                    WhatsApp
                                                </a>
                                            @endif

                                            <a href="{{ route('sales.edit', $sale) }}"
                                               class="flex items-center gap-2 px-3 py-2 text-xs text-blue-700 hover:bg-blue-50">
                                                Edit
                                            </a>

                                            <a href="{{ route('sales.invoice', $sale) }}" target="_blank"
                                               class="flex items-center gap-2 px-3 py-2 text-xs text-purple-700 hover:bg-purple-50">
                                                PDF Invoice
                                            </a>

                                            <a href="{{ route('sales.invoice', $sale) }}?print=1" target="_blank"
                                               class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">
                                                Print
                                            </a>

                                            <form method="POST" action="{{ route('sales.destroy', $sale) }}"
                                                  onsubmit="return confirm('Delete sale?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="w-full text-left px-3 py-2 text-xs text-red-700 hover:bg-red-50">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-5 py-20 text-center text-gray-400">
                                    No sales found.
                                    <a href="{{ route('sales.create') }}"
                                        class="text-blue-600 hover:underline">Create first sale</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sales->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
