@php
    $saleReturn = $saleReturn ?? null;
    $prefillSale = $sale ?? null;

    $sectionClass  = 'bg-white border border-gray-200 rounded-xl overflow-hidden';
    $headerClass   = 'flex items-center gap-2.5 px-5 py-3 border-b border-gray-100 bg-gray-50/60';
    $bodyClass     = 'p-5';
    $labelClass    = 'block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5';
    $inputClass    = 'w-full h-9 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition';
    $inputErrClass = 'w-full h-9 px-3 text-sm bg-red-50 border border-red-300 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 transition';

    $allSales = \App\Models\Sale::with(['customer', 'items.product'])->orderByDesc('id')->get();

    $selectedSaleId = old('sale_id', $saleReturn?->sale_id ?? $prefillSale?->id);
    $selectedSale = $selectedSaleId
        ? $allSales->firstWhere('id', (int) $selectedSaleId)
        : $prefillSale;

    if (old('items')) {
        $items = collect(old('items'))->map(function ($item) {
            return [
                'sale_item_id'   => $item['sale_item_id'] ?? '',
                'product_id'     => $item['product_id'] ?? '',
                'qty'            => $item['qty'] ?? 1,
                'price_on_sale'  => $item['price_on_sale'] ?? 0,
                'line_total'     => ((float) ($item['qty'] ?? 0)) * ((float) ($item['price_on_sale'] ?? 0)),
            ];
        })->values()->all();
    } elseif ($saleReturn) {
        $items = $saleReturn->items->map(function ($item) {
            return [
                'sale_item_id'   => $item->sale_item_id,
                'product_id'     => $item->product_id,
                'qty'            => $item->qty,
                'price_on_sale'  => $item->price_on_sale,
                'line_total'     => $item->line_total,
            ];
        })->values()->all();
    } elseif ($selectedSale && $selectedSale->items->count()) {
        $items = $selectedSale->items->map(function ($item) {
            return [
                'sale_item_id'   => $item->id,
                'product_id'     => $item->product_id,
                'qty'            => $item->qty,
                'price_on_sale'  => $item->price_on_sale,
                'line_total'     => $item->line_total,
            ];
        })->values()->all();
    } else {
        $items = [[
            'sale_item_id'   => '',
            'product_id'     => '',
            'qty'            => 1,
            'price_on_sale'  => 0,
            'line_total'     => 0,
        ]];
    }

    $returnType = old('return_type', $saleReturn?->return_type ?? 'refund');
    $returnStatus = old('return_status', $saleReturn?->return_status ?? 'pending');
    $discount = old('discount', $saleReturn?->discount ?? 0);

    $customersJson = $customers->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->full_name,
            'code' => $c->code,
            'phone' => $c->phone,
        ];
    })->values();

    $salesJson = $allSales->map(function ($sale) {
        return [
            'id' => $sale->id,
            'reference' => $sale->reference,
            'customer_id' => $sale->customer_id,
            'items' => $sale->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->product_name,
                    'sku' => $item->product?->sku,
                    'qty' => (float) $item->qty,
                    'price_on_sale' => (float) $item->price_on_sale,
                    'line_total' => (float) $item->line_total,
                ];
            })->values(),
        ];
    })->values();

    $productsJson = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->product_name,
            'sku' => $product->sku,
            'stock_qty' => (float) optional($product->stock)->stock_qty,
        ];
    })->values();
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2 space-y-4">

        {{-- Sale / Customer --}}
        <div class="{{ $sectionClass }}">
            <div class="{{ $headerClass }}">
                <span class="flex items-center justify-center w-6 h-6 rounded-md bg-violet-50 text-violet-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Return Information</span>
            </div>

            <div class="{{ $bodyClass }}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sale_id" class="{{ $labelClass }}">Original Sale</label>
                        <select id="sale_id" name="sale_id"
                                class="{{ $errors->has('sale_id') ? $inputErrClass : $inputClass }}">
                            <option value="">— No linked sale —</option>
                            @foreach($allSales as $s)
                                <option value="{{ $s->id }}" {{ (string) $selectedSaleId === (string) $s->id ? 'selected' : '' }}>
                                    {{ $s->reference }}
                                    @if($s->customer)
                                        — {{ $s->customer->full_name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('sale_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-400">
                            Linking a sale lets you return from original sale items.
                        </p>
                    </div>

                    <div>
                        <label for="customer_id" class="{{ $labelClass }}">Customer</label>
                        <select id="customer_id" name="customer_id"
                                class="{{ $errors->has('customer_id') ? $inputErrClass : $inputClass }}">
                            <option value="">— Walk-in / No customer —</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    {{ (string) old('customer_id', $saleReturn?->customer_id ?? $prefillSale?->customer_id) === (string) $customer->id ? 'selected' : '' }}>
                                    {{ $customer->full_name }}{{ $customer->phone ? ' · '.$customer->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Return Items --}}
        <div class="{{ $sectionClass }}">
            <div class="{{ $headerClass }}">
                <span class="flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                    </svg>
                </span>
                <div class="flex items-center justify-between w-full gap-3">
                    <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Return Items</span>

                    <button type="button"
                            id="add-return-item"
                            class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Item
                    </button>
                </div>
            </div>

            <div class="{{ $bodyClass }}">
                @error('items')
                    <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
                @enderror

                <div id="return-items-wrapper" class="space-y-3">
                    @foreach($items as $index => $item)
                        <div class="return-item rounded-xl border border-gray-200 bg-gray-50/60 p-4" data-index="{{ $index }}">
                            <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">
                                <div class="xl:col-span-4">
                                    <label class="{{ $labelClass }}">Product</label>
                                    <select name="items[{{ $index }}][product_id]"
                                            class="return-product {{ $errors->has('items.'.$index.'.product_id') ? $inputErrClass : $inputClass }}">
                                        <option value="">— Select product —</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-name="{{ $product->product_name }}"
                                                    data-sku="{{ $product->sku }}"
                                                    data-stock="{{ (float) optional($product->stock)->stock_qty }}"
                                                {{ (string) ($item['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                                {{ $product->product_name }}{{ $product->sku ? ' ['.$product->sku.']' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.'.$index.'.product_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="xl:col-span-2">
                                    <label class="{{ $labelClass }}">Sale Item</label>
                                    <select name="items[{{ $index }}][sale_item_id]"
                                            class="return-sale-item {{ $errors->has('items.'.$index.'.sale_item_id') ? $inputErrClass : $inputClass }}">
                                        <option value="">— Optional —</option>
                                    </select>
                                    @error('items.'.$index.'.sale_item_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="xl:col-span-2">
                                    <label class="{{ $labelClass }}">Qty</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0.01"
                                           name="items[{{ $index }}][qty]"
                                           value="{{ $item['qty'] }}"
                                           class="return-qty {{ $errors->has('items.'.$index.'.qty') ? $inputErrClass : $inputClass }}">
                                    @error('items.'.$index.'.qty')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="xl:col-span-2">
                                    <label class="{{ $labelClass }}">Price</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="items[{{ $index }}][price_on_sale]"
                                           value="{{ $item['price_on_sale'] }}"
                                           class="return-price {{ $errors->has('items.'.$index.'.price_on_sale') ? $inputErrClass : $inputClass }}">
                                    @error('items.'.$index.'.price_on_sale')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="{{ $labelClass }}">Total</label>
                                    <div class="h-9 px-3 inline-flex items-center w-full text-sm bg-white border border-gray-200 rounded-lg text-gray-700">
                                        <span class="return-line-total">৳{{ number_format((float) ($item['line_total'] ?? 0), 2) }}</span>
                                    </div>
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="{{ $labelClass }}">Action</label>
                                    <button type="button"
                                            class="remove-return-item h-9 w-full inline-flex items-center justify-center text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition">
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                                    SKU:
                                    <span class="return-sku text-gray-700 font-medium">—</span>
                                </span>

                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                                    Stock:
                                    <span class="return-stock text-gray-700 font-medium">0</span>
                                </span>

                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                                    Sold Qty:
                                    <span class="return-sold-qty text-gray-700 font-medium">—</span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="{{ $sectionClass }}">
            <div class="{{ $headerClass }}">
                <span class="flex items-center justify-center w-6 h-6 rounded-md bg-gray-100 text-gray-500 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Additional Details</span>
            </div>

            <div class="{{ $bodyClass }}">
                <div>
                    <label for="note" class="{{ $labelClass }}">Note</label>
                    <textarea id="note" name="note" rows="3"
                              placeholder="Any additional notes..."
                              class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">{{ old('note', $saleReturn?->note) }}</textarea>
                    @error('note')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="space-y-4">
        <div class="{{ $sectionClass }}">
            <div class="{{ $headerClass }}">
                <span class="flex items-center justify-center w-6 h-6 rounded-md bg-green-50 text-green-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"/>
                    </svg>
                </span>
                <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Return Summary</span>
            </div>

            <div class="{{ $bodyClass }} space-y-4">
                <div>
                    <label for="discount" class="{{ $labelClass }}">Discount</label>
                    <input type="number"
                           id="discount"
                           name="discount"
                           min="0"
                           step="0.01"
                           value="{{ $discount }}"
                           class="{{ $errors->has('discount') ? $inputErrClass : $inputClass }}">
                    @error('discount')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="return_type" class="{{ $labelClass }}">Return Type</label>
                    <select id="return_type" name="return_type"
                            class="{{ $errors->has('return_type') ? $inputErrClass : $inputClass }}">
                        @foreach(['refund' => 'Refund', 'exchange' => 'Exchange', 'credit' => 'Credit'] as $value => $label)
                            <option value="{{ $value }}" {{ $returnType === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('return_type')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="return_status" class="{{ $labelClass }}">Return Status</label>
                    <select id="return_status" name="return_status"
                            class="{{ $errors->has('return_status') ? $inputErrClass : $inputClass }}">
                        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                            <option value="{{ $value }}" {{ $returnStatus === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('return_status')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="payment_method" class="{{ $labelClass }}">Payment Method</label>
                    <select id="payment_method" name="payment_method"
                            class="{{ $errors->has('payment_method') ? $inputErrClass : $inputClass }}">
                        <option value="">— Select —</option>
                        @foreach(['Cash','Bank','Bkash','Nagad','Card'] as $method)
                            <option value="{{ $method }}"
                                {{ old('payment_method', $saleReturn?->payment_method) === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cash_memo" class="{{ $labelClass }}">Cash Memo</label>
                    <input type="text"
                           id="cash_memo"
                           name="cash_memo"
                           value="{{ old('cash_memo', $saleReturn?->cash_memo) }}"
                           placeholder="Memo number"
                           class="{{ $errors->has('cash_memo') ? $inputErrClass : $inputClass }}">
                    @error('cash_memo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2 pt-2">
                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Subtotal</span>
                        <span id="return-subtotal-display" class="text-base font-semibold text-gray-800">৳0.00</span>
                    </div>

                    <div class="flex items-center justify-between bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Return Amount</span>
                        <span id="return-amount-display" class="text-base font-semibold text-red-600">৳0.00</span>
                    </div>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs text-amber-700">
                    Pending returns do not affect stock or customer balances. Approved returns apply inventory and financial changes.
                </div>
            </div>
        </div>
    </div>
</div>

<template id="return-item-template">
    <div class="return-item rounded-xl border border-gray-200 bg-gray-50/60 p-4" data-index="__INDEX__">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">
            <div class="xl:col-span-4">
                <label class="{{ $labelClass }}">Product</label>
                <select name="items[__INDEX__][product_id]" class="return-product {{ $inputClass }}">
                    <option value="">— Select product —</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-name="{{ $product->product_name }}"
                                data-sku="{{ $product->sku }}"
                                data-stock="{{ (float) optional($product->stock)->stock_qty }}">
                            {{ $product->product_name }}{{ $product->sku ? ' ['.$product->sku.']' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="{{ $labelClass }}">Sale Item</label>
                <select name="items[__INDEX__][sale_item_id]" class="return-sale-item {{ $inputClass }}">
                    <option value="">— Optional —</option>
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="{{ $labelClass }}">Qty</label>
                <input type="number" step="0.01" min="0.01" name="items[__INDEX__][qty]" value="1" class="return-qty {{ $inputClass }}">
            </div>

            <div class="xl:col-span-2">
                <label class="{{ $labelClass }}">Price</label>
                <input type="number" step="0.01" min="0" name="items[__INDEX__][price_on_sale]" value="0" class="return-price {{ $inputClass }}">
            </div>

            <div class="xl:col-span-1">
                <label class="{{ $labelClass }}">Total</label>
                <div class="h-9 px-3 inline-flex items-center w-full text-sm bg-white border border-gray-200 rounded-lg text-gray-700">
                    <span class="return-line-total">৳0.00</span>
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="{{ $labelClass }}">Action</label>
                <button type="button"
                        class="remove-return-item h-9 w-full inline-flex items-center justify-center text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition">
                    Remove
                </button>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                SKU:
                <span class="return-sku text-gray-700 font-medium">—</span>
            </span>

            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                Stock:
                <span class="return-stock text-gray-700 font-medium">0</span>
            </span>

            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                Sold Qty:
                <span class="return-sold-qty text-gray-700 font-medium">—</span>
            </span>
        </div>
    </div>
</template>

@push('scripts')
<script>
    const returnSales = @json($salesJson);
    const returnProducts = @json($productsJson);

    function fmtMoney(value) {
        const num = parseFloat(value || 0);
        return '৳' + num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getSelectedSale() {
        const saleId = document.getElementById('sale_id')?.value;
        if (!saleId) return null;
        return returnSales.find(s => String(s.id) === String(saleId)) || null;
    }

    function getProduct(productId) {
        return returnProducts.find(p => String(p.id) === String(productId)) || null;
    }

    function fillSaleItemOptions(itemRow) {
        const saleItemSelect = itemRow.querySelector('.return-sale-item');
        const productSelect = itemRow.querySelector('.return-product');
        const soldQtyEl = itemRow.querySelector('.return-sold-qty');

        if (!saleItemSelect || !productSelect) return;

        const selectedSale = getSelectedSale();
        const selectedProductId = productSelect.value;
        const currentValue = saleItemSelect.getAttribute('data-current') || saleItemSelect.value || '';

        saleItemSelect.innerHTML = '<option value="">— Optional —</option>';
        soldQtyEl.textContent = '—';

        if (!selectedSale || !selectedSale.items) {
            return;
        }

        const matchedItems = selectedSale.items.filter(item => {
            if (!selectedProductId) return true;
            return String(item.product_id) === String(selectedProductId);
        });

        matchedItems.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.product_name || 'Product'} · Sold ${item.qty} @ ${item.price_on_sale}`;
            option.dataset.qty = item.qty;
            option.dataset.price = item.price_on_sale;
            option.dataset.productId = item.product_id;
            saleItemSelect.appendChild(option);
        });

        if (currentValue) {
            saleItemSelect.value = currentValue;
        }

        const selectedOption = saleItemSelect.options[saleItemSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            soldQtyEl.textContent = selectedOption.dataset.qty || '—';
        }
    }

    function updateItemMeta(itemRow) {
        const productSelect = itemRow.querySelector('.return-product');
        const skuEl = itemRow.querySelector('.return-sku');
        const stockEl = itemRow.querySelector('.return-stock');

        const product = getProduct(productSelect?.value);

        skuEl.textContent = product?.sku || '—';
        stockEl.textContent = product?.stock_qty ?? 0;
    }

    function updateItemLine(itemRow) {
        const qtyInput = itemRow.querySelector('.return-qty');
        const priceInput = itemRow.querySelector('.return-price');
        const totalEl = itemRow.querySelector('.return-line-total');

        const qty = parseFloat(qtyInput?.value || 0);
        const price = parseFloat(priceInput?.value || 0);
        const lineTotal = qty * price;

        totalEl.textContent = fmtMoney(lineTotal);
    }

    function updateSummary() {
        let subtotal = 0;

        document.querySelectorAll('.return-item').forEach(row => {
            const qty = parseFloat(row.querySelector('.return-qty')?.value || 0);
            const price = parseFloat(row.querySelector('.return-price')?.value || 0);
            subtotal += qty * price;
        });

        const discount = parseFloat(document.getElementById('discount')?.value || 0);
        const returnAmount = Math.max(0, subtotal - discount);

        document.getElementById('return-subtotal-display').textContent = fmtMoney(subtotal);
        document.getElementById('return-amount-display').textContent = fmtMoney(returnAmount);
    }

    function updateRowFromSaleItem(itemRow) {
        const saleItemSelect = itemRow.querySelector('.return-sale-item');
        const productSelect = itemRow.querySelector('.return-product');
        const qtyInput = itemRow.querySelector('.return-qty');
        const priceInput = itemRow.querySelector('.return-price');
        const soldQtyEl = itemRow.querySelector('.return-sold-qty');

        const option = saleItemSelect.options[saleItemSelect.selectedIndex];

        if (option && option.value) {
            if (option.dataset.productId) {
                productSelect.value = option.dataset.productId;
            }

            if (option.dataset.qty && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
                qtyInput.value = option.dataset.qty;
            }

            if (option.dataset.price) {
                priceInput.value = option.dataset.price;
            }

            soldQtyEl.textContent = option.dataset.qty || '—';
        } else {
            soldQtyEl.textContent = '—';
        }

        updateItemMeta(itemRow);
        updateItemLine(itemRow);
        updateSummary();
    }

    function bindRowEvents(itemRow) {
        const productSelect = itemRow.querySelector('.return-product');
        const saleItemSelect = itemRow.querySelector('.return-sale-item');
        const qtyInput = itemRow.querySelector('.return-qty');
        const priceInput = itemRow.querySelector('.return-price');
        const removeBtn = itemRow.querySelector('.remove-return-item');

        productSelect?.addEventListener('change', () => {
            fillSaleItemOptions(itemRow);
            updateItemMeta(itemRow);
            updateItemLine(itemRow);
            updateSummary();
        });

        saleItemSelect?.addEventListener('change', () => {
            updateRowFromSaleItem(itemRow);
        });

        qtyInput?.addEventListener('input', () => {
            updateItemLine(itemRow);
            updateSummary();
        });

        priceInput?.addEventListener('input', () => {
            updateItemLine(itemRow);
            updateSummary();
        });

        removeBtn?.addEventListener('click', () => {
            const rows = document.querySelectorAll('.return-item');
            if (rows.length <= 1) return;
            itemRow.remove();
            updateSummary();
        });

        fillSaleItemOptions(itemRow);
        updateItemMeta(itemRow);
        updateItemLine(itemRow);
    }

    function addReturnItemRow(data = {}) {
        const wrapper = document.getElementById('return-items-wrapper');
        const template = document.getElementById('return-item-template').innerHTML;
        const index = wrapper.querySelectorAll('.return-item').length;

        const html = template.replaceAll('__INDEX__', index);
        wrapper.insertAdjacentHTML('beforeend', html);

        const itemRow = wrapper.lastElementChild;

        if (data.product_id) {
            itemRow.querySelector('.return-product').value = data.product_id;
        }

        if (data.qty) {
            itemRow.querySelector('.return-qty').value = data.qty;
        }

        if (data.price_on_sale) {
            itemRow.querySelector('.return-price').value = data.price_on_sale;
        }

        if (data.sale_item_id) {
            itemRow.querySelector('.return-sale-item').setAttribute('data-current', data.sale_item_id);
        }

        bindRowEvents(itemRow);
        updateSummary();
    }

    function rebuildRowsFromSelectedSale() {
        const wrapper = document.getElementById('return-items-wrapper');
        const selectedSale = getSelectedSale();

        if (!wrapper) return;

        const currentRows = Array.from(wrapper.querySelectorAll('.return-item'));
        const hasOldInput = {{ old('items') ? 'true' : 'false' }};
        const isEditPage = {{ $saleReturn ? 'true' : 'false' }};

        if (hasOldInput || isEditPage) {
            currentRows.forEach(bindRowEvents);
            updateSummary();
            return;
        }

        wrapper.innerHTML = '';

        if (selectedSale && selectedSale.items.length) {
            selectedSale.items.forEach(item => {
                addReturnItemRow({
                    sale_item_id: item.id,
                    product_id: item.product_id,
                    qty: item.qty,
                    price_on_sale: item.price_on_sale,
                });
            });
        } else {
            addReturnItemRow();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const saleSelect = document.getElementById('sale_id');
        const customerSelect = document.getElementById('customer_id');
        const discountInput = document.getElementById('discount');
        const addBtn = document.getElementById('add-return-item');

        document.querySelectorAll('.return-item').forEach(bindRowEvents);
        updateSummary();

        saleSelect?.addEventListener('change', () => {
            const sale = getSelectedSale();

            if (sale && sale.customer_id && customerSelect) {
                customerSelect.value = sale.customer_id;
            }

            rebuildRowsFromSelectedSale();
        });

        discountInput?.addEventListener('input', updateSummary);

        addBtn?.addEventListener('click', () => {
            addReturnItemRow();
        });

        if (!document.querySelectorAll('.return-item').length) {
            addReturnItemRow();
        }
    });
</script>
@endpush