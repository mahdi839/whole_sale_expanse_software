<x-app-layout>
    <x-slot name="header">Sale Detail</x-slot>

    <div class="space-y-4 max-w-3xl">

        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sales.index') }}" class="hover:text-gray-600 transition">Sales</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">{{ $sale->reference }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Sale Detail</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ optional($sale->date)->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-violet-50 border border-violet-200
                                 rounded-lg text-xs font-mono font-medium text-violet-700">
                        {{ $sale->reference }}
                    </span>
                    <a href="{{ route('sales.edit', $sale) }}"
                       class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-blue-700
                              bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            </div>

            <div class="p-6 space-y-6">

                {{-- Customer & Product --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Customer</p>
                        @if($sale->customer)
                            <p class="text-sm font-medium text-gray-800">{{ $sale->customer->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $sale->customer->code }}</p>
                            @if($sale->customer->phone)
                                <p class="text-xs text-gray-400">{{ $sale->customer->phone }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-400">No customer linked</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Product</p>
                        <p class="text-sm font-medium text-gray-800">{{ $sale->product_name ?? '—' }}</p>
                        @if($sale->product_code)
                            <p class="text-xs font-mono text-gray-400">{{ $sale->product_code }}</p>
                        @endif
                    </div>
                </div>

                {{-- Financials --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([
                        ['label' => 'Qty',         'value' => number_format($sale->qty, 2),              'color' => 'text-gray-800'],
                        ['label' => 'Grand Total',  'value' => '৳'.number_format($sale->grand_total, 2), 'color' => 'text-blue-600'],
                        ['label' => 'Paid',         'value' => '৳'.number_format($sale->paid, 2),        'color' => 'text-green-600'],
                        ['label' => 'Due',          'value' => '৳'.number_format($sale->due, 2),         'color' => 'text-red-600'],
                    ] as $card)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $card['label'] }}</p>
                        <p class="text-lg font-semibold {{ $card['color'] }}">{{ $card['value'] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Details grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    @foreach([
                        ['label' => 'Price on Sale',    'value' => '৳'.number_format($sale->price_on_sale, 2)],
                        ['label' => 'Discount',         'value' => '৳'.number_format($sale->discount, 2)],
                        ['label' => 'Subtotal',         'value' => '৳'.number_format($sale->subtotal, 2)],
                        ['label' => 'Payment Method',   'value' => $sale->payment_method ?: '—'],
                        ['label' => 'Cash Memo',        'value' => $sale->cash_memo ?: '—'],
                        ['label' => 'Date',             'value' => optional($sale->date)->format('d M Y')],
                    ] as $row)
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-100">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">{{ $row['label'] }}</span>
                        <span class="text-sm text-gray-700 font-medium">{{ $row['value'] }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Statuses --}}
                <div class="flex items-center gap-3">
                    <span @class([
                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                        'bg-green-50 text-green-700' => $sale->purchase_status === 'received',
                        'bg-amber-50 text-amber-700' => $sale->purchase_status === 'partial',
                        'bg-gray-100 text-gray-600'  => $sale->purchase_status === 'pending',
                        'bg-blue-50 text-blue-700'   => $sale->purchase_status === 'ordered',
                    ])>{{ ucfirst($sale->purchase_status) }}</span>

                    <span @class([
                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                        'bg-green-50 text-green-700' => $sale->payment_status === 'paid',
                        'bg-amber-50 text-amber-700' => $sale->payment_status === 'partial',
                        'bg-red-50 text-red-700'     => $sale->payment_status === 'due',
                    ])>{{ ucfirst($sale->payment_status) }}</span>
                </div>

                {{-- Note --}}
                @if($sale->note)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1.5">Note</p>
                        <p class="text-sm text-gray-700">{{ $sale->note }}</p>
                    </div>
                @endif

                {{-- Document --}}
                @if($sale->document)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachment</p>
                        <a href="{{ asset('storage/'.$sale->document) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-700
                                  bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View Document
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>