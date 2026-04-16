<x-app-layout>
    <x-slot name="header">Purchase Return Details</x-slot>

    <div class="space-y-4 max-w-3xl">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('purchase-returns.index') }}" class="hover:text-gray-600">Purchase Returns</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">{{ $purchaseReturn->reference }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50/60 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-semibold">Purchase Return Information</h2>
                    <p class="text-xs text-gray-400">{{ optional($purchaseReturn->date)->format('d M Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if($purchaseReturn->return_status === 'pending')
                        <form method="POST" action="{{ route('purchase-returns.approve', $purchaseReturn) }}" onsubmit="return confirm('Approve this purchase return?')">
                            @csrf
                            <button class="px-3 py-1.5 text-xs bg-green-50 text-green-700 rounded-lg">Approve</button>
                        </form>
                    @endif

                    <a href="{{ route('purchase-returns.edit', $purchaseReturn) }}" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded-lg">
                        Edit Return
                    </a>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase">Supplier</p>
                        <p class="font-medium">{{ $purchaseReturn->supplier?->name ?? '—' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <p class="text-xs text-gray-500 uppercase">Original Purchase</p>
                        <p class="font-medium">{{ $purchaseReturn->purchase?->reference ?? '—' }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2">Product</th>
                                <th class="text-right w-24">Qty</th>
                                <th class="text-right w-32">Unit price</th>
                                <th class="text-right w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseReturn->items as $item)
                                <tr class="border-b">
                                    <td class="py-2">{{ $item->product->product_name ?? 'Unknown Product' }}</td>
                                    <td class="text-right">{{ number_format($item->qty, 2) }}</td>
                                    <td class="text-right">৳{{ number_format($item->price, 2) }}</td>
                                    <td class="text-right">৳{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right pt-3 font-medium">Subtotal</td>
                                <td class="text-right pt-3">৳{{ number_format($purchaseReturn->items->sum('line_total'), 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right">Discount</td>
                                <td class="text-right text-red-600">- ৳{{ number_format($purchaseReturn->discount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-right font-bold">Return Amount</td>
                                <td class="text-right font-bold text-red-600">৳{{ number_format($purchaseReturn->return_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Return Status</p>
                        <span @class([
                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-50 text-green-700' => $purchaseReturn->return_status === 'approved',
                            'bg-amber-50 text-amber-700' => $purchaseReturn->return_status === 'pending',
                            'bg-red-50 text-red-700' => $purchaseReturn->return_status === 'rejected',
                        ])>{{ ucfirst($purchaseReturn->return_status) }}</span>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Return Type</p>
                        <span @class([
                            'inline-block px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-blue-50 text-blue-700' => $purchaseReturn->return_type === 'refund',
                            'bg-purple-50 text-purple-700' => $purchaseReturn->return_type === 'exchange',
                            'bg-gray-100 text-gray-600' => $purchaseReturn->return_type === 'credit',
                        ])>{{ ucfirst($purchaseReturn->return_type) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Payment Method</p>
                        <p>{{ $purchaseReturn->payment_method ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Cash Memo</p>
                        <p>{{ $purchaseReturn->cash_memo ?? '—' }}</p>
                    </div>
                </div>

                @if($purchaseReturn->note)
                    <div class="bg-amber-50 p-4 rounded-xl">
                        <p class="text-xs text-amber-700 uppercase">Note</p>
                        <p class="text-sm">{{ $purchaseReturn->note }}</p>
                    </div>
                @endif

                @if($purchaseReturn->document)
                    <div>
                        <p class="text-xs text-gray-500 uppercase mb-2">Document</p>
                        <a href="{{ asset('storage/'.$purchaseReturn->document) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                            View Document
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>