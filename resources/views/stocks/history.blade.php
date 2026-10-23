<x-app-layout>
    <x-slot name="header">Stock History</x-slot>

    @php
        $fmtQty = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.') ?: '0';
        $ranges = [
            'today' => 'Today',
            '7d' => '7 Days',
            'month' => 'This Month',
            'all' => 'All Time',
        ];
        $rangeUrl = fn (string $range) => route('stocks.history', array_merge(
            request()->except(['date_from', 'date_to', 'page']),
            ['range' => $range]
        ));
        $typeUrl = fn (string $type) => route('stocks.history', array_merge(
            request()->except(['page']),
            ['type' => $type]
        ));
        $badgeClass = function ($movement) {
            return match ($movement->movement_type) {
                'sale' => 'bg-rose-50 text-rose-700 border-rose-100',
                'sale_reversal' => 'bg-gray-100 text-gray-600 border-gray-200',
                'sale_return' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'sale_return_reversal' => 'bg-orange-50 text-orange-700 border-orange-100',
                'add_stock' => 'bg-sky-50 text-sky-700 border-sky-100',
                'adjustment' => 'bg-amber-50 text-amber-700 border-amber-100',
                'transfer' => 'bg-violet-50 text-violet-700 border-violet-100',
                'distribution' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                'distribution_receive' => 'bg-teal-50 text-teal-700 border-teal-100',
                'distribution_cancel' => 'bg-gray-100 text-gray-600 border-gray-200',
                default => 'bg-gray-50 text-gray-600 border-gray-200',
            };
        };
        $periodLabel = match ($filters['range']) {
            'today' => 'Today',
            '7d' => 'Last 7 days',
            'month' => 'This month',
            'all' => 'All time',
            default => ($filters['date_from'] || $filters['date_to'])
                ? trim(($filters['date_from'] ?: 'Start').' – '.($filters['date_to'] ?: 'Now'))
                : 'All time',
        };
    @endphp

    <div class="space-y-5">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#130f55] via-[#3726b0] to-[#2563eb] text-white p-5 sm:p-6">
            <div class="absolute -right-10 -top-12 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute right-16 -bottom-16 w-48 h-48 rounded-full bg-violet-300/20 blur-3xl"></div>
            <div class="relative flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-emerald-100/90 font-semibold">Stock Intelligence</p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-semibold tracking-tight">Stock History</h2>
                    <p class="mt-2 text-sm text-white/75 max-w-xl">
                        Sales, return sales, added stock, adjustments, transfers, and shop distributions.
                        {{ $periodLabel }}.
                    </p>
                </div>
                <a href="{{ route('stocks.index') }}"
                    class="h-10 w-fit px-4 inline-flex items-center justify-center gap-2 rounded-lg bg-white text-[#1e3a5f] text-sm font-semibold hover:bg-gray-100 transition">
                    View inventory
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Movements</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($summary['count']) }}</p>
            </div>
            <div class="bg-white border border-emerald-100 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-emerald-600 font-semibold">Stock in</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">{{ $summary['in'] > 0 ? '+' : '' }}{{ $fmtQty($summary['in']) }}</p>
            </div>
            <div class="bg-white border border-rose-100 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-rose-600 font-semibold">Stock out</p>
                <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $summary['out'] > 0 ? '−' : '' }}{{ $fmtQty($summary['out']) }}</p>
            </div>
            <div class="bg-white border border-indigo-100 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-indigo-600 font-semibold">Net</p>
                <p class="mt-2 text-2xl font-semibold {{ $summary['net'] >= 0 ? 'text-indigo-700' : 'text-rose-700' }}">
                    {{ $summary['net'] > 0 ? '+' : ($summary['net'] < 0 ? '−' : '') }}{{ $fmtQty(abs($summary['net'])) }}
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ $typeUrl('') }}"
                    class="h-8 px-3 inline-flex items-center rounded-full text-xs font-semibold border transition {{ $filters['type'] === '' ? 'bg-[#130f55] text-white border-[#130f55]' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                    All
                </a>
                @foreach(\App\Models\StockMovement::filterOptions() as $key => $label)
                    <a href="{{ $typeUrl($key) }}"
                        class="h-8 px-3 inline-flex items-center rounded-full text-xs font-semibold border transition {{ $filters['type'] === $key ? 'bg-[#3726b0] text-white border-[#3726b0]' : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('stocks.history') }}">
                <input type="hidden" name="range" value="custom">
                <input type="hidden" name="type" value="{{ $filters['type'] }}">

                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($ranges as $key => $label)
                        <a href="{{ $rangeUrl($key) }}"
                            class="h-8 px-3 inline-flex items-center rounded-full text-xs font-semibold border transition {{ $filters['range'] === $key ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-2.5">
                    <div class="sm:col-span-2">
                        <label class="block text-xs text-gray-400 mb-1 ml-0.5">Product or reference</label>
                        <input type="text" name="search" value="{{ $filters['search'] }}"
                            placeholder="Name, design code, or reference"
                            class="h-10 w-full px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    </div>
                    @if(auth()->user()->canManageAllShops())
                        <div>
                            <label class="block text-xs text-gray-400 mb-1 ml-0.5">Location</label>
                            <select name="shop_id" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                                <option value="">All locations</option>
                                <option value="central" @selected($filters['shop_id'] === 'central')>Central</option>
                                @foreach($shops as $shop)
                                    <option value="{{ $shop->id }}" @selected((string) $filters['shop_id'] === (string) $shop->id)>{{ $shop->displayLabel() }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
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
                    <div class="flex items-end">
                        <button type="submit"
                            class="h-10 w-full px-4 rounded-lg bg-[#130f55] text-white text-sm font-semibold hover:bg-[#3726b0] transition">
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="md:hidden space-y-3">
            @forelse($movements as $movement)
                <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="inline-flex items-center h-6 px-2 rounded-full border text-[11px] font-semibold {{ $badgeClass($movement) }}">
                                {{ $movement->label() }}
                            </span>
                            <p class="mt-2 text-sm font-semibold text-gray-900">{{ $movement->product?->product_name ?? 'Product #'.$movement->product_id }}</p>
                            <p class="text-xs text-gray-500">{{ $movement->product?->sku ?: ($movement->product?->product_code ?: '—') }}</p>
                        </div>
                        <p class="text-lg font-semibold {{ $movement->direction === 'in' ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $movement->direction === 'in' ? '+' : '−' }}{{ $fmtQty($movement->qty) }}
                        </p>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-500">
                        <p>{{ $movement->locationLabel() }}</p>
                        <p class="text-right">{{ $movement->occurred_at?->format('d M Y, h:i A') }}</p>
                        <p>{{ $movement->reference ?: '—' }}</p>
                        <p class="text-right">{{ $movement->user?->name ?: '—' }}</p>
                    </div>
                    @if($movement->detailNote())
                        <p class="mt-2 text-xs text-gray-400">{{ $movement->detailNote() }}</p>
                    @endif
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl px-5 py-16 text-center text-sm text-gray-400">
                    No stock movements for this filter.
                </div>
            @endforelse

            @if($movements->hasPages())
                <div class="bg-white border border-gray-200 rounded-2xl px-4 py-3">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>

        <div class="hidden md:block bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">When</th>
                            <th class="px-4 py-3 font-semibold">Movement</th>
                            <th class="px-4 py-3 font-semibold">Product</th>
                            <th class="px-4 py-3 font-semibold">Location</th>
                            <th class="px-4 py-3 font-semibold">Reference</th>
                            <th class="px-4 py-3 font-semibold text-right">Qty</th>
                            <th class="px-5 py-3 font-semibold">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50/70">
                                <td class="px-5 py-3.5 whitespace-nowrap text-gray-600">
                                    <p>{{ $movement->occurred_at?->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-400">{{ $movement->occurred_at?->format('h:i A') }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center h-6 px-2 rounded-full border text-[11px] font-semibold {{ $badgeClass($movement) }}">
                                        {{ $movement->label() }}
                                    </span>
                                    @if($movement->detailNote())
                                        <p class="mt-1 text-xs text-gray-400 max-w-[220px]">{{ $movement->detailNote() }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-gray-900">{{ $movement->product?->product_name ?? 'Product #'.$movement->product_id }}</p>
                                    <p class="text-xs text-gray-400">{{ $movement->product?->sku ?: ($movement->product?->product_code ?: '—') }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">{{ $movement->locationLabel() }}</td>
                                <td class="px-4 py-3.5">
                                    @if($movement->linkUrl())
                                        <a href="{{ $movement->linkUrl() }}" class="text-[#3726b0] font-medium hover:underline">{{ $movement->reference ?: 'Open' }}</a>
                                    @else
                                        <span class="text-gray-600">{{ $movement->reference ?: '—' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold {{ $movement->direction === 'in' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $movement->direction === 'in' ? '+' : '−' }}{{ $fmtQty($movement->qty) }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-500">{{ $movement->user?->name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center text-sm text-gray-400">
                                    No stock movements for this filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movements->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $movements->links() }}
                </div>
            @endif
        </div>

        @if($movements->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $movements->firstItem() }}–{{ $movements->lastItem() }}
                of {{ number_format($movements->total()) }} movements
            </p>
        @endif
    </div>
</x-app-layout>
