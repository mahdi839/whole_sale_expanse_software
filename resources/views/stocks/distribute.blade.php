<x-app-layout>
    <x-slot name="header">Distribute Stock</x-slot>

    <form method="POST" action="{{ route('stocks.distribute.store') }}" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 max-w-3xl">
        @csrf
        @if($errors->any())
            <div class="px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ $errors->first() }}</div>
        @endif
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Shop</label>
            <select name="shop_id" class="w-full border-gray-300 rounded-lg">
                @foreach($shops as $shop)
                    <option value="{{ $shop->id }}">{{ $shop->name }} ({{ $shop->code }})</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-3" id="items">
            <div class="grid grid-cols-1 sm:grid-cols-[1fr_140px] gap-3">
                <select name="items[0][product_id]" class="border-gray-300 rounded-lg">
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }} - design: {{ $product->sku }} - central: {{ $product->stock?->stock_qty ?? 0 }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.01" min="0.01" name="items[0][qty]" class="border-gray-300 rounded-lg" placeholder="Qty">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="button" onclick="addItem()" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Add Product</button>
            <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Distribute</button>
            <a href="{{ route('stocks.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
        </div>
    </form>

    @push('scripts')
    <script>
        let itemIndex = 1;
        const productOptions = @json($products->map(fn($p) => ['id' => $p->id, 'label' => $p->product_name . ' - design: ' . $p->sku . ' - central: ' . ($p->stock?->stock_qty ?? 0)]));
        function addItem() {
            const wrap = document.getElementById('items');
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 sm:grid-cols-[1fr_140px_44px] gap-3';
            row.innerHTML = `<select name="items[${itemIndex}][product_id]" class="border-gray-300 rounded-lg">${productOptions.map(p => `<option value="${p.id}">${p.label}</option>`).join('')}</select><input type="number" step="0.01" min="0.01" name="items[${itemIndex}][qty]" class="border-gray-300 rounded-lg" placeholder="Qty"><button type="button" class="bg-red-50 text-red-700 rounded-lg" onclick="this.parentElement.remove()">X</button>`;
            wrap.appendChild(row);
            itemIndex++;
        }
    </script>
    @endpush
</x-app-layout>
