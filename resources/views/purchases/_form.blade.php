@php
    $purchase = $purchase ?? null;

    $initialCart = [];
    $oldItems = old('items');
    if (is_array($oldItems) && count($oldItems)) {
        $initialCart = collect($oldItems)
            ->map(function ($item) use ($products) {
                $product = $products->firstWhere('id', (int) ($item['product_id'] ?? 0));
                $qty = (float) ($item['qty'] ?? 1);
                $price = (float) ($item['price'] ?? $product?->buying_price ?? 0);

                return [
                    'product_id' => $item['product_id'] ?? null,
                    'product_name' => $product?->product_name ?? 'Unknown',
                    'sku' => $product?->sku ?? '',
                    'stock_qty' => (float) ($product?->stock?->stock_qty ?? 0),
                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $qty * $price,
                    'bale_no' => $item['bale_no'] ?? '',
                    'batch' => $item['batch'] ?? '',
                ];
            })
            ->filter(fn ($item) => $item['product_id'])
            ->values()
            ->toArray();
    } elseif ($purchase && $purchase->relationLoaded('items') && $purchase->items->count()) {
        $initialCart = $purchase->items
            ->map(
                fn($item) => [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->product_name ?? 'Unknown',
                    'sku' => $item->product->sku ?? '',
                    'stock_qty' => (float) ($item->product?->stock?->stock_qty ?? 0),
                    'qty' => (float) $item->qty,
                    'price' => (float) $item->price,
                    'line_total' => (float) $item->line_total,
                    'bale_no' => $item->bale_no ?? '',
                    'batch' => $item->batch ?? '',
                ],
            )
            ->values()
            ->toArray();
    }
@endphp

