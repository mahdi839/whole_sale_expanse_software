<x-app-layout>
    <x-slot name="header">{{ $shop->name }}</x-slot>
    <div class="space-y-4">
        @include('partials.flash')
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <p class="text-sm text-gray-500">{{ $shop->address ?: 'No address' }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $shop->phone ?: 'No phone' }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold">Shop Stock</div>
            <table class="w-full text-sm">
                <tbody class="divide-y">
                    @forelse($shop->stocks as $stock)
                        <tr><td class="px-4 py-3">{{ $stock->product?->product_name }}</td><td class="px-4 py-3 text-right">{{ number_format($stock->stock_qty) }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-10 text-center text-gray-400">No stock distributed.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex justify-between mb-3"><h3 class="font-semibold">Executives</h3><a class="text-blue-600 text-sm" href="{{ route('shops.executives', $shop) }}">Assign</a></div>
            <p class="text-sm text-gray-600">{{ $shop->users->pluck('name')->implode(', ') ?: 'No executives assigned.' }}</p>
        </div>
    </div>
</x-app-layout>
