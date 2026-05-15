@csrf
@php
    $selectedProductId = old('product_id', $stock?->product_id ?? null);
    $selectedProduct = collect($products ?? [])->firstWhere('id', (int) $selectedProductId);
@endphp
<style>
    .stock-product-picker { position: relative; }
    .stock-product-display {
        width: 100%;
        height: 42px;
        padding: 0 38px 0 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        text-align: left;
        cursor: pointer;
    }
    .stock-product-chevron {
        position: absolute;
        right: 12px;
        top: 13px;
        color: #94a3b8;
        pointer-events: none;
    }
    .stock-product-dropdown {
        display: none;
        position: absolute;
        z-index: 60;
        left: 0;
        right: 0;
        top: calc(100% + 6px);
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
        overflow: hidden;
    }
    .stock-product-dropdown.open { display: block; }
    .stock-product-search { padding: 8px; border-bottom: 1px solid #f1f5f9; }
    .stock-product-search input {
        width: 100%;
        height: 34px;
        padding: 0 10px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        font-size: 13px;
    }
    .stock-product-list { max-height: 220px; overflow-y: auto; }
    .stock-product-option {
        padding: 9px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f8fafc;
        font-size: 13px;
    }
    .stock-product-option:hover,
    .stock-product-option.selected { background: #eff6ff; }
    .stock-product-option .sku { color: #64748b; font-family: monospace; font-size: 11px; margin-top: 2px; }
</style>
<div class="bg-white border border-gray-200 rounded-xl p-5 space-y-4 max-w-2xl">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
        <div class="stock-product-picker" id="stock-product-picker">
            <button type="button" class="stock-product-display" id="stock-product-display">
                {{ $selectedProduct ? $selectedProduct->product_name.' (Design: '.$selectedProduct->sku.')' : 'Select product' }}
            </button>
            <span class="stock-product-chevron">⌄</span>
            <input type="hidden" name="product_id" id="stock-product-id" value="{{ $selectedProductId }}">
            <div class="stock-product-dropdown" id="stock-product-dropdown">
                <div class="stock-product-search">
                    <input type="text" id="stock-product-search" placeholder="Search product or design code..." autocomplete="off">
                </div>
                <div class="stock-product-list" id="stock-product-list"></div>
            </div>
        </div>
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

@push('scripts')
<script>
    const stockProducts = @json(collect($products ?? [])->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->product_name,
        'sku' => $product->sku,
    ])->values());

    const stockPicker = document.getElementById('stock-product-picker');
    const stockDisplay = document.getElementById('stock-product-display');
    const stockHidden = document.getElementById('stock-product-id');
    const stockDropdown = document.getElementById('stock-product-dropdown');
    const stockSearch = document.getElementById('stock-product-search');
    const stockList = document.getElementById('stock-product-list');

    function renderStockProducts(query = '') {
        const q = query.toLowerCase();
        const matches = stockProducts.filter(product =>
            product.name.toLowerCase().includes(q) || String(product.sku || '').toLowerCase().includes(q)
        );

        stockList.innerHTML = matches.length ? matches.map(product => `
            <div class="stock-product-option${String(product.id) === String(stockHidden.value) ? ' selected' : ''}" data-id="${product.id}">
                <div>${escapeStockHtml(product.name)}</div>
                <div class="sku">Design: ${escapeStockHtml(product.sku || '-')}</div>
            </div>
        `).join('') : '<div class="stock-product-option">No products found</div>';

        stockList.querySelectorAll('[data-id]').forEach(option => {
            option.addEventListener('click', () => {
                const product = stockProducts.find(item => String(item.id) === String(option.dataset.id));
                stockHidden.value = product.id;
                stockDisplay.textContent = `${product.name} (Design: ${product.sku || '-'})`;
                stockDropdown.classList.remove('open');
            });
        });
    }

    stockDisplay?.addEventListener('click', () => {
        stockDropdown.classList.toggle('open');
        renderStockProducts();
        setTimeout(() => stockSearch?.focus(), 50);
    });

    stockSearch?.addEventListener('input', () => renderStockProducts(stockSearch.value));

    document.addEventListener('click', event => {
        if (stockPicker && !stockPicker.contains(event.target)) {
            stockDropdown.classList.remove('open');
        }
    });

    function escapeStockHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
    }
</script>
@endpush