<style>
    .pos-wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 0;
        min-height: 600px;
    }

    @media (max-width: 900px) {
        .pos-wrap {
            grid-template-columns: 1fr;
        }

        .pos-sidebar {
            border-left: none !important;
            border-top: 1px solid #e5e7eb;
        }
    }

    .pos-left,
    .pos-sidebar {
        min-width: 0;
    }

    .pos-left {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .pos-sidebar {
        padding: 24px;
        background: #f8fafc;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    @media (max-width: 640px) {

        .pos-left,
        .pos-sidebar {
            padding: 16px;
        }
    }

    /* Product search */
    .search-wrap {
        position: relative;
    }

    .search-wrap input {
        width: 100%;
        height: 44px;
        padding: 0 16px 0 42px;
        font-size: 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        outline: none;
        transition: border .15s, box-shadow .15s;
    }

    .search-wrap input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
    }

    .search-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
    }

    .search-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .09);
        max-height: 240px;
        overflow-y: auto;
        z-index: 50;
        display: none;
    }

    .search-dropdown.open {
        display: block;
    }

    .search-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        cursor: pointer;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .1s;
    }

    .search-option:last-child {
        border-bottom: none;
    }

    .search-option:hover {
        background: #f0f9ff;
    }

    .search-option .sku {
        font-size: 11px;
        color: #94a3b8;
        font-family: monospace;
    }

    /* Cart */
    .cart-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #cbd5e1;
        gap: 10px;
        padding: 40px;
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        text-align: center;
    }

    .cart-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .cart-card {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 14px;
        animation: slideIn .18s ease;
        min-width: 0;
        flex-wrap: wrap;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    .cart-card .prod-info {
        flex: 1;
        min-width: 0;
    }

    .cart-card .prod-name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cart-card .prod-sku {
        font-size: 11px;
        color: #94a3b8;
        font-family: monospace;
        word-break: break-all;
    }

    .cart-card .prod-stock {
        font-size: 11px;
        color: #16a34a;
        font-weight: 600;
        margin-top: 2px;
    }

    .cart-card .price-col {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .cart-card .line-total {
        font-size: 14px;
        font-weight: 700;
        color: #16a34a;
        min-width: 80px;
        text-align: right;
    }

    /* Bale No row — full width below main row */
    .cart-card .bale-row {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-top: 8px;
        border-top: 1px solid #f1f5f9;
        margin-top: 2px;
    }

    .cart-card .bale-row label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .bale-input {
        flex: 1;
        height: 32px;
        padding: 0 10px;
        font-size: 13px;
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
        background: #fff;
        outline: none;
        transition: border .15s, box-shadow .15s;
        min-width: 0;
    }

    .bale-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
    }

    .price-edit {
        width: 110px;
        height: 38px;
        font-size: 14px;
        padding: 0 10px;
        border: 1.5px solid #e2e8f0;
        border-radius: 7px;
    }

    .price-edit:focus {
        outline: none;
        border-color: #3b82f6;
    }

    .qty-stepper {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .qty-stepper button {
        width: 34px;
        height: 38px;
        font-size: 18px;
        font-weight: 600;
        background: #f8fafc;
        border: none;
        cursor: pointer;
        color: #475569;
        transition: background .12s;
    }

    .qty-stepper button:hover {
        background: #e2e8f0;
    }

    .qty-stepper input {
        width: 60px;
        height: 38px;
        font-size: 14px;
        border: none;
        border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        text-align: center;
        font-weight: 600;
        outline: none;
        background: #fff;
        color: #0f172a;
    }

    .qty-stepper input::-webkit-inner-spin-button,
    .qty-stepper input::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }

    .btn-remove {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: none;
        background: #fef2f2;
        color: #ef4444;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .12s;
        flex-shrink: 0;
    }

    .btn-remove:hover {
        background: #fee2e2;
    }

    @media (max-width: 640px) {
        .cart-card .prod-name {
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
            word-break: break-word;
        }

        .qty-stepper {
            order: 2;
        }

        .cart-card .price-col {
            order: 3;
            align-items: stretch;
            width: calc(100% - 44px);
        }

        .price-edit {
            width: 100%;
        }

        .btn-remove {
            order: 4;
            margin-left: auto;
        }

        .cart-card .bale-row {
            order: 5;
        }
    }

    /* Form fields */
    .field-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .field-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .field-input {
        width: 100%;
        height: 38px;
        padding: 0 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        outline: none;
        transition: border .15s, box-shadow .15s;
        min-width: 0;
    }

    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
    }

    .field-input[readonly] {
        background: #f1f5f9;
        color: #64748b;
        cursor: default;
    }

    select.field-input {
        cursor: pointer;
    }

    textarea.field-input {
        height: auto;
        padding: 8px 12px;
        resize: vertical;
    }

    /* Supplier searchable dropdown */
    .sup-wrap {
        position: relative;
    }

    .sup-row {
        display: flex;
        gap: 8px;
    }

    .sup-row .sup-wrap {
        flex: 1;
        min-width: 0;
    }

    .supplier-add-btn {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 8px;
        border: 1.5px solid #bfdbfe;
        background: #eff6ff;
        color: #2563eb;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sup-display {
        width: 100%;
        height: 38px;
        padding: 0 34px 0 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        outline: none;
        transition: border .15s, box-shadow .15s;
        cursor: pointer;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: left;
        appearance: none;
        min-width: 0;
    }

    .sup-display:focus,
    .sup-display.open {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
    }

    .sup-display::placeholder {
        color: #94a3b8;
    }

    .sup-chevron {
        position: absolute;
        right: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        pointer-events: none;
        transition: transform .2s;
    }

    .sup-chevron.open {
        transform: translateY(-50%) rotate(180deg);
    }

    .sup-dropdown {
        position: absolute;
        top: calc(100% + 5px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .09);
        z-index: 100;
        display: none;
        flex-direction: column;
        overflow: hidden;
    }

    .sup-dropdown.open {
        display: flex;
    }

    .sup-search-box {
        padding: 8px;
        border-bottom: 1px solid #f1f5f9;
    }

    .sup-search-box input {
        width: 100%;
        height: 32px;
        padding: 0 10px;
        font-size: 13px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #f8fafc;
        outline: none;
        color: #1e293b;
        transition: border .15s;
    }

    .sup-search-box input:focus {
        border-color: #3b82f6;
    }

    .sup-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .sup-option {
        display: flex;
        flex-direction: column;
        padding: 9px 12px;
        cursor: pointer;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .1s;
    }

    .sup-option:last-child {
        border-bottom: none;
    }

    .sup-option:hover {
        background: #f0f9ff;
    }

    .sup-option.sup-selected {
        background: #eff6ff;
    }

    .sup-option .sup-name {
        font-weight: 600;
        color: #1e293b;
        word-break: break-word;
    }

    .sup-option .sup-phone {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 1px;
    }

    .sup-option.sup-clear .sup-name {
        color: #94a3b8;
        font-style: italic;
        font-weight: 400;
    }

    .sup-no-results {
        padding: 12px;
        font-size: 13px;
        color: #94a3b8;
        text-align: center;
    }

    /* Misc */
    .divider {
        border: none;
        border-top: 1px solid #e5e7eb;
        margin: 0;
    }

    .totals-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        font-size: 13px;
    }

    .totals-row.grand {
        font-size: 16px;
        font-weight: 700;
        margin-top: 4px;
    }

    .totals-row .label {
        color: #64748b;
    }

    .totals-row .val {
        color: #1e293b;
        text-align: right;
        word-break: break-word;
    }

    .totals-row.grand .val {
        color: #16a34a;
        font-size: 20px;
    }

    .text-error {
        font-size: 12px;
        color: #dc2626;
    }
