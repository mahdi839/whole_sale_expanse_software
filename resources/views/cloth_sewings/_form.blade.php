@php
    $productOptions = $products->map(function ($product) {
        return [
            'id' => (string) $product->id,
            'name' => $product->product_name,
            'sku' => $product->sku,
            'code' => $product->product_code,
        ];
    })->values();

    $selectedProductId = (string) old('product_id', $clothSewing->product_id);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tailor Name</label>
        <input type="text" name="tailor_name" value="{{ old('tailor_name', $clothSewing->tailor_name) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('tailor_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div x-data="productSelect()" class="relative sm:col-span-2" @click.outside="open = false">
        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
        <input type="hidden" name="product_id" x-model="selectedId">
        <button type="button" @click="open = !open; $nextTick(() => $refs.search.focus())"
            class="w-full min-h-10 px-3 py-2 text-left text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <span x-text="selectedLabel || 'Select product'"></span>
        </button>
        <div x-show="open" class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
            <input x-ref="search" type="text" x-model="query" placeholder="Search product or design code..."
                class="w-full h-10 px-3 text-sm border-0 border-b border-gray-100 focus:ring-0">
            <div class="max-h-56 overflow-y-auto">
                <template x-for="product in filteredProducts()" :key="product.id">
                    <button type="button" @click="select(product)"
                        class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50">
                        <span class="font-medium text-gray-800" x-text="product.name"></span>
                        <span class="block text-xs text-gray-400" x-text="'Design: ' + product.sku + (product.code ? ' | Code: ' + product.code : '')"></span>
                    </button>
                </template>
                <div x-show="filteredProducts().length === 0" class="px-3 py-3 text-sm text-gray-400">No products found.</div>
            </div>
        </div>
        @error('product_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Item Qty</label>
        <input type="number" step="0.01" min="0.01" name="item_qty" value="{{ old('item_qty', $clothSewing->item_qty) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('item_qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($clothSewing->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
function productSelect() {
    const products = @json($productOptions);
    const initialId = @json($selectedProductId);
    const initial = products.find(product => product.id === initialId);

    return {
        open: false,
        query: '',
        products,
        selectedId: initial?.id || '',
        selectedLabel: initial ? `${initial.name} - ${initial.sku}` : '',
        filteredProducts() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.products.slice(0, 50);

            return this.products.filter(product =>
                product.name.toLowerCase().includes(q) ||
                String(product.sku || '').toLowerCase().includes(q) ||
                String(product.code || '').toLowerCase().includes(q)
            ).slice(0, 50);
        },
        select(product) {
            this.selectedId = product.id;
            this.selectedLabel = `${product.name} - ${product.sku}`;
            this.query = '';
            this.open = false;
        },
    };
}
</script>
@endpush
