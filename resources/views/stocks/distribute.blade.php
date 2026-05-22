<x-app-layout>
    <x-slot name="header">Distribute Stock</x-slot>

    @php
        $stockProductPayload = $products->map(function ($product) {
            return [
                'id' => (string) $product->id,
                'name' => $product->product_name,
                'sku' => $product->sku,
                'stock' => (float) ($product->stock?->stock_qty ?? 0),
            ];
        })->values();
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_420px] gap-4">
    <form method="POST" action="{{ route('stocks.distribute.store') }}" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
        @csrf
        @if($errors->any())
            <div class="px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ $errors->first() }}</div>
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
                <select name="shop_id" class="w-full border-gray-300 rounded-lg">
                    @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" @selected(old('shop_id') == $shop->id)>{{ $shop->name }} ({{ $shop->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="distribution_date" value="{{ old('distribution_date', now()->toDateString()) }}" class="w-full border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Distributor</label>
                <input name="distributor" value="{{ old('distributor', auth()->user()->name) }}" class="w-full border-gray-300 rounded-lg" placeholder="Distributor name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receiver</label>
                <input name="receiver" value="{{ old('receiver') }}" class="w-full border-gray-300 rounded-lg" placeholder="Receiver name">
            </div>
        </div>

        <div class="space-y-3" id="items">
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_140px] gap-3 stock-row">
                <div x-data="stockProductSelect(0)" class="relative" @click.outside="open = false">
                    <input type="hidden" name="items[0][product_id]" x-model="selectedId">
                    <button type="button" @click="open = !open; $nextTick(() => $refs.search.focus())"
                        class="w-full min-h-10 px-3 py-2 text-left text-sm bg-white border border-gray-300 rounded-lg">
                        <span x-text="selectedLabel || 'Select product'"></span>
                    </button>
                    <div x-show="open" class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                        <input x-ref="search" type="text" x-model="query" placeholder="Search product or design code..."
                            class="w-full h-10 px-3 text-sm border-0 border-b border-gray-100 focus:ring-0">
                        <div class="max-h-56 overflow-y-auto">
                            <template x-for="product in filteredProducts()" :key="product.id">
                                <button type="button" @click="select(product)" class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50">
                                    <span class="font-medium text-gray-800" x-text="product.name"></span>
                                    <span class="block text-xs text-gray-400" x-text="'Design: ' + (product.sku || '-') + ' | Central: ' + product.stock"></span>
                                </button>
                            </template>
                            <div x-show="filteredProducts().length === 0" class="px-3 py-3 text-sm text-gray-400">No products found.</div>
                        </div>
                    </div>
                </div>
                <input type="number" step="0.01" min="0.01" name="items[0][qty]" class="border-gray-300 rounded-lg" placeholder="Qty">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="button" onclick="addItem()" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Add Product</button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Create Pending Distribution</button>
            <a href="{{ route('stocks.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
        </div>
    </form>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-5 py-3 border-b font-semibold text-sm text-gray-700">Recent Distributions</div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-400 uppercase">Date</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-400 uppercase">Shop</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-400 uppercase">Receiver</th>
                        <th class="text-right px-4 py-3 text-xs font-medium text-gray-400 uppercase">Qty</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-400 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($distributions as $distribution)
                        <tr>
                            <td class="px-4 py-3">{{ $distribution->distribution_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $distribution->shop?->name }}</td>
                            <td class="px-4 py-3">{{ $distribution->receiver }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($distribution->items->sum('qty'), 2) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $distribution->status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-green-50 text-green-700' }}">
                                    {{ ucfirst($distribution->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No distributions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>

    @push('scripts')
    <script>
        let itemIndex = 1;
        window.stockProducts = @json($stockProductPayload);

        function stockProductSelect(initialIndex) {
            return {
                open: false,
                query: '',
                selectedId: '',
                selectedLabel: '',
                filteredProducts() {
                    const q = this.query.trim().toLowerCase();
                    return window.stockProducts.filter(product =>
                        !q || product.name.toLowerCase().includes(q) || String(product.sku || '').toLowerCase().includes(q)
                    ).slice(0, 50);
                },
                select(product) {
                    this.selectedId = product.id;
                    this.selectedLabel = `${product.name} - design: ${product.sku || '-'} - central: ${product.stock}`;
                    this.open = false;
                    this.query = '';
                },
            };
        }

        function addItem() {
            const wrap = document.getElementById('items');
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 sm:grid-cols-[1fr_140px_44px] gap-3 stock-row';
            row.innerHTML = `
                <div x-data="stockProductSelect(${itemIndex})" class="relative" @click.outside="open = false">
                    <input type="hidden" name="items[${itemIndex}][product_id]" x-model="selectedId">
                    <button type="button" @click="open = !open; $nextTick(() => $refs.search.focus())" class="w-full min-h-10 px-3 py-2 text-left text-sm bg-white border border-gray-300 rounded-lg">
                        <span x-text="selectedLabel || 'Select product'"></span>
                    </button>
                    <div x-show="open" class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                        <input x-ref="search" type="text" x-model="query" placeholder="Search product or design code..." class="w-full h-10 px-3 text-sm border-0 border-b border-gray-100 focus:ring-0">
                        <div class="max-h-56 overflow-y-auto">
                            <template x-for="product in filteredProducts()" :key="product.id">
                                <button type="button" @click="select(product)" class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50">
                                    <span class="font-medium text-gray-800" x-text="product.name"></span>
                                    <span class="block text-xs text-gray-400" x-text="'Design: ' + (product.sku || '-') + ' | Central: ' + product.stock"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <input type="number" step="0.01" min="0.01" name="items[${itemIndex}][qty]" class="border-gray-300 rounded-lg" placeholder="Qty">
                <button type="button" class="bg-red-50 text-red-700 rounded-lg" onclick="this.parentElement.remove()">X</button>`;
            wrap.appendChild(row);
            if (window.Alpine) Alpine.initTree(row);
            itemIndex++;
        }
    </script>
    @endpush
</x-app-layout>
