<x-app-layout>
    <x-slot name="header">Shops</x-slot>
    <div class="space-y-4">
        @include('partials.flash')
        <div class="flex justify-end"><a href="{{ route('shops.create') }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">New Shop</a></div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="text-left px-4 py-3">Shop</th><th class="text-left px-4 py-3">Executives</th><th class="text-left px-4 py-3">Sales</th><th class="text-right px-4 py-3">Stock Qty</th><th class="text-right px-4 py-3">Stock Value</th><th class="text-right px-4 py-3">Actions</th></tr></thead>
                <tbody class="divide-y">
                    @foreach($shops as $shop)
                        <tr>
                            <td class="px-4 py-3"><a class="text-blue-600" href="{{ route('shops.show', $shop) }}">{{ $shop->name }}</a><br><span class="text-xs text-gray-400">{{ $shop->code }}</span></td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ $shop->users_count }}</div>
                                @if($shop->users->isNotEmpty())
                                    <div class="text-xs text-gray-500 mt-1">{{ $shop->users->pluck('name')->join(', ') }}</div>
                                @else
                                    <div class="text-xs text-gray-400 mt-1">No executive</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $shop->sales_count }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($shop->stocks->sum('stock_qty'), 2) }}</td>
                            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->is_admin)<td class="px-4 py-3 text-right">BDT {{ number_format($shop->stocks->sum(fn($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0)), 2) }}</td>@endif
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('shops.executives', $shop) }}" class="text-green-600">Executives</a>
                                <a href="{{ route('shops.edit', $shop) }}" class="text-blue-600 ml-2">Edit</a>
                                <form method="POST" action="{{ route('shops.destroy', $shop) }}" class="inline" onsubmit="return confirm('Delete this shop?')">@csrf @method('DELETE') <button class="text-red-600 ml-2">Delete</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $shops->links() }}</div>
        </div>
    </div>
</x-app-layout>
