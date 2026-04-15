<x-app-layout>
    <x-slot name="header">Return Detail</x-slot>

    <div class="space-y-4 max-w-3xl">

        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sale-returns.index') }}" class="hover:text-gray-600 transition">Sale Returns</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">{{ $saleReturn->reference }}</span>
        </nav>

        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Return Detail</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ optional($saleReturn->date)->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-orange-50 border border-orange-200
                                 rounded-lg text-xs font-mono font-medium text-orange-700">
                        {{ $saleReturn->reference }}
                    </span>

                    @if($saleReturn->return_status === 'pending')
                        <form method="POST" action="{{ route('sale-returns.approve', $saleReturn) }}"
                              onsubmit="return confirm('Approve this return?')">
                            @csrf
                            <button type="submit"
                                    class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium
                                           text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M5 13l4 4L19 7"/>
                                </svg>
                                Approve
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('sale-returns.edit', $saleReturn) }}"
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

                {{-- Original sale link --}}
                @if($saleReturn->sale)
                    <div class="flex items-center gap-3 p-3 bg-violet-50 border border-violet-200 rounded-xl">
                        <svg class="w-4 h-4 text-violet-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                        <p class="text-xs text-violet-700">
                            Return for original sale
                            <a href="{{ route('sales.show', $saleReturn->sale) }}"
                               class="font-mono font-semibold hover:underline ml-1">
                                {{ $saleReturn->sale->reference }}
                            </a>
                        </p>
                    </div>
                @endif

                {{-- Customer & Product --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Customer</p>
                        @if($saleReturn->customer)
                            <p class="text-sm font-medium text-gray-800">{{ $saleReturn->customer->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $saleReturn->customer->code }}</p>
                            @if($saleReturn->customer->phone)
                                <p class="text-xs text-gray-400">{{ $saleReturn->customer->phone }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-400">No customer linked</p>
                        @endif
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 space-y-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Product</p>
                        <p class="text-sm font-medium text-gray-800">{{ $saleReturn->product_name ?? '—' }}</p>
                        @if($saleReturn->product_code)
                            <p class="text-xs font-mono text-gray-400">{{ $saleReturn->product_code }}</p>
                        @endif
                    </div>
                </div>

                {{-- Financials --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach([
                        ['label' => 'Qty Returned',   'value' => number_format($saleReturn->qty, 2),              'color' => 'text-gray-800'],
                        ['label' => 'Subtotal',        'value' => '৳'.number_format($saleReturn->subtotal, 2),    'color' => 'text-gray-800'],
                        ['label' => 'Return Amount',   'value' => '৳'.number_format($saleReturn->return_amount, 2),'color' => 'text-red-600'],
                    ] as $card)
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $card['label'] }}</p>
                        <p class="text-lg font-semibold {{ $card['color'] }}">{{ $card['value'] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Details --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    @foreach([
                        ['label' => 'Price on Sale',   'value' => '৳'.number_format($saleReturn->price_on_sale, 2)],
                        ['label' => 'Discount',        'value' => '৳'.number_format($saleReturn->discount, 2)],
                        ['label' => 'Payment Method',  'value' => $saleReturn->payment_method ?: '—'],
                        ['label' => 'Cash Memo',       'value' => $saleReturn->cash_memo ?: '—'],
                        ['label' => 'Date',            'value' => optional($saleReturn->date)->format('d M Y')],
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
                        'bg-green-50 text-green-700'   => $saleReturn->return_status === 'approved',
                        'bg-amber-50 text-amber-700'   => $saleReturn->return_status === 'pending',
                        'bg-red-50 text-red-700'       => $saleReturn->return_status === 'rejected',
                    ])>{{ ucfirst($saleReturn->return_status) }}</span>

                    <span @class([
                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
                        'bg-blue-50 text-blue-700'     => $saleReturn->return_type === 'refund',
                        'bg-purple-50 text-purple-700' => $saleReturn->return_type === 'exchange',
                        'bg-gray-100 text-gray-600'    => $saleReturn->return_type === 'credit',
                    ])>{{ ucfirst($saleReturn->return_type) }}</span>
                </div>

                {{-- Reason --}}
                @if($saleReturn->reason)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-1.5">Return Reason</p>
                        <p class="text-sm text-gray-700">{{ $saleReturn->reason }}</p>
                    </div>
                @endif

                {{-- Note --}}
                @if($saleReturn->note)
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Note</p>
                        <p class="text-sm text-gray-700">{{ $saleReturn->note }}</p>
                    </div>
                @endif

                {{-- Document --}}
                @if($saleReturn->document)
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Attachment</p>
                        <a href="{{ asset('storage/'.$saleReturn->document) }}" target="_blank"
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