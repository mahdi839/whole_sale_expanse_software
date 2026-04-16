@php
    $purchaseReturn = $purchaseReturn ?? null;
    $prefillPurchase = $purchase ?? null;

    $sectionClass  = 'bg-white border border-gray-200 rounded-xl overflow-hidden';
    $headerClass   = 'flex items-center gap-2.5 px-5 py-3 border-b border-gray-100 bg-gray-50/60';
    $bodyClass     = 'p-5';
    $labelClass    = 'block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5';
    $inputClass    = 'w-full h-9 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition';
    $inputErrClass = 'w-full h-9 px-3 text-sm bg-red-50 border border-red-300 rounded-lg text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 transition';

    $allPurchases = \App\Models\Purchase::with(['supplier', 'items.product'])->orderByDesc('id')->get();

    $selectedPurchaseId = old('purchase_id', $purchaseReturn?->purchase_id ?? $prefillPurchase?->id);
    $selectedPurchase = $selectedPurchaseId
        ? $allPurchases->firstWhere('id', (int) $selectedPurchaseId)
        : $prefillPurchase;

    if (old('items')) {
        $items = collect(old('items'))->map(function ($item) {
            return [
                'purchase_item_id' => $item['purchase_item_id'] ?? '',
                'product_id'       => $item['product_id'] ?? '',
                'qty'              => $item['qty'] ?? 1,
                'price'            => $item['price'] ?? 0,
                'line_total'       => ((float) ($item['qty'] ?? 0)) * ((float) ($item['price'] ?? 0)),
            ];
        })->values()->all();
    } elseif ($purchaseReturn) {
        $items = $purchaseReturn->items->map(function ($item) {
            return [
                'purchase_item_id' => $item->purchase_item_id,
                'product_id'       => $item->product_id,
                'qty'              => $item->qty,
                'price'            => $item->price,
                'line_total'       => $item->line_total,
            ];
        })->values()->all();
    } elseif ($selectedPurchase && $selectedPurchase->items->count()) {
        $items = $selectedPurchase->items->map(function ($item) {
            return [
                'purchase_item_id' => $item->id,
                'product_id'       => $item->product_id,
                'qty'              => $item->qty,
                'price'            => $item->price,
                'line_total'       => $item->line_total,
            ];
        })->values()->all();
    } else {
        $items = [[
            'purchase_item_id' => '',
            'product_id'       => '',
            'qty'              => 1,
            'price'            => 0,
            'line_total'       => 0,
        ]];
    }

    $returnType = old('return_type', $purchaseReturn?->return_type ?? 'refund');
    $returnStatus = old('return_status', $purchaseReturn?->return_status ?? 'pending');
    $discount = old('discount', $purchaseReturn?->discount ?? 0);

    $purchasesJson = $allPurchases->map(function ($purchase) {
        return [
            'id' => $purchase->id,
            'reference' => $purchase->reference,
            'supplier_id' => $purchase->supplier_id,
            'items' => $purchase->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->product_name,
                    'sku' => $item->product?->sku,
                    'qty' => (float) $item->qty,
                    'price' => (float) $item->price,
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

        {{-- Purchase / Supplier --}}
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
                        <label for="purchase_id" class="{{ $labelClass }}">Original Purchase</label>
                        <select id="purchase_id" name="purchase_id"
                                class="{{ $errors->has('purchase_id') ? $inputErrClass : $inputClass }}">
                            <option value="">— No linked purchase —</option>
                            @foreach($allPurchases as $p)
                                <option value="{{ $p->id }}" {{ (string) $selectedPurchaseId === (string) $p->id ? 'selected' : '' }}>
                                    {{ $p->reference }}
                                    @if($p->supplier)
                                        — {{ $p->supplier->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-400">
                            Selecting a purchase auto-fills supplier and purchase items.
                        </p>
                    </div>

                    <div>
                        <label for="supplier_id" class="{{ $labelClass }}">Supplier</label>
                        <select id="supplier_id" name="supplier_id"
                                class="{{ $errors->has('supplier_id') ? $inputErrClass : $inputClass }}">
                            <option value="">— No supplier —</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ (string) old('supplier_id', $purchaseReturn?->supplier_id ?? $prefillPurchase?->supplier_id) === (string) $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}{{ $supplier->phone ? ' · '.$supplier->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
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
                                    <label class="{{ $labelClass }}">Purchase Item</label>
                                    <select name="items[{{ $index }}][purchase_item_id]"
                                            class="return-purchase-item {{ $errors->has('items.'.$index.'.purchase_item_id') ? $inputErrClass : $inputClass }}">
                                        <option value="">— Optional —</option>
                                    </select>
                                    @error('items.'.$index.'.purchase_item_id')
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
                                           name="items[{{ $index }}][price]"
                                           value="{{ $item['price'] }}"
                                           class="return-price {{ $errors->has('items.'.$index.'.price') ? $inputErrClass : $inputClass }}">
                                    @error('items.'.$index.'.price')
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
                                    Purchased Qty:
                                    <span class="return-purchased-qty text-gray-700 font-medium">—</span>
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
                              class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">{{ old('note', $purchaseReturn?->note) }}</textarea>
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
                                {{ old('payment_method', $purchaseReturn?->payment_method) === $method ? 'selected' : '' }}>
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
                           value="{{ old('cash_memo', $purchaseReturn?->cash_memo) }}"
                           placeholder="Memo number"
                           class="{{ $errors->has('cash_memo') ? $inputErrClass : $inputClass }}">
                    @error('cash_memo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="{{ $labelClass }}">Date</label>
                    <input type="date"
                           id="date"
                           name="date"
                           value="{{ old('date', optional($purchaseReturn?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                           class="{{ $errors->has('date') ? $inputErrClass : $inputClass }}">
                    @error('date')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="document" class="{{ $labelClass }}">Attachment</label>
                    <input type="file" id="document" name="document"
                           class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer
                                  file:h-7 file:mr-3 file:px-3 file:rounded-md file:border-0
                                  file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100 transition"/>
                    @if($purchaseReturn?->document)
                        <a href="{{ asset('storage/'.$purchaseReturn->document) }}" target="_blank"
                           class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                            View current file
                        </a>
                    @endif
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
                    Pending returns do not affect stock or supplier balances. Approved returns apply inventory and financial changes.
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
                <label class="{{ $labelClass }}">Purchase Item</label>
                <select name="items[__INDEX__][purchase_item_id]" class="return-purchase-item {{ $inputClass }}">
                    <option value="">— Optional —</option>
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="{{ $labelClass }}">Qty</label>
                <input type="number" step="0.01" min="0.01" name="items[__INDEX__][qty]" value="1" class="return-qty {{ $inputClass }}">
            </div>

            <div class="xl:col-span-2">
                <label class="{{ $labelClass }}">Price</label>
                <input type="number" step="0.01" min="0" name="items[__INDEX__][price]" value="0" class="return-price {{ $inputClass }}">
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
                Purchased Qty:
                <span class="return-purchased-qty text-gray-700 font-medium">—</span>
            </span>
        </div>
    </div>
</template>

@push('scripts')
<script>
    const returnPurchases = @json($purchasesJson);
    const returnProducts = @json($productsJson);

    function fmtMoney(value) {
        const num = parseFloat(value || 0);
        return '৳' + num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function getSelectedPurchase() {
        const purchaseId = document.getElementById('purchase_id')?.value;
        if (!purchaseId) return null;
        return returnPurchases.find(p => String(p.id) === String(purchaseId)) || null;
    }

    function getProduct(productId) {
        return returnProducts.find(p => String(p.id) === String(productId)) || null;
    }

    function fillPurchaseItemOptions(itemRow) {
        const purchaseItemSelect = itemRow.querySelector('.return-purchase-item');
        const productSelect = itemRow.querySelector('.return-product');
        const purchasedQtyEl = itemRow.querySelector('.return-purchased-qty');

        if (!purchaseItemSelect || !productSelect) return;

        const selectedPurchase = getSelectedPurchase();
        const selectedProductId = productSelect.value;
        const currentValue = purchaseItemSelect.getAttribute('data-current') || purchaseItemSelect.value || '';

        purchaseItemSelect.innerHTML = '<option value="">— Optional —</option>';
        purchasedQtyEl.textContent = '—';

        if (!selectedPurchase || !selectedPurchase.items) {
            return;
        }

        const matchedItems = selectedPurchase.items.filter(item => {
            if (!selectedProductId) return true;
            return String(item.product_id) === String(selectedProductId);
        });

        matchedItems.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = `${item.product_name || 'Product'} · Purchased ${item.qty} @ ${item.price}`;
            option.dataset.qty = item.qty;
            option.dataset.price = item.price;
            option.dataset.productId = item.product_id;
            purchaseItemSelect.appendChild(option);
        });

        if (currentValue) {
            purchaseItemSelect.value = currentValue;
        }

        const selectedOption = purchaseItemSelect.options[purchaseItemSelect.selectedIndex];
        if (selectedOption && selectedOption.value) {
            purchasedQtyEl.textContent = selectedOption.dataset.qty || '—';
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

    function updateRowFromPurchaseItem(itemRow) {
        const purchaseItemSelect = itemRow.querySelector('.return-purchase-item');
        const productSelect = itemRow.querySelector('.return-product');
        const qtyInput = itemRow.querySelector('.return-qty');
        const priceInput = itemRow.querySelector('.return-price');
        const purchasedQtyEl = itemRow.querySelector('.return-purchased-qty');

        const option = purchaseItemSelect.options[purchaseItemSelect.selectedIndex];

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

            purchasedQtyEl.textContent = option.dataset.qty || '—';
        } else {
            purchasedQtyEl.textContent = '—';
        }

        updateItemMeta(itemRow);
        updateItemLine(itemRow);
        updateSummary();
    }

    function bindRowEvents(itemRow) {
        const productSelect = itemRow.querySelector('.return-product');
        const purchaseItemSelect = itemRow.querySelector('.return-purchase-item');
        const qtyInput = itemRow.querySelector('.return-qty');
        const priceInput = itemRow.querySelector('.return-price');
        const removeBtn = itemRow.querySelector('.remove-return-item');

        productSelect?.addEventListener('change', () => {
            fillPurchaseItemOptions(itemRow);
            updateItemMeta(itemRow);
            updateItemLine(itemRow);
            updateSummary();
        });

        purchaseItemSelect?.addEventListener('change', () => {
            updateRowFromPurchaseItem(itemRow);
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

        fillPurchaseItemOptions(itemRow);
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

        if (data.price) {
            itemRow.querySelector('.return-price').value = data.price;
        }

        if (data.purchase_item_id) {
            itemRow.querySelector('.return-purchase-item').setAttribute('data-current', data.purchase_item_id);
        }

        bindRowEvents(itemRow);
        updateSummary();
    }

    function rebuildRowsFromSelectedPurchase() {
        const wrapper = document.getElementById('return-items-wrapper');
        const selectedPurchase = getSelectedPurchase();

        if (!wrapper) return;

        const currentRows = Array.from(wrapper.querySelectorAll('.return-item'));
        const hasOldInput = {{ old('items') ? 'true' : 'false' }};
        const isEditPage = {{ $purchaseReturn ? 'true' : 'false' }};

        if (hasOldInput || isEditPage) {
            currentRows.forEach(bindRowEvents);
            updateSummary();
            return;
        }

        wrapper.innerHTML = '';

        if (selectedPurchase && selectedPurchase.items.length) {
            selectedPurchase.items.forEach(item => {
                addReturnItemRow({
                    purchase_item_id: item.id,
                    product_id: item.product_id,
                    qty: item.qty,
                    price: item.price,
                });
            });
        } else {
            addReturnItemRow();
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const purchaseSelect = document.getElementById('purchase_id');
        const supplierSelect = document.getElementById('supplier_id');
        const discountInput = document.getElementById('discount');
        const addBtn = document.getElementById('add-return-item');

        document.querySelectorAll('.return-item').forEach(bindRowEvents);
        updateSummary();

        purchaseSelect?.addEventListener('change', () => {
            const purchase = getSelectedPurchase();

            if (purchase && purchase.supplier_id && supplierSelect) {
                supplierSelect.value = purchase.supplier_id;
            }

            rebuildRowsFromSelectedPurchase();
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