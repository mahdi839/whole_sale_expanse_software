<x-app-layout>
    <x-slot name="header">{{ $shop->name }}</x-slot>
    <div class="space-y-4">
        @include('partials.flash')
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-sm text-gray-500">{{ $shop->address ?: 'No address' }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $shop->phone ?: 'No phone' }}</p>
            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Stock Qty</p>
                    <p class="font-semibold text-gray-800">{{ number_format($stockQty, 2) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Stock Value</p>
                    <p class="font-semibold text-green-600">BDT {{ number_format($stockValue, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-gray-800">Shop Stock</p>
                    <p class="text-xs text-gray-400 mt-0.5">Available product stock in this shop</p>
                </div>
                <form method="GET" action="{{ route('shops.show', $shop) }}" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Search product name or SKU"
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full sm:w-72">
                    <button class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">Search</button>
                    @if($search)
                        <a href="{{ route('shops.show', $shop) }}" class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center">Reset</a>
                    @endif
                </form>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Product</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Design Code</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Available Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Stock Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($stocks as $stock)
                        <tr>
                            <td class="px-4 py-3">{{ $stock->product?->product_name }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $stock->product?->sku ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($stock->stock_qty, 2) }}</td>
                            <td class="px-4 py-3 text-right">BDT {{ number_format((float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0), 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">{{ $search ? 'No products match your search.' : 'No stock distributed.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($stocks->hasPages())
                <div class="px-4 py-3 border-t bg-gray-50/50">{{ $stocks->links() }}</div>
            @endif
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex justify-between mb-3"><h3 class="font-semibold">Executives</h3><a class="text-blue-600 text-sm" href="{{ route('shops.executives', $shop) }}">Assign</a></div>
            <p class="text-sm text-gray-600">{{ $shop->users->pluck('name')->implode(', ') ?: 'No executives assigned.' }}</p>
        </div>
    </div>
</x-app-layout>
