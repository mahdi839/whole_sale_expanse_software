<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="space-y-5">

        {{-- ── Filters ─────────────────────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3">
                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">From</label>
                        <input type="date" name="date_from" value="{{ $filters['dateFrom'] }}"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">To</label>
                        <input type="date" name="date_to" value="{{ $filters['dateTo'] }}"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Payment Status</label>
                        <select name="payment_status"
                            class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            <option value="">All payments</option>
                            <option value="paid" @selected($filters['paymentStatus'] == 'paid')>Paid</option>
                            <option value="due" @selected($filters['paymentStatus'] == 'due')>Due</option>
                            <option value="partial" @selected($filters['paymentStatus'] == 'partial')>Partial</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                    <button type="submit" class="h-10 px-5 bg-gray-800 text-white rounded-lg text-sm w-full sm:w-auto">
                        Apply
                    </button>

                    <a href="{{ route('dashboard') }}"
                        class="h-10 px-5 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center w-full sm:w-auto">
                        Reset
                    </a>

                    {{-- Active filter badges --}}
                    @if ($filters['dateFrom'] || $filters['dateTo'] || $filters['paymentStatus'])
                        <div class="flex flex-wrap items-center gap-2 sm:ml-2">
                            @if ($filters['dateFrom'] || $filters['dateTo'])
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full text-xs font-medium">
                                    📅
                                    {{ $filters['dateFrom'] ? \Carbon\Carbon::parse($filters['dateFrom'])->format('d M Y') : '…' }}
                                    →
                                    {{ $filters['dateTo'] ? \Carbon\Carbon::parse($filters['dateTo'])->format('d M Y') : 'today' }}
                                </span>
                            @endif
                            @if ($filters['paymentStatus'])
                                <span
                                    class="inline-flex items-center gap-1 px-2.5 py-1 bg-violet-50 text-violet-700 border border-violet-200 rounded-full text-xs font-medium">
                                    {{ ucfirst($filters['paymentStatus']) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </form>
        </div>

        {{-- ── Stat cards row 1 ────────────────────────────────────── --}}
        <div @class([
            'grid grid-cols-2 gap-3',
            'sm:grid-cols-3 lg:grid-cols-6' => $canViewProfit,
            'sm:grid-cols-3 lg:grid-cols-5' => ! $canViewProfit,
        ])>

            @if($canViewProfit)
                <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-50 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" stroke-width="1.8"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h7v7" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Net Profit</p>

                        <p @class([
                            'text-xl font-medium mt-0.5',
                            'text-teal-600' => $stats['net_profit'] >= 0,
                            'text-red-600' => $stats['net_profit'] < 0,
                        ])>
                            ৳{{ number_format($stats['net_profit'], 2) }}
                        </p>

                        <p class="text-xs text-gray-400 mt-0.5">
                            Net sales - COGS - expenses - salaries - advances
                        </p>
                    </div>
                </div>
            @endif

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Products</p>
                    <p class="text-xl font-medium mt-0.5">{{ number_format($stats['total_products']) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">In catalogue</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7l9-4 9 4-9 4-9-4z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l9 4 9-4M3 17l9 4 9-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Stock Value</p>
                    <p class="text-xl font-medium mt-0.5 text-indigo-600">
                        {{ number_format($stats['total_stock_cost_value'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        At purchase price · {{ number_format($stats['total_stock_qty']) }} pcs · {{ $stats['stock_scope_label'] }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Retail: {{ number_format($stats['total_stock_retail_value'], 2) }}
                    </p>
                    @if ($stats['pending_stock_qty'] > 0)
                        <p class="text-xs text-amber-600 mt-0.5">
                            Pending: {{ number_format($stats['pending_stock_cost_value'], 2) }} · {{ number_format($stats['pending_stock_qty']) }} pcs
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Expenses</p>
                    <p class="text-xl font-medium mt-0.5 text-rose-600">
                        ৳{{ number_format($stats['total_expenses'], 2) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $filters['dateFrom'] || $filters['dateTo'] ? 'Filtered period' : 'All time' }}
                    </p>
                    @if(($stats['total_salaries'] ?? 0) > 0)
                        <p class="text-xs text-gray-400 mt-0.5">
                            Salaries: ৳{{ number_format($stats['total_salaries'], 2) }}
                        </p>
                    @endif
                    @if(($stats['total_salary_advances'] ?? 0) > 0)
                        <p class="text-xs text-gray-400 mt-0.5">
                            Advances: ৳{{ number_format($stats['total_salary_advances'], 2) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h13M10 19a1 1 0 100 2 1 1 0 000-2zm7 0a1 1 0 100 2 1 1 0 000-2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Total Sales</p>
                    <p class="text-xl font-medium mt-0.5 text-emerald-600">
                        ৳{{ number_format($stats['total_sales'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $filters['dateFrom'] || $filters['dateTo'] ? 'Filtered period' : 'All time' }}</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Sales Due</p>
                    <p class="text-xl font-medium mt-0.5 text-red-500">
                        ৳{{ number_format($stats['total_sales_due'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Unpaid balance</p>
                </div>
            </div>

        </div>

        {{-- ── Stat cards row 2 ────────────────────────────────────── --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m6-4.13a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Customers</p>
                    <p class="text-xl font-medium mt-0.5 text-blue-600">{{ number_format($stats['total_customers']) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Registered</p>
                </div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Sale Returns</p>
                    <p class="text-xl font-medium mt-0.5 text-amber-600">
                        ৳{{ number_format($stats['total_sale_returns'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Approved only</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6m16 0H4" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Purchases</p>
                    <p class="text-xl font-medium mt-0.5 text-violet-600">
                        ৳{{ number_format($stats['total_purchases'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $filters['dateFrom'] || $filters['dateTo'] ? 'Filtered period' : 'All time' }}</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Purchase Returns</p>
                    <p class="text-xl font-medium mt-0.5 text-amber-600">
                        ৳{{ number_format($stats['total_purchase_returns'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Approved only</p>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="1.8"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4 4-4m6 8l4-4-4-4" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide leading-tight">Purchase Due</p>
                    <p class="text-xl font-medium mt-0.5 text-red-500">
                        ৳{{ number_format($stats['total_purchase_due'], 2) }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Owed to suppliers</p>
                </div>
            </div>

        </div>

        {{-- ── Sales chart + Low stock ──────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">
                    Sales —
                    @if ($filters['dateFrom'] || $filters['dateTo'])
                        {{ $filters['dateFrom'] ? \Carbon\Carbon::parse($filters['dateFrom'])->format('d M Y') : '…' }}
                        →
                        {{ $filters['dateTo'] ? \Carbon\Carbon::parse($filters['dateTo'])->format('d M Y') : 'today' }}
                    @else
                        last 7 days
                    @endif
                </p>
                <div style="position:relative;height:180px">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Low stock alerts</p>
                @forelse($lowStock as $s)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                        <span class="text-sm text-gray-700 truncate max-w-[140px]">
                            {{ $s->product?->product_name ?? 'Unknown' }}
                        </span>
                        <span @class([
                            'px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-red-50 text-red-700' => $s->stock_qty <= 3,
                            'bg-amber-50 text-amber-700' => $s->stock_qty > 3,
                        ])>{{ $s->stock_qty }} left</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">All stock levels healthy</p>
                @endforelse
            </div>
        </div>

        {{-- ── Top products + Top customers ────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Top 10 products --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Top 10 selling products</p>
                @php
                    $maxQty = $topProducts->max('total_qty');
                    $maxQty = $maxQty > 0 ? (float) $maxQty : 1;
                @endphp
                <div class="space-y-2.5">
                    @forelse($topProducts as $p)
                        @php
                            $qtyFloat = (float) $p->total_qty;
                            $barWidth = $maxQty > 0 ? round(($qtyFloat / $maxQty) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-28 text-right text-gray-500 truncate text-xs shrink-0">
                                {{ $p->product_name }}
                            </span>
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div class="bg-teal-500 h-2 rounded-full" style="width:{{ $barWidth }}%"></div>
                            </div>
                            <span class="w-10 text-right text-xs text-gray-500 shrink-0">
                                {{ number_format($qtyFloat) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No sales data yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Top customers --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-4">Top customers by sales</p>
                @php
                    $maxSale = $topCustomers->max('total_sale');
                    $maxSale = $maxSale > 0 ? (float) $maxSale : 1;
                @endphp
                <div class="space-y-2.5">
                    @forelse($topCustomers as $c)
                        @php
                            $saleFloat = (float) $c->total_sale;
                            $barWidth = $maxSale > 0 ? round(($saleFloat / $maxSale) * 100) : 0;
                        @endphp
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-28 text-right text-gray-500 truncate text-xs shrink-0">
                                {{ $c->full_name }}
                            </span>
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width:{{ $barWidth }}%"></div>
                            </div>
                            <span class="w-16 text-right text-xs text-gray-500 shrink-0">
                                ৳{{ $saleFloat >= 1000 ? number_format($saleFloat / 1000, 1) . 'k' : number_format($saleFloat, 0) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">No customer data yet.</p>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ── Recent 10 sales table ────────────────────────────────── --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400 uppercase tracking-wide">Recent sales</p>
                <a href="{{ route('sales.index') }}" class="text-xs text-blue-600 hover:underline">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Items</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Payment</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentSales as $sale)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3">
                                    <a href="{{ route('sales.show', $sale) }}"
                                        class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">
                                        {{ $sale->reference }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-gray-700">
                                    {{ $sale->customer?->full_name ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-gray-500">
                                    {{ $sale->items->count() }} item{{ $sale->items->count() !== 1 ? 's' : '' }}
                                </td>
                                <td class="px-5 py-3 text-right font-medium text-emerald-600">
                                    ৳{{ number_format($sale->grand_total, 2) }}
                                </td>
                                <td class="px-5 py-3 text-right text-blue-600">
                                    ৳{{ number_format($sale->paid, 2) }}
                                </td>
                                <td class="px-5 py-3 text-right text-red-500">
                                    ৳{{ number_format($sale->due, 2) }}
                                </td>
                                <td class="px-5 py-3">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-emerald-50 text-emerald-700' => $sale->payment_status === 'paid',
                                        'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                                        'bg-red-50 text-red-600' => $sale->payment_status === 'due',
                                    ])>{{ ucfirst($sale->payment_status) }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span @class([
                                        'px-2 py-0.5 rounded-full text-xs font-medium',
                                        'bg-blue-50 text-blue-700' => $sale->status === 'success',
                                        'bg-gray-100 text-gray-500' => $sale->status === 'returned',
                                        'bg-red-50 text-red-600' => $sale->status === 'cancelled',
                                    ])>{{ ucfirst($sale->status ?? '—') }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-400 text-xs">
                                    {{ $sale->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                                    No sales found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- ── Chart.js ─────────────────────────────────────────────────── --}}
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            const labels = @json($chartLabels);
            const data = @json($chartData);

            new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Sales',
                        data,
                        backgroundColor: '#14b8a6',
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                maxRotation: 45
                            }
                        },
                        y: {
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: v => '৳' + (v >= 1000 ? (v / 1000).toFixed(0) + 'k' : v)
                            }
                        }
                    }
                }
            });
        </script>
    @endpush

</x-app-layout>
