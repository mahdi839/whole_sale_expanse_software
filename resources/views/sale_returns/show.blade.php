<x-app-layout>
    <x-slot name="header">Return Detail</x-slot>

    @php
        $fmtQty = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.') ?: '0';
        $fmtMoney = fn ($n) => '৳'.number_format((float) $n, 2);
        $itemCount = $saleReturn->items->count();
        $qtyTotal = (float) $saleReturn->items->sum('qty');
        $typeClass = match ($saleReturn->return_type) {
            'refund' => 'bg-sky-50 text-sky-700 border-sky-100',
            'exchange' => 'bg-violet-50 text-violet-700 border-violet-100',
            default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
    @endphp

    <div class="space-y-5">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sale-returns.index') }}" class="hover:text-gray-600 transition">Sale Returns</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-gray-600">{{ $saleReturn->reference }}</span>
        </nav>

        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#130f55] via-[#3726b0] to-[#2563eb] text-white p-5 sm:p-6">
            <div class="absolute -right-10 -top-12 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute right-16 -bottom-16 w-48 h-48 rounded-full bg-violet-300/20 blur-3xl"></div>
            <div class="relative flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div>
                    <p class="text-[11px] uppercase tracking-widest text-violet-200/90 font-semibold">Sale Return</p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-semibold tracking-tight">{{ $saleReturn->reference }}</h2>
                    <p class="mt-2 text-sm text-white/75">
                        Entry {{ optional($saleReturn->entry_date)->format('d M Y') ?: optional($saleReturn->created_at)->format('d M Y') }}
                        @if($saleReturn->customer)
                            · {{ $saleReturn->customer->full_name }}
                        @endif
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="inline-flex items-center h-7 px-2.5 rounded-full border text-xs font-semibold bg-white/10 text-white border-white/20">
                            {{ ucfirst($saleReturn->return_status) }}
                        </span>
                        <span class="inline-flex items-center h-7 px-2.5 rounded-full border text-xs font-semibold bg-white/10 text-white border-white/20">
                            {{ ucfirst($saleReturn->return_type) }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($saleReturn->return_status === 'pending')
                        <form method="POST" action="{{ route('sale-returns.approve', $saleReturn) }}" onsubmit="return confirm('Approve this return?')">
                            @csrf
                            <button type="submit" class="h-10 px-4 rounded-lg bg-white text-emerald-700 text-sm font-semibold hover:bg-gray-100 transition">
                                Approve
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('sale-returns.edit', $saleReturn) }}" class="h-10 px-4 inline-flex items-center justify-center rounded-lg bg-white text-[#1e3a5f] text-sm font-semibold hover:bg-gray-100 transition">
                        Edit return
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Products</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($itemCount) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Qty returned</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $fmtQty($qtyTotal) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-gray-400 font-semibold">Subtotal</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $fmtMoney($saleReturn->subtotal) }}</p>
            </div>
            <div class="bg-white border border-rose-100 rounded-2xl p-4 shadow-sm">
                <p class="text-[11px] uppercase tracking-wider text-rose-600 font-semibold">Return amount</p>
                <p class="mt-2 text-2xl font-semibold text-rose-700">{{ $fmtMoney($saleReturn->return_amount) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <div class="lg:col-span-2 bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Return information</p>
                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400">Original sale</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @if($saleReturn->sale)
                                <a href="{{ route('sales.show', $saleReturn->sale) }}" class="text-blue-700 hover:underline">{{ $saleReturn->sale->reference }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Applied on sale</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @if($saleReturn->appliedSale)
                                <a href="{{ route('sales.show', $saleReturn->appliedSale) }}" class="text-blue-700 hover:underline">{{ $saleReturn->appliedSale->reference }}</a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Entry date</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ optional($saleReturn->entry_date)->format('d M Y') ?: optional($saleReturn->created_at)->format('d M Y') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Shop</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $saleReturn->sale?->shop?->displayLabel() ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Payment</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $saleReturn->payment_method ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Cash memo</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $saleReturn->cash_memo ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Discount</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $fmtMoney($saleReturn->discount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Type</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center h-6 px-2 rounded-full border text-[11px] font-semibold {{ $typeClass }}">{{ ucfirst($saleReturn->return_type) }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Customer</p>
                @if($saleReturn->customer)
                    <p class="mt-4 text-lg font-semibold text-gray-900">{{ $saleReturn->customer->full_name }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $saleReturn->customer->code }}</p>
                    @if($saleReturn->customer->phone)
                        <p class="mt-1 text-sm text-gray-500">{{ $saleReturn->customer->phone }}</p>
                    @endif
                    @if($saleReturn->customer->address)
                        <p class="mt-3 text-sm text-gray-500">{{ $saleReturn->customer->address }}</p>
                    @endif
                @else
                    <p class="mt-4 text-sm text-gray-400">No customer linked</p>
                @endif
            </div>
        </div>

        <div class="md:hidden space-y-3">
            @forelse($saleReturn->items as $item)
                <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $item->product?->product_name ?? 'Product #'.$item->product_id }}</p>
                            <p class="text-xs text-gray-500">{{ $item->product?->sku ?: ($item->product?->product_code ?: '—') }}</p>
                        </div>
                        <p class="text-sm font-semibold text-rose-700">{{ $fmtMoney($item->line_total) }}</p>
                    </div>
                    <div class="mt-3 flex justify-between text-xs text-gray-500">
                        <span>Qty {{ $fmtQty($item->qty) }}</span>
                        <span>{{ $fmtMoney($item->price_on_sale) }} each</span>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl px-5 py-12 text-center text-sm text-gray-400">
                    No products on this return.
                </div>
            @endforelse
        </div>

        <div class="hidden md:block bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Returned products</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $itemCount }} {{ $itemCount === 1 ? 'product' : 'products' }} · {{ $fmtQty($qtyTotal) }} pieces</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-400">
                        <tr>
                            <th class="px-5 py-3 font-semibold">#</th>
                            <th class="px-4 py-3 font-semibold">Product</th>
                            <th class="px-4 py-3 font-semibold">Design code</th>
                            <th class="px-4 py-3 font-semibold text-right">Qty</th>
                            <th class="px-4 py-3 font-semibold text-right">Price</th>
                            <th class="px-5 py-3 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($saleReturn->items as $item)
                            <tr class="hover:bg-gray-50/70">
                                <td class="px-5 py-3.5 text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3.5 font-medium text-gray-900">{{ $item->product?->product_name ?? 'Product #'.$item->product_id }}</td>
                                <td class="px-4 py-3.5 text-gray-500">{{ $item->product?->sku ?: ($item->product?->product_code ?: '—') }}</td>
                                <td class="px-4 py-3.5 text-right text-gray-700">{{ $fmtQty($item->qty) }}</td>
                                <td class="px-4 py-3.5 text-right text-gray-700">{{ $fmtMoney($item->price_on_sale) }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold text-gray-900">{{ $fmtMoney($item->line_total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-sm text-gray-400">No products on this return.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($itemCount)
                        <tfoot class="bg-gray-50/80 text-sm">
                            <tr>
                                <td colspan="3" class="px-5 py-3 font-semibold text-gray-700">Total</td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $fmtQty($qtyTotal) }}</td>
                                <td class="px-4 py-3"></td>
                                <td class="px-5 py-3 text-right font-semibold text-rose-700">{{ $fmtMoney($saleReturn->items->sum('line_total')) }}</td>
                            </tr>
                            @if((float) $saleReturn->discount > 0)
                                <tr>
                                    <td colspan="5" class="px-5 py-2 text-right text-gray-500">Discount</td>
                                    <td class="px-5 py-2 text-right text-gray-700">− {{ $fmtMoney($saleReturn->discount) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="5" class="px-5 py-3 text-right font-semibold text-gray-700">Return amount</td>
                                <td class="px-5 py-3 text-right font-semibold text-rose-700">{{ $fmtMoney($saleReturn->return_amount) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        @if($saleReturn->note)
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Note</p>
                <p class="mt-2 text-sm text-gray-700">{{ $saleReturn->note }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
