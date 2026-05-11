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

    $returnType   = old('return_type',   $purchaseReturn?->return_type   ?? 'refund');
    $returnStatus = old('return_status', $purchaseReturn?->return_status ?? 'pending');
    $discount     = old('discount',      $purchaseReturn?->discount      ?? 0);

    $purchasesJson = $allPurchases->map(function ($purchase) {
        return [
            'id'          => $purchase->id,
            'reference'   => $purchase->reference,
            'supplier_id' => $purchase->supplier_id,
            'items'       => $purchase->items->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product?->product_name,
                    'sku'          => $item->product?->sku,
                    'qty'          => (float) $item->qty,
                    'price'        => (float) $item->price,
                    'line_total'   => (float) $item->line_total,
                ];
            })->values(),
        ];
    })->values();

    $productsJson = $products->map(function ($product) {
        return [
            'id'        => $product->id,
            'name'      => $product->product_name,
            'sku'       => $product->sku,
            'stock_qty' => (float) optional($product->stock)->stock_qty,
        ];
    })->values();
@endphp

<style>
/* ── Searchable dropdown trigger (inline element) ── */
.sd-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    height: 36px;
    padding: 0 10px;
    font-size: 14px;
    cursor: pointer;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #1f2937;
    outline: none;
    user-select: none;
    transition: border-color .15s, box-shadow .15s;
    white-space: nowrap;
    overflow: hidden;
    box-sizing: border-box;
}
.sd-trigger:focus,
.sd-trigger.is-open {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(59,130,246,.15);
}
.sd-trigger-text {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 13px;
}
.sd-trigger-text.placeholder { color: #9ca3af; }
.sd-chevron {
    flex-shrink: 0;
    margin-left: 6px;
    color: #9ca3af;
    transition: transform .2s;
}
.sd-chevron.is-open { transform: rotate(180deg); }

/* ── Portal dropdown — fixed to viewport, escapes overflow:hidden ── */
#sd-portal {
    position: fixed;
    top: 0; left: 0;
    width: 0; height: 0;
    z-index: 9999;
    pointer-events: none;
}
.sd-portal-panel {
    position: fixed;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 8px 28px rgba(0,0,0,.13);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    pointer-events: all;
    min-width: 200px;
}
.sd-search-box { padding: 8px; border-bottom: 1px solid #f3f4f6; }
.sd-search-box input {
    width: 100%;
    height: 32px;
    padding: 0 10px;
    font-size: 13px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #f9fafb;
    color: #1f2937;
    outline: none;
    transition: border-color .15s;
    box-sizing: border-box;
}
.sd-search-box input:focus { border-color: #60a5fa; }
.sd-list { max-height: 210px; overflow-y: auto; }
.sd-option {
    padding: 9px 12px;
    cursor: pointer;
    font-size: 13px;
    border-bottom: 1px solid #f3f4f6;
    transition: background .1s;
    color: #1f2937;
}
.sd-option:last-child { border-bottom: none; }
.sd-option:hover,
.sd-option.is-active { background: #eff6ff; }
.sd-option .sd-main { font-weight: 600; line-height: 1.3; }
.sd-option .sd-sub  { font-size: 11px; color: #9ca3af; margin-top: 2px; }
.sd-option.sd-clear { color: #9ca3af; font-style: italic; font-weight: 400; }
.sd-no-results { padding: 12px; font-size: 13px; color: #9ca3af; text-align: center; }
</style>

{{-- Portal container — lives at body level, outside all overflow:hidden cards --}}
<div id="sd-portal"></div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2 space-y-4">

        {{-- ── Return Information ── --}}
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

                    {{-- Original Purchase --}}
                    <div>
                        <label class="{{ $labelClass }}">Original Purchase</label>
                        <div class="sd-wrap" id="purchase-sd-wrap">
                            <div class="sd-trigger" id="purchase-sd-trigger" tabindex="0">
                                <span class="sd-trigger-text placeholder" id="purchase-sd-label">— No linked purchase —</span>
                                <svg class="sd-chevron" id="purchase-sd-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                            <input type="hidden" name="purchase_id" id="purchase_id"
                                   value="{{ old('purchase_id', $purchaseReturn?->purchase_id ?? $prefillPurchase?->id) }}">
                        </div>
                        @error('purchase_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-gray-400">Selecting a purchase auto-fills supplier and purchase items.</p>
                    </div>

                    {{-- Supplier --}}
                    <div>
                        <label class="{{ $labelClass }}">Supplier</label>
                        <div class="sd-wrap" id="supplier-sd-wrap">
                            <div class="sd-trigger" id="supplier-sd-trigger" tabindex="0">
                                <span class="sd-trigger-text placeholder" id="supplier-sd-label">— No supplier —</span>
                                <svg class="sd-chevron" id="supplier-sd-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                            </div>
                            <input type="hidden" name="supplier_id" id="supplier_id"
                                   value="{{ old('supplier_id', $purchaseReturn?->supplier_id ?? $prefillPurchase?->supplier_id) }}">
                        </div>
                        @error('supplier_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Return Items ── --}}
        <div class="{{ $sectionClass }}">
            <div class="{{ $headerClass }}">
                <span class="flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                    </svg>
                </span>
                <div class="flex items-center justify-between w-full gap-3">
                    <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Return Items</span>
                    <button type="button" id="add-return-item"
                            class="h-8 px-3 inline-flex items-center gap-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
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

                                {{-- Product searchable --}}
                                <div class="xl:col-span-3">
                                    <label class="{{ $labelClass }}">Product</label>
                                    <div class="sd-wrap return-product-wrap">
                                        <div class="sd-trigger return-product-trigger" tabindex="0">
                                            <span class="sd-trigger-text placeholder">— Select product —</span>
                                            <svg class="sd-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                                        </div>
                                        <input type="hidden"
                                               name="items[{{ $index }}][product_id]"
                                               class="return-product-hidden"
                                               value="{{ $item['product_id'] ?? '' }}">
                                    </div>
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
                                    <input type="number" step="0.01" min="0.01"
                                           name="items[{{ $index }}][qty]"
                                           value="{{ $item['qty'] }}"
                                           class="return-qty {{ $errors->has('items.'.$index.'.qty') ? $inputErrClass : $inputClass }}">
                                    @error('items.'.$index.'.qty')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="xl:col-span-2">
                                    <label class="{{ $labelClass }}">Price</label>
                                    <input type="number" step="0.01" min="0"
                                           name="items[{{ $index }}][price]"
                                           value="{{ $item['price'] }}"
                                           class="return-price {{ $errors->has('items.'.$index.'.price') ? $inputErrClass : $inputClass }}">
                                    @error('items.'.$index.'.price')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="xl:col-span-2">
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
                                    Design Code: <span class="return-sku text-gray-700 font-medium">—</span>
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                                    Stock: <span class="return-stock text-gray-700 font-medium">0</span>
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                                    Purchased Qty: <span class="return-purchased-qty text-gray-700 font-medium">—</span>
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
                <label for="note" class="{{ $labelClass }}">Note</label>
                <textarea id="note" name="note" rows="3" placeholder="Any additional notes..."
                          class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">{{ old('note', $purchaseReturn?->note) }}</textarea>
                @error('note')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </div>{{-- /col-span-2 --}}

    {{-- ── Summary Sidebar ── --}}
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
                    <input type="number" id="discount" name="discount" min="0" step="0.01"
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
                            <option value="{{ $value }}" {{ $returnType === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                            <option value="{{ $value }}" {{ $returnStatus === $value ? 'selected' : '' }}>{{ $label }}</option>
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
                    <input type="text" id="cash_memo" name="cash_memo"
                           value="{{ old('cash_memo', $purchaseReturn?->cash_memo) }}"
                           placeholder="Memo number"
                           class="{{ $errors->has('cash_memo') ? $inputErrClass : $inputClass }}">
                    @error('cash_memo')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date" class="{{ $labelClass }}">Date</label>
                    <input type="date" id="date" name="date"
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

{{-- ── Row template ── --}}
<template id="return-item-template">
    <div class="return-item rounded-xl border border-gray-200 bg-gray-50/60 p-4" data-index="__INDEX__">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-3">

            <div class="xl:col-span-4">
                <label class="{{ $labelClass }}">Product</label>
                <div class="sd-wrap return-product-wrap">
                    <div class="sd-trigger return-product-trigger" tabindex="0">
                        <span class="sd-trigger-text placeholder">— Select product —</span>
                        <svg class="sd-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                    <input type="hidden" name="items[__INDEX__][product_id]" class="return-product-hidden" value="">
                </div>
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
                Design Code: <span class="return-sku text-gray-700 font-medium">—</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                Stock: <span class="return-stock text-gray-700 font-medium">0</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-gray-200 text-gray-500">
                Purchased Qty: <span class="return-purchased-qty text-gray-700 font-medium">—</span>
            </span>
        </div>
    </div>
</template>

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────
// Data
// ─────────────────────────────────────────────────────────────
const returnPurchases = @json($purchasesJson);
const returnProducts  = @json($productsJson);
const returnSuppliers = @json($suppliers->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'phone' => $s->phone ?? ''])->values());

// ─────────────────────────────────────────────────────────────
// Portal-based searchable dropdown
// Renders the panel into #sd-portal (fixed, outside all cards)
// so overflow:hidden on ancestor elements never clips it.
// ─────────────────────────────────────────────────────────────
const sdPortal = document.getElementById('sd-portal');
let activeSD = null; // currently open instance

function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
    );
}

/**
 * makeSD(config) → { setValue(id), getValue(), destroy() }
 *
 * config: {
 *   triggerEl,          — the .sd-trigger div
 *   hiddenEl,           — <input type="hidden">
 *   items,              — array of data objects
 *   placeholder,        — string shown when nothing selected
 *   labelFn(item),      — returns display label string
 *   subFn(item),        — returns subtitle string or null
 *   filterFn(item, q),  — returns bool
 *   onPick(item|null),  — callback after selection
 * }
 */
function makeSD(config) {
    const { triggerEl, hiddenEl, items, placeholder, labelFn, subFn, filterFn, onPick } = config;

    const labelEl  = triggerEl.querySelector('.sd-trigger-text');
    const chevron  = triggerEl.querySelector('.sd-chevron');

    // Build the portal panel (created once, reused)
    const panel = document.createElement('div');
    panel.className = 'sd-portal-panel';
    panel.innerHTML = `
        <div class="sd-search-box"><input type="text" placeholder="Search…" autocomplete="off"></div>
        <div class="sd-list"></div>
    `;
    const searchInput = panel.querySelector('input');
    const listEl      = panel.querySelector('.sd-list');
    sdPortal.appendChild(panel);
    panel.style.display = 'none';

    function positionPanel() {
        const rect = triggerEl.getBoundingClientRect();
        const spaceBelow = window.innerHeight - rect.bottom;
        const panelH = Math.min(270, spaceBelow > 200 ? 270 : spaceBelow - 10);

        panel.style.width  = rect.width + 'px';
        panel.style.left   = rect.left  + window.scrollX + 'px';
        panel.style.maxHeight = panelH + 'px';

        // Open upward if not enough space below
        if (spaceBelow < 220) {
            panel.style.top    = '';
            panel.style.bottom = (window.innerHeight - rect.top - window.scrollY) + 'px';
        } else {
            panel.style.bottom = '';
            panel.style.top    = rect.bottom + window.scrollY + 4 + 'px';
        }
    }

    function renderList(q) {
        q = (q || '').trim().toLowerCase();
        const hits = q ? items.filter(i => filterFn(i, q)) : items;
        listEl.innerHTML = '';

        const clearEl = document.createElement('div');
        clearEl.className = 'sd-option sd-clear';
        clearEl.textContent = placeholder;
        clearEl.addEventListener('mousedown', e => { e.preventDefault(); pick(null); });
        listEl.appendChild(clearEl);

        if (!hits.length) {
            const n = document.createElement('div');
            n.className = 'sd-no-results';
            n.textContent = 'No results found';
            listEl.appendChild(n);
            return;
        }

        hits.forEach(item => {
            const d = document.createElement('div');
            d.className = 'sd-option' + (hiddenEl.value && String(item.id) === String(hiddenEl.value) ? ' is-active' : '');
            const main = document.createElement('div');
            main.className = 'sd-main';
            main.textContent = labelFn(item);
            d.appendChild(main);
            if (subFn) {
                const sub = subFn(item);
                if (sub) {
                    const s = document.createElement('div');
                    s.className = 'sd-sub';
                    s.textContent = sub;
                    d.appendChild(s);
                }
            }
            d.addEventListener('mousedown', e => { e.preventDefault(); pick(item); });
            listEl.appendChild(d);
        });
    }

    function pick(item) {
        if (item) {
            hiddenEl.value = item.id;
            labelEl.textContent = labelFn(item);
            labelEl.classList.remove('placeholder');
        } else {
            hiddenEl.value = '';
            labelEl.textContent = placeholder;
            labelEl.classList.add('placeholder');
        }
        close();
        if (onPick) onPick(item);
    }

    function open() {
        // Close any currently open dropdown
        if (activeSD && activeSD !== instance) activeSD.close();
        activeSD = instance;

        panel.style.display = 'flex';
        positionPanel();
        triggerEl.classList.add('is-open');
        chevron.classList.add('is-open');
        searchInput.value = '';
        renderList('');
        setTimeout(() => searchInput.focus(), 30);
    }

    function close() {
        panel.style.display = 'none';
        triggerEl.classList.remove('is-open');
        chevron.classList.remove('is-open');
        if (activeSD === instance) activeSD = null;
    }

    function isOpen() { return panel.style.display !== 'none'; }

    // Prefill label from existing hidden value
    function prefill() {
        const v = hiddenEl.value;
        if (!v) return;
        const found = items.find(i => String(i.id) === String(v));
        if (found) {
            labelEl.textContent = labelFn(found);
            labelEl.classList.remove('placeholder');
        }
    }

    // Events
    triggerEl.addEventListener('click', () => isOpen() ? close() : open());
    triggerEl.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); isOpen() ? close() : open(); }
        if (e.key === 'Escape') close();
    });
    searchInput.addEventListener('input', () => renderList(searchInput.value));
    searchInput.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

    // Reposition on scroll/resize
    const reposition = () => { if (isOpen()) positionPanel(); };
    window.addEventListener('scroll', reposition, true);
    window.addEventListener('resize', reposition);

    prefill();

    const instance = {
        open, close, isOpen,
        setValue(id) {
            const found = id ? items.find(i => String(i.id) === String(id)) : null;
            pick(found || null);
        },
        getValue() { return hiddenEl.value; },
        destroy() {
            panel.remove();
            window.removeEventListener('scroll', reposition, true);
            window.removeEventListener('resize', reposition);
        }
    };
    return instance;
}

// Close active dropdown on outside click
document.addEventListener('mousedown', e => {
    if (!activeSD) return;
    const panel = [...sdPortal.children].find(p => p.style.display !== 'none');
    if (panel && panel.contains(e.target)) return;
    if (activeSD.isOpen() && !e.target.closest('.sd-trigger')) {
        activeSD.close();
    }
});

// ─────────────────────────────────────────────────────────────
// Purchase dropdown
// ─────────────────────────────────────────────────────────────
let purchaseSD, supplierSD;

function initPurchaseDropdown() {
    purchaseSD = makeSD({
        triggerEl:   document.getElementById('purchase-sd-trigger'),
        hiddenEl:    document.getElementById('purchase_id'),
        items:       returnPurchases,
        placeholder: '— No linked purchase —',
        labelFn:     p => p.reference,
        subFn: p => {
            const sup = returnSuppliers.find(s => String(s.id) === String(p.supplier_id));
            return sup ? sup.name + (sup.phone ? ' · ' + sup.phone : '') : null;
        },
        filterFn: (p, q) => {
            if (p.reference.toLowerCase().includes(q)) return true;
            const sup = returnSuppliers.find(s => String(s.id) === String(p.supplier_id));
            return sup ? sup.name.toLowerCase().includes(q) : false;
        },
        onPick: purchase => {
            if (purchase?.supplier_id) supplierSD?.setValue(purchase.supplier_id);
            rebuildRowsFromSelectedPurchase();
        }
    });
}

// ─────────────────────────────────────────────────────────────
// Supplier dropdown
// ─────────────────────────────────────────────────────────────
function initSupplierDropdown() {
    supplierSD = makeSD({
        triggerEl:   document.getElementById('supplier-sd-trigger'),
        hiddenEl:    document.getElementById('supplier_id'),
        items:       returnSuppliers,
        placeholder: '— No supplier —',
        labelFn:     s => s.name + (s.phone ? ' · ' + s.phone : ''),
        subFn:       null,
        filterFn:    (s, q) => s.name.toLowerCase().includes(q) || s.phone.includes(q),
        onPick:      null
    });
}

// ─────────────────────────────────────────────────────────────
// Product dropdown — per row
// ─────────────────────────────────────────────────────────────
function initRowProductDropdown(itemRow) {
    const triggerEl = itemRow.querySelector('.return-product-trigger');
    const hiddenEl  = itemRow.querySelector('.return-product-hidden');
    if (!triggerEl || triggerEl._sdInstance) return; // already init'd

    const sd = makeSD({
        triggerEl,
        hiddenEl,
        items:       returnProducts,
        placeholder: '— Select product —',
        labelFn:     p => p.name + (p.sku ? ' [' + p.sku + ']' : ''),
        subFn:       p => 'Stock: ' + (p.stock_qty ?? 0),
        filterFn:    (p, q) => p.name.toLowerCase().includes(q) || (p.sku || '').toLowerCase().includes(q),
        onPick: () => {
            fillPurchaseItemOptions(itemRow);
            updateItemMeta(itemRow);
            updateItemLine(itemRow);
            updateSummary();
        }
    });

    triggerEl._sdInstance = sd; // prevent double-init
}

// ─────────────────────────────────────────────────────────────
// Business logic (unchanged)
// ─────────────────────────────────────────────────────────────
function fmtMoney(value) {
    return '৳' + parseFloat(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function getSelectedPurchase() {
    const id = document.getElementById('purchase_id')?.value;
    return id ? (returnPurchases.find(p => String(p.id) === String(id)) || null) : null;
}

function getProduct(productId) {
    return returnProducts.find(p => String(p.id) === String(productId)) || null;
}

function fillPurchaseItemOptions(itemRow) {
    const purchaseItemSelect = itemRow.querySelector('.return-purchase-item');
    const hiddenEl           = itemRow.querySelector('.return-product-hidden');
    const purchasedQtyEl     = itemRow.querySelector('.return-purchased-qty');
    if (!purchaseItemSelect) return;

    const selectedPurchase  = getSelectedPurchase();
    const selectedProductId = hiddenEl?.value || '';
    const currentValue      = purchaseItemSelect.getAttribute('data-current') || purchaseItemSelect.value || '';

    purchaseItemSelect.innerHTML = '<option value="">— Optional —</option>';
    purchasedQtyEl.textContent = '—';
    if (!selectedPurchase?.items) return;

    const matchedItems = selectedPurchase.items.filter(item =>
        !selectedProductId || String(item.product_id) === String(selectedProductId)
    );

    matchedItems.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = `${item.product_name || 'Product'} · Purchased ${item.qty} @ ${item.price}`;
        opt.dataset.qty       = item.qty;
        opt.dataset.price     = item.price;
        opt.dataset.productId = item.product_id;
        purchaseItemSelect.appendChild(opt);
    });

    if (currentValue) purchaseItemSelect.value = currentValue;
    const sel = purchaseItemSelect.options[purchaseItemSelect.selectedIndex];
    if (sel?.value) purchasedQtyEl.textContent = sel.dataset.qty || '—';
}

function updateItemMeta(itemRow) {
    const product = getProduct(itemRow.querySelector('.return-product-hidden')?.value);
    itemRow.querySelector('.return-sku').textContent   = product?.sku      || '—';
    itemRow.querySelector('.return-stock').textContent = product?.stock_qty ?? 0;
}

function updateItemLine(itemRow) {
    const qty   = parseFloat(itemRow.querySelector('.return-qty')?.value   || 0);
    const price = parseFloat(itemRow.querySelector('.return-price')?.value || 0);
    itemRow.querySelector('.return-line-total').textContent = fmtMoney(qty * price);
}

function updateSummary() {
    let subtotal = 0;
    document.querySelectorAll('.return-item').forEach(row => {
        subtotal += parseFloat(row.querySelector('.return-qty')?.value   || 0)
                  * parseFloat(row.querySelector('.return-price')?.value || 0);
    });
    const discount = parseFloat(document.getElementById('discount')?.value || 0);
    document.getElementById('return-subtotal-display').textContent = fmtMoney(subtotal);
    document.getElementById('return-amount-display').textContent   = fmtMoney(Math.max(0, subtotal - discount));
}

function updateRowFromPurchaseItem(itemRow) {
    const purchaseItemSelect = itemRow.querySelector('.return-purchase-item');
    const hiddenEl           = itemRow.querySelector('.return-product-hidden');
    const labelEl            = itemRow.querySelector('.sd-trigger-text');
    const qtyInput           = itemRow.querySelector('.return-qty');
    const priceInput         = itemRow.querySelector('.return-price');
    const purchasedQtyEl     = itemRow.querySelector('.return-purchased-qty');

    const option = purchaseItemSelect.options[purchaseItemSelect.selectedIndex];
    if (option?.value) {
        if (option.dataset.productId && hiddenEl) {
            hiddenEl.value = option.dataset.productId;
            const prod = getProduct(option.dataset.productId);
            if (prod && labelEl) {
                labelEl.textContent = prod.name + (prod.sku ? ' [' + prod.sku + ']' : '');
                labelEl.classList.remove('placeholder');
            }
        }
        if (option.dataset.qty && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
            qtyInput.value = option.dataset.qty;
        }
        if (option.dataset.price) priceInput.value = option.dataset.price;
        purchasedQtyEl.textContent = option.dataset.qty || '—';
    } else {
        purchasedQtyEl.textContent = '—';
    }

    updateItemMeta(itemRow);
    updateItemLine(itemRow);
    updateSummary();
}

function bindRowEvents(itemRow) {
    initRowProductDropdown(itemRow);

    itemRow.querySelector('.return-purchase-item')?.addEventListener('change', () => updateRowFromPurchaseItem(itemRow));
    itemRow.querySelector('.return-qty')?.addEventListener('input',   () => { updateItemLine(itemRow); updateSummary(); });
    itemRow.querySelector('.return-price')?.addEventListener('input', () => { updateItemLine(itemRow); updateSummary(); });
    itemRow.querySelector('.remove-return-item')?.addEventListener('click', () => {
        if (document.querySelectorAll('.return-item').length <= 1) return;
        // Destroy portal panel for this row's product dropdown
        const trigger = itemRow.querySelector('.return-product-trigger');
        trigger?._sdInstance?.destroy();
        itemRow.remove();
        updateSummary();
    });

    fillPurchaseItemOptions(itemRow);
    updateItemMeta(itemRow);
    updateItemLine(itemRow);
}

function addReturnItemRow(data = {}) {
    const wrapper  = document.getElementById('return-items-wrapper');
    const template = document.getElementById('return-item-template').innerHTML;
    const index    = wrapper.querySelectorAll('.return-item').length;

    wrapper.insertAdjacentHTML('beforeend', template.replaceAll('__INDEX__', index));
    const itemRow = wrapper.lastElementChild;

    if (data.product_id) itemRow.querySelector('.return-product-hidden').value = data.product_id;
    if (data.qty)        itemRow.querySelector('.return-qty').value   = data.qty;
    if (data.price)      itemRow.querySelector('.return-price').value = data.price;
    if (data.purchase_item_id) {
        itemRow.querySelector('.return-purchase-item').setAttribute('data-current', data.purchase_item_id);
    }

    bindRowEvents(itemRow);
    updateSummary();
}

function rebuildRowsFromSelectedPurchase() {
    const wrapper          = document.getElementById('return-items-wrapper');
    const selectedPurchase = getSelectedPurchase();
    const hasOldInput      = {{ old('items') ? 'true' : 'false' }};
    const isEditPage       = {{ $purchaseReturn ? 'true' : 'false' }};

    if (hasOldInput || isEditPage) {
        wrapper.querySelectorAll('.return-item').forEach(bindRowEvents);
        updateSummary();
        return;
    }

    // Destroy existing product SD panels before clearing
    wrapper.querySelectorAll('.return-product-trigger').forEach(t => t._sdInstance?.destroy());
    wrapper.innerHTML = '';

    if (selectedPurchase?.items?.length) {
        selectedPurchase.items.forEach(item => addReturnItemRow({
            purchase_item_id: item.id,
            product_id:       item.product_id,
            qty:              item.qty,
            price:            item.price,
        }));
    } else {
        addReturnItemRow();
    }
}

// ─────────────────────────────────────────────────────────────
// Boot
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initPurchaseDropdown();
    initSupplierDropdown();

    document.querySelectorAll('.return-item').forEach(bindRowEvents);
    updateSummary();

    document.getElementById('discount')?.addEventListener('input', updateSummary);
    document.getElementById('add-return-item')?.addEventListener('click', () => addReturnItemRow());

    if (!document.querySelectorAll('.return-item').length) addReturnItemRow();
});
</script>
@endpush