</style>

<div class="pos-wrap">

    {{-- LEFT — Product search + cart --}}
    <div class="pos-left">
        <div>
            <div class="field-label mb-2">Search Products</div>
            <div class="search-wrap" id="search-wrap">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.35-4.35" />
                </svg>
                <input type="text" id="product-search" placeholder="Search by name or design code…" autocomplete="off">
                <div class="search-dropdown" id="search-dropdown">
                    @foreach ($products as $product)
                        <div class="search-option" data-id="{{ $product->id }}"
                            data-name="{{ $product->product_name }}" data-sku="{{ $product->sku }}"
                            data-price="{{ $product->buying_price ?? 0 }}">
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:600;color:#1e293b;word-break:break-word">
                                    {{ $product->product_name }}</div>
                                <div class="sku">{{ $product->sku }}</div>
                            </div>
                            <div style="font-size:12px;font-weight:600;color:#16a34a;flex-shrink:0">
                                ৳{{ number_format($product->buying_price ?? 0, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @error('items')
                <p class="text-error mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div id="cart-container" style="flex:1; display:flex; flex-direction:column; gap:10px; min-width:0;">
            <div class="field-label">Purchase Items</div>
            <div id="cart-empty" class="cart-empty">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5"
                    viewBox="0 0 24 24">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                    <path d="M3 6h18" />
                    <path d="M16 10a4 4 0 0 1-8 0" />
                </svg>
                <span>No items added yet.<br>Search and select a product above.</span>
            </div>
            <div id="cart-list" class="cart-list"></div>
        </div>
    </div>

    {{-- RIGHT — Sidebar / form fields --}}
    <div class="pos-sidebar">

        <div class="field-group">
            <div class="field-label">Supplier</div>
            <div class="sup-row">
            <div class="sup-wrap" id="sup-wrap">
                <input type="text" id="sup-display" class="sup-display" placeholder="— Select supplier —" readonly>
                <input type="hidden" name="supplier_id" id="supplier_id"
                    value="{{ old('supplier_id', $purchase?->supplier_id) }}">
                <svg class="sup-chevron" id="sup-chevron" width="14" height="14" fill="none"
                    stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6" />
                </svg>
                <div class="sup-dropdown" id="sup-dropdown">
                    <div class="sup-search-box">
                        <input type="text" id="sup-search" placeholder="Search by name or phone…" autocomplete="off">
                    </div>
                    <div class="sup-list" id="sup-list"></div>
                </div>
            </div>
            <button type="button" class="supplier-add-btn" onclick="openSupplierModal()" title="Add supplier">+</button>
            </div>
        </div>

        <div class="field-group">
            <div class="field-label">Seller / Store Name</div>
            <input type="text" name="seller_store_name"
                value="{{ old('seller_store_name', $purchase?->seller_store_name) }}" class="field-input"
                placeholder="Optional store name">
        </div>

        <div class="field-group">
            <div class="field-label">Purchased By</div>
            <input type="text" name="purchased_by" value="{{ old('purchased_by', $purchase?->purchased_by) }}"
                class="field-input" placeholder="Enter purchaser name">
            @error('purchased_by')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- ↓ NEW: Bill No --}}
        <div class="field-group">
            <div class="field-label">Bill No</div>
            <input type="text" name="bill_no" value="{{ old('bill_no', $purchase?->bill_no) }}"
                class="field-input" placeholder="Supplier bill / invoice number">
            @error('bill_no')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <hr class="divider">

        <div style="display:flex;flex-direction:column;gap:8px">
            <div class="totals-row">
                <span class="label">Items Total</span>
                <span class="val" id="subtotal-display">৳0.00</span>
            </div>

            <div class="totals-row grand" style="padding-top:4px;border-top:1px solid #e5e7eb;margin-top:4px">
                <span class="label">Grand Total</span>
                <span class="val" id="grand-total-display">৳0.00</span>
            </div>
        </div>

        <hr class="divider">

        <div class="field-group">
            <div class="field-label">Purchase Status</div>
            <select name="purchase_status" class="field-input">
                <option value="received" @selected(old('purchase_status', $purchase?->purchase_status) === 'received')>Received</option>
                <option value="partial" @selected(old('purchase_status', $purchase?->purchase_status) === 'partial')>Partial</option>
                <option value="pending" @selected(old('purchase_status', $purchase?->purchase_status ?? 'pending') === 'pending')>Pending</option>
                <option value="ordered" @selected(old('purchase_status', $purchase?->purchase_status) === 'ordered')>Ordered</option>
            </select>
            @error('purchase_status')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Payment Status</div>
            <select name="payment_status" id="payment_status" class="field-input">
                <option value="due" @selected(old('payment_status', $purchase?->payment_status ?? 'due') === 'due')>Due</option>
                <option value="paid" @selected(old('payment_status', $purchase?->payment_status) === 'paid')>Paid</option>
                <option value="partial" @selected(old('payment_status', $purchase?->payment_status) === 'partial')>Partial</option>
            </select>
            @error('payment_status')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div id="paid-field" class="field-group" style="display:none">
            <div class="field-label">Paid Amount (৳)</div>
            <input type="number" name="paid_amount" id="paid_amount"
                value="{{ old('paid_amount', $purchase?->paid_amount ?? 0) }}" step="0.01" min="0"
                class="field-input">
            @error('paid_amount')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div id="due-field" class="field-group" style="display:none">
            <div class="field-label">Due Amount (৳)</div>
            <input type="number" name="due_amount" id="due_amount"
                value="{{ old('due_amount', $purchase?->due_amount ?? 0) }}" step="0.01" min="0" readonly
                class="field-input">
        </div>

        <div class="field-group">
            <div class="field-label">Payment Method</div>
            <select name="payment_method" class="field-input">
                <option value="">— Select —</option>
                @foreach (['Cash', 'Bank', 'Bkash', 'Nagad', 'Card'] as $method)
                    <option value="{{ $method }}" @selected(old('payment_method', $purchase?->payment_method) == $method)>{{ $method }}</option>
                @endforeach
            </select>
            @error('payment_method')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <hr class="divider">

        <div class="field-group">
            <div class="field-label">Cash Memo #</div>
            <input type="text" name="cash_memo" value="{{ old('cash_memo', $purchase?->cash_memo) }}"
                class="field-input">
            @error('cash_memo')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Date</div>
            <input type="date" name="date"
                value="{{ old('date', optional($purchase?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                class="field-input">
            @error('date')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Document</div>
            <input type="file" name="document" class="field-input" style="padding:6px 10px;height:auto">
            @if ($purchase?->document)
                <a href="{{ asset('storage/' . $purchase->document) }}" target="_blank"
                    class="text-xs text-blue-600 hover:underline break-all">
                    View current file
                </a>
            @endif
            @error('document')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Note</div>
            <textarea name="note" rows="2" class="field-input">{{ old('note', $purchase?->note) }}</textarea>
            @error('note')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

<input type="hidden" name="reference" value="{{ $nextReference ?? $purchase?->reference }}">

<div
    id="supplierModal"
    class="hidden"
    style="position:fixed;inset:0;z-index:80;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.4);padding:16px"
    onclick="closeSupplierModal(event)"
>
    <div
        style="background:#fff;border-radius:14px;width:100%;max-width:440px;box-shadow:0 20px 50px rgba(0,0,0,.15)"
        onclick="event.stopPropagation()"
    >
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <span style="font-weight:700;font-size:14px">New Supplier</span>
            <button type="button" onclick="closeSupplierModal()" style="border:none;background:none;font-size:18px;color:#94a3b8;cursor:pointer">×</button>
        </div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:12px">
            <input type="text" id="new_supplier_name" placeholder="Supplier Name *" class="field-input">
            <input type="text" id="new_supplier_phone" placeholder="Phone" class="field-input">            <textarea id="new_supplier_address" placeholder="Address" rows="3" class="field-input"></textarea>
            <div id="supplier-modal-error" class="hidden" style="font-size:12px;color:#ef4444"></div>
        </div>
        <div style="padding:12px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:8px">
            <button type="button" onclick="closeSupplierModal()" style="height:36px;padding:0 16px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;background:#fff">Cancel</button>
            <button type="button" id="save-supplier-btn" style="height:36px;padding:0 18px;border:none;border-radius:8px;font-size:13px;font-weight:600;background:#2563eb;color:#fff;cursor:pointer">Save</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // ─────────────────────────────────────────────────────────────
        // Cart state
        // ─────────────────────────────────────────────────────────────
        let cartItems = @json($initialCart);

        const ALL_PRODUCTS = [
            @foreach ($products as $p)
                {
                    id: "{{ $p->id }}",
                    name: @json($p->product_name),
                    sku: "{{ $p->sku }}",
                    price: {{ $p->buying_price ?? 0 }},
                    stock_qty: {{ (float) ($p->stock?->stock_qty ?? 0) }}
                },
            @endforeach
        ];

        // ─────────────────────────────────────────────────────────────
        // Supplier data
        // ─────────────────────────────────────────────────────────────
        const ALL_SUPPLIERS = [
            @foreach ($suppliers as $s)
                {
                    id: "{{ $s->id }}",
                    name: @json($s->name),
                    phone: "{{ $s->phone ?? '' }}"
                },
            @endforeach
        ];

        // ─────────────────────────────────────────────────────────────
        // DOM refs — product search
        // ─────────────────────────────────────────────────────────────
        const searchInput = document.getElementById('product-search');
        const dropdown    = document.getElementById('search-dropdown');
        const cartList    = document.getElementById('cart-list');
        const cartEmpty   = document.getElementById('cart-empty');
        const discountInput  = document.getElementById('discount');
        const otherCostInput = document.getElementById('other_cost');
        const subtotalSpan   = document.getElementById('subtotal-display');
        const grandSpan      = document.getElementById('grand-total-display');
        const payStatus  = document.getElementById('payment_status');
        const paidField  = document.getElementById('paid-field');
        const dueField   = document.getElementById('due-field');
        const paidInput  = document.getElementById('paid_amount');
        const dueInput   = document.getElementById('due_amount');

        // ─────────────────────────────────────────────────────────────
        // Product search
        // ─────────────────────────────────────────────────────────────
        searchInput?.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            if (!q) { dropdown.classList.remove('open'); return; }

            const matches = ALL_PRODUCTS.filter(p =>
                p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
            );

            dropdown.innerHTML = matches.length
                ? matches.slice(0, 12).map(p => `
                    <div class="search-option"
                         data-id="${p.id}"
                         data-name="${escHtml(p.name)}"
                         data-sku="${escHtml(p.sku)}"
                         data-price="${p.price}">
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;color:#1e293b;word-break:break-word">${escHtml(p.name)}</div>
                            <div class="sku">${escHtml(p.sku)} · Stock: ${formatQty(p.stock_qty)}</div>
                        </div>
                        <div style="font-size:12px;font-weight:600;color:#16a34a;flex-shrink:0">৳${Number(p.price).toFixed(2)}</div>
                    </div>
                `).join('')
                : '<div style="padding:14px 16px;font-size:13px;color:#94a3b8">No products found</div>';

            dropdown.classList.add('open');
            attachDropdownEvents();
        });

        document.addEventListener('click', e => {
            const wrap = document.getElementById('search-wrap');
            if (wrap && !wrap.contains(e.target)) dropdown.classList.remove('open');
        });

        function attachDropdownEvents() {
            dropdown.querySelectorAll('.search-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    const product = ALL_PRODUCTS.find(p => String(p.id) === String(opt.dataset.id));
                    addToCart(opt.dataset.id, opt.dataset.name, opt.dataset.sku, parseFloat(opt.dataset.price), product?.stock_qty ?? 0);
                    searchInput.value = '';
                    dropdown.classList.remove('open');
                });
            });
        }

        // ─────────────────────────────────────────────────────────────
        // Cart logic
        // ─────────────────────────────────────────────────────────────
        function addToCart(id, name, sku, price, stockQty = 0) {
            const existing = cartItems.find(i => String(i.product_id) === String(id));
            if (existing) {
                existing.qty += 1;
                existing.stock_qty = stockQty;
                existing.line_total = existing.qty * existing.price;
            } else {
                cartItems.push({
                    product_id: id,
                    product_name: name,
                    sku: sku || '',
                    stock_qty: stockQty,
                    qty: 1,
                    price,
                    line_total: price,
                    bale_no: '',
                    batch: ''
                });
            }
            renderCart();
        }

        function renderCart() {
            cartEmpty.style.display = cartItems.length ? 'none' : 'flex';
            cartList.innerHTML = '';

            cartItems.forEach((item, idx) => {
                const card = document.createElement('div');
                card.className = 'cart-card';
                card.innerHTML = `
                    <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id}">

                    <div class="prod-info">
                        <div class="prod-name">${escHtml(item.product_name)}</div>
                        <div class="prod-sku">${escHtml(item.sku)}</div>
                        <div class="prod-stock">Available: ${formatQty(item.stock_qty)}</div>
                    </div>

                    <div class="qty-stepper">
                        <button type="button" class="btn-minus" data-idx="${idx}">−</button>
                        <input type="number" name="items[${idx}][qty]" class="item-qty" data-idx="${idx}"
                               value="${item.qty}" step="0.01" min="0.01">
                        <button type="button" class="btn-plus" data-idx="${idx}">+</button>
                    </div>

                    <div class="price-col">
                        <input type="number" name="items[${idx}][price]" class="price-edit item-price"
                               data-idx="${idx}" value="${Number(item.price).toFixed(2)}"
                               step="0.01" min="0" title="Unit price">
                        <div class="line-total item-total">৳${Number(item.line_total).toFixed(2)}</div>
                    </div>

                    <button type="button" class="btn-remove" data-idx="${idx}" title="Remove">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M18 6 6 18M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- ↓ NEW: Bale No row --}}
                    <div class="bale-row">
                        <label for="bale_no_${idx}">Bale No</label>
                        <input type="text"
                               id="bale_no_${idx}"
                               name="items[${idx}][bale_no]"
                               class="bale-input item-bale"
                               data-idx="${idx}"
                               value="${escHtml(item.bale_no || '')}"
                               placeholder="Optional bale / bundle number">
                    </div>
                    <div class="bale-row">
                        <label for="batch_${idx}">Batch</label>
                        <input type="text"
                               id="batch_${idx}"
                               name="items[${idx}][batch]"
                               class="bale-input item-batch"
                               data-idx="${idx}"
                               value="${escHtml(item.batch || '')}"
                               placeholder="Batch code">
                    </div>
                `;
                cartList.appendChild(card);
            });

            attachCardEvents();
            recalc();
        }

        function attachCardEvents() {
            cartList.querySelectorAll('.btn-minus').forEach(b => b.addEventListener('click', e => {
                const i = +e.currentTarget.dataset.idx;
                cartItems[i].qty > 1 ? (cartItems[i].qty -= 1, updateItem(i)) : removeItem(i);
            }));
            cartList.querySelectorAll('.btn-plus').forEach(b => b.addEventListener('click', e => {
                const i = +e.currentTarget.dataset.idx;
                cartItems[i].qty += 1;
                updateItem(i);
            }));
            cartList.querySelectorAll('.item-qty').forEach(inp => inp.addEventListener('change', e => {
                const i = +e.target.dataset.idx;
                const v = parseFloat(e.target.value) || 1;
                cartItems[i].qty = v < 0.01 ? 0.01 : v;
                updateItem(i);
            }));
            cartList.querySelectorAll('.item-price').forEach(inp => inp.addEventListener('change', e => {
                const i = +e.target.dataset.idx;
                cartItems[i].price = parseFloat(e.target.value) || 0;
                updateItem(i);
            }));
            // ↓ NEW: keep bale_no in state so it survives re-renders
            cartList.querySelectorAll('.item-bale').forEach(inp => inp.addEventListener('input', e => {
                const i = +e.target.dataset.idx;
                cartItems[i].bale_no = e.target.value;
            }));
            cartList.querySelectorAll('.item-batch').forEach(inp => inp.addEventListener('input', e => {
                const i = +e.target.dataset.idx;
                cartItems[i].batch = e.target.value;
            }));
            cartList.querySelectorAll('.btn-remove').forEach(b => b.addEventListener('click', e => {
                removeItem(+e.currentTarget.dataset.idx);
            }));
        }

        function updateItem(idx) {
            cartItems[idx].line_total = cartItems[idx].qty * cartItems[idx].price;
            const qtyInp = cartList.querySelector(`.item-qty[data-idx="${idx}"]`);
            if (qtyInp) qtyInp.value = cartItems[idx].qty;
            const totalEl = cartList.querySelectorAll('.item-total')[idx];
            if (totalEl) totalEl.textContent = '৳' + cartItems[idx].line_total.toFixed(2);
            recalc();
        }

        function removeItem(idx) {
            cartItems.splice(idx, 1);
            renderCart();
        }

        // ─────────────────────────────────────────────────────────────
        // Totals
        // ─────────────────────────────────────────────────────────────
        function recalc() {
            const sub   = cartItems.reduce((s, i) => s + i.line_total, 0);
            const disc  = parseFloat(discountInput?.value)  || 0;
            const other = parseFloat(otherCostInput?.value) || 0;
            const grand = Math.max(0, sub + other - disc);

            subtotalSpan.textContent = '৳' + sub.toFixed(2);
            grandSpan.textContent    = '৳' + grand.toFixed(2);
            updatePayment(grand);
        }

        function updatePayment(grand) {
            const s = payStatus.value;
            paidField.style.display = (s === 'paid' || s === 'partial') ? 'flex' : 'none';
            dueField.style.display  = (s === 'due'  || s === 'partial') ? 'flex' : 'none';

            if (s === 'paid') {
                paidInput.value = grand.toFixed(2);
                dueInput.value  = '0.00';
            } else if (s === 'due') {
                paidInput.value = '0.00';
                dueInput.value  = grand.toFixed(2);
            } else {
                const paid = Math.min(parseFloat(paidInput.value) || 0, grand);
                paidInput.value = paid.toFixed(2);
                dueInput.value  = (grand - paid).toFixed(2);
            }
        }

        function getGrand() {
            return Math.max(
                0,
                cartItems.reduce((s, i) => s + i.line_total, 0) +
                (parseFloat(otherCostInput?.value) || 0) -
                (parseFloat(discountInput?.value)  || 0)
            );
        }

        discountInput?.addEventListener('input', recalc);
        otherCostInput?.addEventListener('input', recalc);
        payStatus?.addEventListener('change', recalc);
        paidInput?.addEventListener('input', () => {
            if (payStatus.value === 'partial') {
                const g = getGrand();
                const p = Math.min(parseFloat(paidInput.value) || 0, g);
                dueInput.value = (g - p).toFixed(2);
            }
        });

        // ─────────────────────────────────────────────────────────────
        // Supplier searchable dropdown
        // ─────────────────────────────────────────────────────────────
        (function () {
            const supDisplay  = document.getElementById('sup-display');
            const supHidden   = document.getElementById('supplier_id');
            const supDropdown = document.getElementById('sup-dropdown');
            const supChevron  = document.getElementById('sup-chevron');
            const supSearch   = document.getElementById('sup-search');
            const supList     = document.getElementById('sup-list');
            const supWrap     = document.getElementById('sup-wrap');

            if (!supDisplay) return;

            const preId = supHidden.value;
            if (preId) {
                const pre = ALL_SUPPLIERS.find(s => String(s.id) === String(preId));
                if (pre) supDisplay.value = pre.name + (pre.phone ? ' · ' + pre.phone : '');
            }

            function renderSupList(q = '') {
                const hits = ALL_SUPPLIERS.filter(s =>
                    !q ||
                    s.name.toLowerCase().includes(q.toLowerCase()) ||
                    (s.phone && s.phone.includes(q))
                );

                supList.innerHTML = '';

                const clearEl = document.createElement('div');
                clearEl.className = 'sup-option sup-clear';
                clearEl.innerHTML = '<span class="sup-name">— Select supplier —</span>';
                clearEl.addEventListener('click', () => pickSupplier(null));
                supList.appendChild(clearEl);

                if (!hits.length) {
                    const noEl = document.createElement('div');
                    noEl.className = 'sup-no-results';
                    noEl.textContent = 'No suppliers found';
                    supList.appendChild(noEl);
                    return;
                }

                hits.forEach(s => {
                    const d = document.createElement('div');
                    d.className = 'sup-option' + (supHidden.value && String(s.id) === String(supHidden.value) ? ' sup-selected' : '');
                    d.innerHTML = `<span class="sup-name">${escHtml(s.name)}</span>` +
                        (s.phone ? `<span class="sup-phone">${escHtml(s.phone)}</span>` : '');
                    d.addEventListener('click', () => pickSupplier(s));
                    supList.appendChild(d);
                });
            }

            function pickSupplier(s) {
                if (s) {
                    supDisplay.value = s.name + (s.phone ? ' · ' + s.phone : '');
                    supHidden.value  = s.id;
                } else {
                    supDisplay.value = '';
                    supHidden.value  = '';
                }
                closeSupDropdown();
            }

            function openSupDropdown() {
                supDropdown.classList.add('open');
                supChevron.classList.add('open');
                supSearch.value = '';
                renderSupList();
                setTimeout(() => supSearch.focus(), 50);
            }

            function closeSupDropdown() {
                supDropdown.classList.remove('open');
                supChevron.classList.remove('open');
            }

            supDisplay.addEventListener('click', () =>
                supDropdown.classList.contains('open') ? closeSupDropdown() : openSupDropdown()
            );

            supSearch.addEventListener('input', () => renderSupList(supSearch.value.trim()));

            document.addEventListener('click', e => {
                if (!supWrap.contains(e.target)) closeSupDropdown();
            });
        })();

        // ─────────────────────────────────────────────────────────────
        // Helpers
        // ─────────────────────────────────────────────────────────────
        function openSupplierModal() {
            const modal = document.getElementById('supplierModal');
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
        }

        function closeSupplierModal(event) {
            if (!event || event.target === document.getElementById('supplierModal')) {
                const modal = document.getElementById('supplierModal');
                modal.style.display = 'none';
                modal.classList.add('hidden');
            }
        }

        document.getElementById('save-supplier-btn')?.addEventListener('click', async () => {
            const name = document.getElementById('new_supplier_name').value.trim();
            const phone = document.getElementById('new_supplier_phone').value.trim();
            const address = document.getElementById('new_supplier_address').value.trim();
            const error = document.getElementById('supplier-modal-error');
            const button = document.getElementById('save-supplier-btn');

            if (!name) {
                error.textContent = 'Supplier name is required';
                error.classList.remove('hidden');
                return;
            }

            error.classList.add('hidden');
            button.disabled = true;

            try {
                const response = await fetch('{{ route('suppliers.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, phone , address })
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || 'Could not save supplier');
                }

                ALL_SUPPLIERS.push({
                    id: String(data.id),
                    name: data.name,
                    phone: data.phone || ''
                });

                document.getElementById('supplier_id').value = data.id;
                document.getElementById('sup-display').value = data.name + (data.phone ? ' · ' + data.phone : '');

                closeSupplierModal();
                document.getElementById('new_supplier_name').value = '';
                document.getElementById('new_supplier_phone').value = '';
                document.getElementById('new_supplier_address').value = '';
            } catch (exception) {
                error.textContent = exception.message;
                error.classList.remove('hidden');
            } finally {
                button.disabled = false;
            }
        });

        function escHtml(s) {
            return String(s).replace(/[&<>"']/g, c => (
                { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
            ));
        }

        function formatQty(qty) {
            const value = Number(qty) || 0;
            return Number.isInteger(value) ? String(value) : value.toFixed(2);
        }

        // ─────────────────────────────────────────────────────────────
        // Init
        // ─────────────────────────────────────────────────────────────
        renderCart();
        recalc();
        setTimeout(recalc, 100);
    </script>
@endpush

