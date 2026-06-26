<x-app-layout>
    <x-slot name="header">Stock Adjustment</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <form method="POST" action="{{ route('stocks.adjustments.store') }}" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                @csrf
                <h2 class="text-sm font-semibold text-gray-800">Plus / Minus Stock</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <select name="location_type" id="adjust-location-type" class="w-full border-gray-300 rounded-lg">
                            <option value="central" @selected(old('location_type', 'central') === 'central')>Central Stock</option>
                            <option value="shop" @selected(old('location_type') === 'shop')>Shop Stock</option>
                        </select>
                    </div>

                    <div id="adjust-shop-wrap">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
                        <select name="shop_id" id="adjust-shop-id" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" @selected(old('shop_id') == $shop->id)>{{ $shop->name }}{{ $shop->code ? ' ('.$shop->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                        <select name="product_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->product_name }}{{ $product->sku ? ' - '.$product->sku : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment</label>
                        <select name="adjustment_type" class="w-full border-gray-300 rounded-lg">
                            <option value="plus" @selected(old('adjustment_type', 'plus') === 'plus')>Plus Stock</option>
                            <option value="minus" @selected(old('adjustment_type') === 'minus')>Minus Stock</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                        <input type="number" name="qty" value="{{ old('qty') }}" min="0.01" step="0.01" class="w-full border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save Adjustment</button>
                    <a href="{{ route('stocks.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Back</a>
                </div>
            </form>

            <form method="POST" action="{{ route('stocks.transfers.store') }}" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
                @csrf
                <h2 class="text-sm font-semibold text-gray-800">Transfer Shop to Shop</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Shop</label>
                        <select name="from_shop_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" @selected(old('from_shop_id') == $shop->id)>{{ $shop->name }}{{ $shop->code ? ' ('.$shop->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Shop</label>
                        <select name="to_shop_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select shop</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" @selected(old('to_shop_id') == $shop->id)>{{ $shop->name }}{{ $shop->code ? ' ('.$shop->code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                        <select name="product_id" class="w-full border-gray-300 rounded-lg">
                            <option value="">Select product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->product_name }}{{ $product->sku ? ' - '.$product->sku : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Qty</label>
                        <input type="number" name="qty" value="{{ old('qty') }}" min="0.01" step="0.01" class="w-full border-gray-300 rounded-lg">
                    </div>
                </div>

                <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Transfer Stock</button>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b font-semibold text-sm text-gray-700">Recent Stock Balances</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Location</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Design Code</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400 uppercase">Qty</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stocks as $stock)
                            <tr>
                                <td class="px-5 py-3">{{ $stock->shop?->name ?? 'Central' }}</td>
                                <td class="px-5 py-3">{{ $stock->product?->product_name }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $stock->product?->sku ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-medium">{{ number_format($stock->stock_qty, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-gray-400">No stock balances found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const locationType = document.getElementById('adjust-location-type');
                const shopWrap = document.getElementById('adjust-shop-wrap');
                const shopSelect = document.getElementById('adjust-shop-id');

                function syncShopField() {
                    const isShop = locationType?.value === 'shop';
                    shopWrap?.classList.toggle('hidden', !isShop);
                    if (shopSelect) {
                        shopSelect.disabled = !isShop;
                        if (!isShop) shopSelect.value = '';
                    }
                }

                locationType?.addEventListener('change', syncShopField);
                syncShopField();
            });
        </script>
    @endpush
</x-app-layout>
