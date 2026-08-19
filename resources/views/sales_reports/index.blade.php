<x-app-layout>
    <x-slot name="header">Product Sales Report</x-slot>

    @php
        $fmtQty = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.') ?: '0';
        $sortUrl = function (string $column) use ($filters) {
            $dir = $filters['sort'] === $column && $filters['dir'] === 'desc' ? 'asc' : 'desc';
            if ($column === 'product_name' && $filters['sort'] !== 'product_name') {
                $dir = 'asc';
            }

            return route('sales-reports.index', array_merge(request()->query(), ['sort' => $column, 'dir' => $dir]));
        };
        $rangeUrl = fn (string $range) => route('sales-reports.index', array_merge(
            request()->except(['date_from', 'date_to', 'page']),
            ['range' => $range]
        ));
        $exportQuery = request()->query();
        $ranges = [
            'today' => 'Today',
            '7d' => '7 Days',
            'month' => 'This Month',
            'year' => 'This Year',
            'all' => 'All Time',
        ];
    @endphp

    <div class="space-y-5">
        {{-- Hero --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#130f55] via-[#3726b0] to-[#2563eb] text-white p-5 sm:p-6">
            <div class="absolute -right-10 -top-12 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute right-16 -bottom-16 w-48 h-48 rounded-full bg-violet-300/20 blur-3xl"></div>
            <div class="relative flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.22em] text-violet-200/90 font-semibold">Sales Intelligence</p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-semibold tracking-tight">Product Sales Report</h2>
                    <p class="mt-2 text-sm text-white/75">
                        {{ $periodLabel }} · {{ $shopLabel }} · {{ number_format($totals->products_count) }} products
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('sales-reports.export.csv', $exportQuery) }}"
                        class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-400/15 text-emerald-100 border border-emerald-300/30 text-sm font-medium hover:bg-emerald-400/25 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                        Download CSV
                    </a>
                    <a href="{{ route('sales-reports.export.pdf', $exportQuery) }}"
                        class="h-10 px-4 inline-flex items-center justify-center gap-2 rounded-lg bg-white text-[#1e3a5f] text-sm font-semibold hover:bg-violet-50 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"/><path stroke-linecap="round" d="M14 3v6h6"/></svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <form method="GET" action="{{ route('sales-reports.index') }}">
                <input type="hidden" name="range" value="custom">
                <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                <input type="hidden" name="dir" value="{{ $filters['dir'] }}">

                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($ranges as $key => $label)
                        <a href="{{ $rangeUrl($key) }}"
                            class="h-8 px-3 inline-flex items-center rounded-full text-xs font-semibold border transition {{ $filters['range'] === $key ? 'bg-[#3726b0] text-white border-[#3726b0]' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2.5 mb-3.5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Search product</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                            </span>
                            <input type="text" name="search" value="{{ $filters['search'] }}"
                                placeholder="Name, design code or product code"
                                class="h-10 w-full pl-9 pr-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">From</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">To</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-2.5 mb-3.5">
                    @if(auth()->user()->canManageAllShops())
                        <div>
                            <label class="block text-xs text-gray-400 mb-1 ml-0.5">Shop</label>
                            <select name="shop_id" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                                <option value="">All shops</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" @selected($filters['shop_id'] == $shop->id)>{{ $shop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Payment</label>
                        <select name="payment_status" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            <option value="">All payments</option>
                            <option value="paid" @selected($filters['payment_status'] == 'paid')>Paid</option>
                            <option value="due" @selected($filters['payment_status'] == 'due')>Due</option>
                            <option value="partial" @selected($filters['payment_status'] == 'partial')>Partial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Sale status</label>
                        <select name="sale_status" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            <option value="">All statuses</option>
                            <option value="success" @selected($filters['sale_status'] == 'success')>Success</option>
                            <option value="returned" @selected($filters['sale_status'] == 'returned')>Returned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Products</label>
                        <select name="activity" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            <option value="sold" @selected($filters['activity'] === 'sold')>With sales / returns</option>
                            <option value="all" @selected($filters['activity'] === 'all')>All products</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Per page</label>
                        <select name="per_page" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            @foreach([25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected($filters['per_page'] == $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" class="h-10 px-5 bg-gray-800 text-white rounded-lg text-sm font-medium w-full sm:w-auto">
                        Apply filters
                    </button>
                    <a href="{{ route('sales-reports.index') }}"
                        class="h-10 px-5 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center w-full sm:w-auto">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            <div class="relative overflow-hidden rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-emerald-400/15"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700/80">Total Sales Amount</p>
                        <p class="mt-1 text-2xl font-semibold text-emerald-700 break-words">৳{{ number_format($totals->sales_amount, 2) }}</p>
                        <p class="mt-1 text-xs text-emerald-700/70">Gross product line totals</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-indigo-400/15"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-indigo-700/80">Total Sold Qty</p>
                        <p class="mt-1 text-2xl font-semibold text-indigo-700">{{ $fmtQty($totals->sold_qty) }}</p>
                        <p class="mt-1 text-xs text-indigo-700/70">{{ number_format($totals->sales_count) }} sales in range</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-5">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-rose-400/15"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-rose-700/80">Total Return Qty</p>
                        <p class="mt-1 text-2xl font-semibold text-rose-600">{{ $fmtQty($totals->return_qty) }}</p>
                        <p class="mt-1 text-xs text-rose-700/70">Approved returns · {{ number_format($totals->return_rate, 1) }}% of sold qty</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h7V3m11 11h-7v7M5 5l14 14"/></svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-amber-400/15"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-amber-800/80">Return Amount</p>
                        <p class="mt-1 text-2xl font-semibold text-amber-700 break-words">৳{{ number_format($totals->return_amount, 2) }}</p>
                        <p class="mt-1 text-xs text-amber-800/70">Refunded product value</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3-3m-3 3l3 3"/></svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-5">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-violet-400/15"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-violet-700/80">Net Sales Amount</p>
                        <p class="mt-1 text-2xl font-semibold text-violet-700 break-words">৳{{ number_format($totals->net_amount, 2) }}</p>
                        <p class="mt-1 text-xs text-violet-700/70">Sales minus approved returns</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-violet-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8"/></svg>
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-full bg-sky-400/15"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-sky-700/80">Net Qty</p>
                        <p class="mt-1 text-2xl font-semibold text-sky-700">{{ $fmtQty($totals->net_qty) }}</p>
                        <p class="mt-1 text-xs text-sky-700/70">{{ number_format($totals->products_count) }} products in this report</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-sky-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6h13M9 7h13M4 7h.01M4 17h.01"/></svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @forelse($rows as $index => $row)
                <div class="bg-white border border-gray-200 rounded-2xl p-4 space-y-3 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-800 break-words">{{ $row->product_name }}</p>
                            <p class="mt-1 text-xs font-mono text-gray-500">{{ $row->sku ?: '-' }} @if($row->product_code)· {{ $row->product_code }}@endif</p>
                        </div>
                        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-semibold">
                            #{{ $rows->firstItem() + $index }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="rounded-xl bg-indigo-50 p-2.5">
                            <p class="text-indigo-500">Sold qty</p>
                            <p class="mt-0.5 font-semibold text-indigo-700">{{ $fmtQty($row->sold_qty) }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 p-2.5">
                            <p class="text-rose-500">Return qty</p>
                            <p class="mt-0.5 font-semibold text-rose-600">{{ $fmtQty($row->return_qty) }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-2.5 col-span-2">
                            <p class="text-emerald-600">Sales amount</p>
                            <p class="mt-0.5 font-semibold text-emerald-700">৳{{ number_format($row->sales_amount, 2) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ (int) $row->sales_count }} sales · Net ৳{{ number_format($row->net_amount, 2) }}</span>
                        <span>Return {{ number_format($row->return_rate, 1) }}%</span>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center text-sm text-gray-500">
                    No product sales found for the selected filters.
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Product performance</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Sorted by {{ \App\Services\ProductSalesReport::SORTS[$filters['sort']] }} · {{ $filters['dir'] === 'asc' ? 'ascending' : 'descending' }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-[#1e3a5f] to-[#2563eb] text-white">
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide">#</th>
                            @foreach([
                                'product_name' => 'Product',
                                'sales_count' => 'Sales Count',
                                'sold_qty' => 'Sold Qty',
                                'return_qty' => 'Return Qty',
                                'net_qty' => 'Net Qty',
                                'sales_amount' => 'Sales Amount',
                                'return_amount' => 'Return Amount',
                                'net_amount' => 'Net Amount',
                                'return_rate' => 'Return %',
                                'stock_qty' => 'Stock',
                            ] as $column => $label)
                                <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wide {{ $column === 'product_name' ? 'text-left' : 'text-right' }}">
                                    <a href="{{ $sortUrl($column) }}" class="inline-flex items-center gap-1 hover:text-violet-100">
                                        {{ $label }}
                                        @if($filters['sort'] === $column)
                                            <span>{{ $filters['dir'] === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($rows as $index => $row)
                            <tr class="hover:bg-slate-50/80 {{ $index % 2 === 1 ? 'bg-slate-50/40' : 'bg-white' }}">
                                <td class="px-4 py-3 text-gray-400">{{ $rows->firstItem() + $index }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800">{{ $row->product_name }}</div>
                                    <div class="text-[11px] font-mono text-gray-400 mt-0.5">
                                        {{ $row->sku ?: '-' }}
                                        @if($row->product_code)
                                            · {{ $row->product_code }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ (int) $row->sales_count }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-indigo-600">{{ $fmtQty($row->sold_qty) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600">{{ $fmtQty($row->return_qty) }}</td>
                                <td class="px-4 py-3 text-right text-sky-700">{{ $fmtQty($row->net_qty) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-600">৳{{ number_format($row->sales_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-amber-700">৳{{ number_format($row->return_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-violet-700">৳{{ number_format($row->net_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex min-w-[3.25rem] justify-end px-2 py-0.5 rounded-md text-xs font-semibold {{ $row->return_rate > 20 ? 'bg-rose-50 text-rose-700' : ($row->return_rate > 0 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">
                                        {{ number_format($row->return_rate, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600">{{ $fmtQty($row->stock_qty) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-12 text-center text-sm text-gray-500">
                                    No product sales found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rows->isNotEmpty())
                        <tfoot>
                            <tr class="bg-slate-900 text-white font-semibold">
                                <td class="px-4 py-3" colspan="2">Report total</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals->sales_count) }}</td>
                                <td class="px-4 py-3 text-right text-indigo-200">{{ $fmtQty($totals->sold_qty) }}</td>
                                <td class="px-4 py-3 text-right text-rose-200">{{ $fmtQty($totals->return_qty) }}</td>
                                <td class="px-4 py-3 text-right text-sky-200">{{ $fmtQty($totals->net_qty) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-200">৳{{ number_format($totals->sales_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-amber-200">৳{{ number_format($totals->return_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right text-violet-200">৳{{ number_format($totals->net_amount, 2) }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals->return_rate, 1) }}%</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            @if($rows->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>

        @if($rows->hasPages())
            <div class="sm:hidden">
                {{ $rows->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
