<x-app-layout>
    <x-slot name="header">Sale Details</x-slot>

    <div class="space-y-4 max-w-3xl mx-auto min-h-[calc(100vh-10rem)] flex flex-col justify-center">
        <nav class="flex items-center justify-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sales.index') }}" class="hover:text-gray-600">Sales</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-gray-600">{{ $sale->reference }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50/60 flex flex-col sm:flex-row justify-center sm:justify-between items-center gap-3 text-center sm:text-left">
                <div><h2 class="text-sm font-semibold">Sale Information</h2><p class="text-xs text-gray-400">{{ $sale->created_at->format('d M Y, h:i A') }}</p></div>
                @canany(['manage sales', 'edit sales'])
                    <a href="{{ route('sales.edit', $sale) }}" class="px-3 py-1.5 text-xs bg-blue-50 text-blue-700 rounded-lg">Edit Sale</a>
                @endcanany
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-xl"><p class="text-xs text-gray-500 uppercase">Customer</p><p class="font-medium">{{ $sale->customer?->full_name ?? 'Walk-in customer' }}</p></div>
                    <div class="bg-gray-50 p-4 rounded-xl"><p class="text-xs text-gray-500 uppercase">Cash Memo</p><p class="font-mono">{{ $sale->cash_memo ?? '—' }}</p></div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="border-b"><th class="text-left py-2">Product</th><th class="text-right w-24">Qty</th><th class="text-right w-32">Unit price</th><th class="text-right w-32">Total</th></tr></thead>
                        <tbody>
                            @foreach($sale->items as $item)
                                <tr class="border-b"><td class="py-2">{{ $item->product->product_name }}</td><td class="text-right">{{ number_format($item->qty, 2) }}</td><td class="text-right">৳{{ number_format($item->price_on_sale, 2) }}</td><td class="text-right">৳{{ number_format($item->line_total, 2) }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="3" class="text-right pt-3 font-medium">Subtotal</td><td class="text-right pt-3">৳{{ number_format($sale->items->sum('line_total'), 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right">Discount</td><td class="text-right text-red-600">- ৳{{ number_format($sale->discount, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right">Add Money</td><td class="text-right text-indigo-600">+ ৳{{ number_format($sale->add_money, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right font-bold">Grand Total</td><td class="text-right font-bold text-blue-600">৳{{ number_format($sale->grand_total, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right text-green-600">Paid</td><td class="text-right text-green-600">৳{{ number_format($sale->paid, 2) }}</td></tr>
                            <tr><td colspan="3" class="text-right text-red-600">Due</td><td class="text-right text-red-600">৳{{ number_format($sale->due, 2) }}</td></tr>
                        </tfoot>
                    </table>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div><p class="text-xs text-gray-500">Payment Status</p><span @class(['inline-block px-2 py-0.5 rounded-full text-xs font-medium','bg-green-50 text-green-700'=> $sale->payment_status=='paid','bg-amber-50 text-amber-700'=> $sale->payment_status=='partial','bg-red-50 text-red-700'=> $sale->payment_status=='due'])>{{ ucfirst($sale->payment_status) }}</span></div>
                    <div><p class="text-xs text-gray-500">Payment Method</p><p>{{ $sale->payment_method ?? '—' }}</p></div>
                </div>

                @if($sale->note)
                    <div class="bg-amber-50 p-4 rounded-xl"><p class="text-xs text-amber-700 uppercase">Note</p><p class="text-sm">{{ $sale->note }}</p></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
