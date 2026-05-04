@csrf
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 max-w-2xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
        <select name="product_id" class="w-full border-gray-300 rounded-lg">
            @foreach($products ?? [] as $product)
                <option value="{{ $product->id }}" @selected(old('product_id', $stock?->product_id ?? null) == $product->id)>
                    {{ $product->product_name }} ({{ $product->sku }})
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('product_id')" class="mt-1" />
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Central Stock Quantity</label>
        <input type="number" name="stock_qty" value="{{ old('stock_qty', $stock?->stock_qty ?? 0) }}" min="0" class="w-full border-gray-300 rounded-lg">
        <x-input-error :messages="$errors->get('stock_qty')" class="mt-1" />
    </div>
    <div class="flex gap-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save</button>
        <a href="{{ route('stocks.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm">Cancel</a>
    </div>
</div>
