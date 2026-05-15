@php
    $record = $clothSewing ?? $receivedCloth ?? null;
    $routeBase = $routeBase ?? 'cloth-sewings';
    $isCreate = ! $record?->exists;

    $productOptions = $products->map(fn ($product) => [
        'id' => (string) $product->id,
        'name' => $product->product_name,
        'sku' => $product->sku,
        'code' => $product->product_code,
    ])->values();

    $oldItems = old('items');
    $items = $isCreate
        ? ($oldItems ?: [['product_id' => '', 'item_qty' => '']])
        : [[
            'product_id' => old('product_id', $record?->product_id),
            'item_qty' => old('item_qty', $record?->item_qty),
        ]];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Tailor Name</label>
        <input type="text" name="tailor_name" value="{{ old('tailor_name', $record?->tailor?->name ?? $record?->tailor_name) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('tailor_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($record?->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Products</h3>
        @if($isCreate)
            <button type="button" id="add-cloth-item" class="h-9 px-3 text-sm text-blue-700 bg-blue-50 border border-blue-200 rounded-lg">Add Product</button>
        @endif
    </div>

    <div id="cloth-items" class="space-y-3">
        @foreach($items as $index => $item)
            <div class="cloth-item grid grid-cols-1 sm:grid-cols-[1fr_140px_44px] gap-3">
                <div x-data="clothProductSelect(@js((string) ($item['product_id'] ?? '')))" class="relative" @click.outside="open = false">
                    <input type="hidden" name="{{ $isCreate ? 'items['.$index.'][product_id]' : 'product_id' }}" x-model="selectedId">
                    <button type="button" @click="open = !open; $nextTick(() => $refs.search.focus())"
                        class="w-full min-h-10 px-3 py-2 text-left text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <span x-text="selectedLabel || 'Select product'"></span>
                    </button>
                    <div x-show="open" class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                        <input x-ref="search" type="text" x-model="query" placeholder="Search product or design code..."
                            class="w-full h-10 px-3 text-sm border-0 border-b border-gray-100 focus:ring-0">
                        <div class="max-h-56 overflow-y-auto">
                            <template x-for="product in filteredProducts()" :key="product.id">
                                <button type="button" @click="select(product)" class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50">
                                    <span class="font-medium text-gray-800" x-text="product.name"></span>
                                    <span class="block text-xs text-gray-400" x-text="'Design: ' + (product.sku || '-') + (product.code ? ' | Code: ' + product.code : '')"></span>
                                </button>
                            </template>
                            <div x-show="filteredProducts().length === 0" class="px-3 py-3 text-sm text-gray-400">No products found.</div>
                        </div>
                    </div>
                    @error($isCreate ? 'items.'.$index.'.product_id' : 'product_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <input type="number" step="0.01" min="0.01" name="{{ $isCreate ? 'items['.$index.'][item_qty]' : 'item_qty' }}" value="{{ $item['item_qty'] ?? '' }}"
                        class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg" placeholder="Qty">
                    @error($isCreate ? 'items.'.$index.'.item_qty' : 'item_qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                @if($isCreate)
                    <button type="button" class="remove-cloth-item h-10 bg-red-50 text-red-700 rounded-lg">X</button>
                @endif
            </div>
        @endforeach
    </div>
</div>

@if($isCreate)
    <template id="cloth-item-template">
        <div class="cloth-item grid grid-cols-1 sm:grid-cols-[1fr_140px_44px] gap-3">
            <div x-data="clothProductSelect('')" class="relative" @click.outside="open = false">
                <input type="hidden" name="items[__INDEX__][product_id]" x-model="selectedId">
                <button type="button" @click="open = !open; $nextTick(() => $refs.search.focus())"
                    class="w-full min-h-10 px-3 py-2 text-left text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <span x-text="selectedLabel || 'Select product'"></span>
                </button>
                <div x-show="open" class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
                    <input x-ref="search" type="text" x-model="query" placeholder="Search product or design code..." class="w-full h-10 px-3 text-sm border-0 border-b border-gray-100 focus:ring-0">
                    <div class="max-h-56 overflow-y-auto">
                        <template x-for="product in filteredProducts()" :key="product.id">
                            <button type="button" @click="select(product)" class="w-full px-3 py-2 text-left text-sm hover:bg-blue-50">
                                <span class="font-medium text-gray-800" x-text="product.name"></span>
                                <span class="block text-xs text-gray-400" x-text="'Design: ' + (product.sku || '-') + (product.code ? ' | Code: ' + product.code : '')"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <input type="number" step="0.01" min="0.01" name="items[__INDEX__][item_qty]" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg" placeholder="Qty">
            <button type="button" class="remove-cloth-item h-10 bg-red-50 text-red-700 rounded-lg">X</button>
        </div>
    </template>
@endif

@push('scripts')
<script>
window.clothProducts = @json($productOptions);
function clothProductSelect(initialId) {
    const initial = window.clothProducts.find(product => product.id === String(initialId));
    return {
        open: false,
        query: '',
        selectedId: initial?.id || '',
        selectedLabel: initial ? `${initial.name} - ${initial.sku || '-'}` : '',
        filteredProducts() {
            const q = this.query.trim().toLowerCase();
            return window.clothProducts.filter(product =>
                !q || product.name.toLowerCase().includes(q) ||
                String(product.sku || '').toLowerCase().includes(q) ||
                String(product.code || '').toLowerCase().includes(q)
            ).slice(0, 50);
        },
        select(product) {
            this.selectedId = product.id;
            this.selectedLabel = `${product.name} - ${product.sku || '-'}`;
            this.query = '';
            this.open = false;
        },
    };
}

document.getElementById('add-cloth-item')?.addEventListener('click', () => {
    const wrap = document.getElementById('cloth-items');
    const index = wrap.querySelectorAll('.cloth-item').length;
    wrap.insertAdjacentHTML('beforeend', document.getElementById('cloth-item-template').innerHTML.replaceAll('__INDEX__', index));
    if (window.Alpine) Alpine.initTree(wrap.lastElementChild);
});

document.addEventListener('click', event => {
    if (event.target.classList.contains('remove-cloth-item')) {
        const rows = document.querySelectorAll('.cloth-item');
        if (rows.length > 1) event.target.closest('.cloth-item')?.remove();
    }
});
</script>
@endpush
