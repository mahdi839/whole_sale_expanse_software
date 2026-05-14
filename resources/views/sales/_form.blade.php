{{-- resources/views/sales/_form.blade.php --}}
@php
    $sale = $sale ?? null;

    $initialCart = [];
    if ($sale && $sale->relationLoaded('items') && $sale->items->count()) {
        $initialCart = $sale->items
            ->map(fn($item) => [
                'product_id'    => $item->product_id,
                'product_name'  => $item->product->product_name ?? 'Unknown',
                'sku'           => $item->product->sku ?? '',
                'purchase_item_id' => $item->purchase_item_id,
                'batch'         => $item->batch ?? '',
                'stock_qty'     => (float) ($item->product?->stocks?->firstWhere('shop_id', $sale->shop_id)?->stock_qty ?? 0),
                'qty'           => (float) $item->qty,
                'price_on_sale' => (float) $item->price_on_sale,
                'line_total'    => (float) $item->line_total,
            ])
            ->values()
            ->toArray();
    }

    $productPayload = $products->map(function ($product) {
        return [
            'id' => (string) $product->id,
            'name' => $product->product_name,
            'sku' => $product->sku,
            'price' => (float) ($product->selling_price ?? 0),
            'stocks' => $product->stocks->mapWithKeys(fn ($stock) => [
                (string) $stock->shop_id => (float) $stock->stock_qty,
            ]),
            'batches' => $product->purchaseItems->map(function ($item) {
                $returned = $item->returnItems->sum(fn ($returnItem) => (float) $returnItem->qty);
                $sold = $item->saleItems->sum(fn ($saleItem) => (float) $saleItem->qty);
                $salesReturned = $item->saleItems
                    ->flatMap(fn ($saleItem) => $saleItem->returnItems)
                    ->sum(fn ($returnItem) => (float) $returnItem->qty);

                return [
                    'id' => $item->id,
                    'batch' => $item->batch ?: ('Batch #'.$item->id),
                    'price' => (float) $item->price,
                    'available_qty' => max(0, (float) $item->qty - $returned - $sold + $salesReturned),
                ];
            })->values(),
        ];
    })->values();
@endphp

<style>
    /* ---- POS Layout ---- */
    .pos-wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 380px;
        gap: 0;
        min-height: 600px;
    }

    @media (max-width: 900px) {
        .pos-wrap { grid-template-columns: 1fr; }
        .pos-sidebar { border-left: none !important; border-top: 1px solid #e5e7eb; }
    }

    .pos-left, .pos-sidebar { min-width: 0; }

    .pos-left {
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: relative;
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
        .pos-left, .pos-sidebar { padding: 16px; }
    }

    /* Search */
    .product-search-panel {
        position: sticky;
        top: 0;
        z-index: 60;
        background: #fff;
        margin: -24px -24px 0;
        padding: 16px 24px 12px;
        border-bottom: 1px solid #eef2f7;
        box-shadow: 0 8px 18px rgba(15, 23, 42, .06);
    }

    @media (max-width: 640px) {
        .product-search-panel {
            margin: -16px -16px 0;
            padding: 12px 16px 10px;
        }
    }

    .search-wrap { position: relative; }

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
        box-shadow: 0 0 0 3px rgba(59,130,246,.12);
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
        left: 0; right: 0;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.09);
        max-height: 240px;
        overflow-y: auto;
        z-index: 50;
        display: none;
    }

    .search-dropdown.open { display: block; }

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

    .search-option:last-child { border-bottom: none; }
    .search-option:hover { background: #f0f9ff; }

    .search-option .sku {
        font-size: 11px;
        color: #94a3b8;
        font-family: monospace;
        word-break: break-all;
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

    .cart-list { display: flex; flex-direction: column; gap: 10px; }

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
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: none; }
    }

    .cart-card .prod-info { flex: 1; min-width: 0; }

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
        flex-direction: column;
        align-items: flex-end;
        gap: 2px;
        flex-shrink: 0;
    }

    .cart-card .line-total {
        font-size: 13px;
        font-weight: 700;
        color: #1d4ed8;
    }

    .price-edit {
        width: 90px;
        height: 32px;
        padding: 0 8px;
        font-size: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        text-align: right;
    }

    .price-edit:focus { outline: none; border-color: #3b82f6; }

    /* Qty stepper */
    .qty-stepper {
        display: flex;
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .qty-stepper button {
        width: 32px; height: 32px;
        font-size: 16px; font-weight: 600;
        background: #f8fafc;
        border: none; cursor: pointer;
        color: #475569;
        transition: background .12s;
    }

    .qty-stepper button:hover { background: #e2e8f0; }

    .qty-stepper input {
        width: 80px; height: 32px;
        border: none;
        border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        text-align: center;
        font-size: 13px; font-weight: 600;
        outline: none;
        background: #fff; color: #0f172a;
    }

    .qty-stepper input::-webkit-inner-spin-button,
    .qty-stepper input::-webkit-outer-spin-button { -webkit-appearance: none; }

    .btn-remove {
        width: 28px; height: 28px;
        border-radius: 6px; border: none;
        background: #fef2f2; color: #ef4444;
        cursor: pointer; font-size: 14px;
        display: flex; align-items: center; justify-content: center;
        transition: background .12s;
        flex-shrink: 0;
    }

    .btn-remove:hover { background: #fee2e2; }

    @media (max-width: 640px) {
        .cart-card { flex-wrap: wrap; align-items: flex-start; padding: 12px; }
        .cart-card .prod-info { width: 100%; }
        .cart-card .prod-name { white-space: normal; overflow: visible; text-overflow: unset; word-break: break-word; }
        .qty-stepper { order: 2; }
        .cart-card .price-col { order: 3; width: calc(100% - 44px); align-items: stretch; }
        .price-edit { width: 100%; }
        .btn-remove { order: 4; margin-left: auto; }
    }

    /* Sidebar fields */
    .field-group { display: flex; flex-direction: column; gap: 6px; min-width: 0; }

    .field-label {
        font-size: 11px; font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .field-input {
        width: 100%; height: 38px;
        padding: 0 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        background: #fff; outline: none;
        transition: border .15s, box-shadow .15s;
        min-width: 0;
    }

    .field-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }

    .field-input[readonly] { background: #f1f5f9; color: #64748b; cursor: default; }
    select.field-input { cursor: pointer; }
    textarea.field-input { height: auto; padding: 8px 12px; resize: vertical; }

    .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0; }

    /* Totals */
    .totals-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        font-size: 13px;
    }

    .totals-row.grand { font-size: 16px; font-weight: 700; margin-top: 4px; }
    .totals-row .label { color: #64748b; }
    .totals-row .val { color: #1e293b; text-align: right; word-break: break-word; }
    .totals-row.grand .val { color: #16a34a; font-size: 20px; }

    @media (max-width: 640px) {
        .customer-select-row { flex-direction: column; }
        .customer-select-row > button { width: 100% !important; height: 40px !important; }
        .customer-modal-actions { flex-direction: column-reverse; }
        .customer-modal-actions button { width: 100%; justify-content: center; }
    }

    /* Two-column grid for reference fields */
    .field-row-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    @media (max-width: 480px) {
        .field-row-2 { grid-template-columns: 1fr; }
    }
</style>

<div class="pos-wrap">

    {{-- ==================== LEFT PANEL ==================== --}}
    <div class="pos-left">

        {{-- Product Search --}}
        <div class="product-search-panel">
            <div class="field-label mb-2">Search Products</div>
            <div class="search-wrap" id="search-wrap">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="product-search" placeholder="Search by name or design code…" autocomplete="off">
                <div class="search-dropdown" id="search-dropdown">
                    @foreach ($products as $product)
                        <div class="search-option"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->product_name }}"
                             data-sku="{{ $product->sku }}"
                             data-price="{{ $product->selling_price ?? 0 }}">
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:600;color:#1e293b;word-break:break-word">{{ $product->product_name }}</div>
                                <div class="sku">{{ $product->sku }}</div>
                            </div>
                            <div style="font-size:12px;font-weight:600;color:#2563eb;flex-shrink:0">৳{{ number_format($product->selling_price ?? 0, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Cart --}}
        <div id="cart-container" style="flex:1; display:flex; flex-direction:column; gap:10px; min-width:0;">
            <div class="field-label">Cart Items</div>
            <div id="cart-empty" class="cart-empty">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                    <path d="M3 6h18"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
                <span>No items added yet.<br>Search and select a product above.</span>
            </div>
            <div id="cart-list" class="cart-list"></div>
        </div>
    </div>

    {{-- ==================== RIGHT SIDEBAR ==================== --}}
    <div class="pos-sidebar">
        {{-- Shop --}}
        <div class="field-group">
            <div class="field-label">Shop</div>
            @if(auth()->user()->canManageAllShops())
                <select name="shop_id" class="field-input" required>
                    <option value="">Select shop</option>
                    @foreach($shops ?? [] as $shop)
                        <option value="{{ $shop->id }}" @selected(old('shop_id', $sale?->shop_id ?? null) == $shop->id)>{{ $shop->name }}</option>
                    @endforeach
                </select>
            @else
                <input type="hidden" name="shop_id" value="{{ auth()->user()->shop_id }}">
                <input type="text" class="field-input" value="{{ auth()->user()->shop?->name ?? 'No shop assigned' }}" readonly>
            @endif
        </div>

        <hr class="divider">

        {{-- Customer --}}
        <div class="field-group">
            <div class="field-label">Customer</div>
            <div class="customer-select-row" style="display:flex;gap:8px">
                <select name="customer_id" id="customer_id" class="field-input" style="flex:1">
                    <option value="">Walk-in / No customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id', $sale?->customer_id) == $customer->id)>
                            {{ $customer->full_name }}{{ $customer->phone ? ' · ' . $customer->phone : '' }}
                        </option>
                    @endforeach
                </select>
                <button
                    type="button"
                    onclick="openCustomerModal()"
                    title="Add customer"
                    style="width:38px;height:38px;flex-shrink:0;border-radius:8px;border:1.5px solid #bfdbfe;background:#eff6ff;color:#2563eb;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:background .12s"
                >+</button>
            </div>
        </div>

        <hr class="divider">

        {{-- Totals --}}
        <div style="display:flex;flex-direction:column;gap:8px">
            <div class="totals-row">
                <span class="label">Subtotal</span>
                <span class="val" id="subtotal-display">৳0.00</span>
            </div>
            <div class="field-group">
                <div class="field-label">Discount (৳)</div>
                <input type="number" name="discount" id="discount"
                       value="{{ old('discount', $sale?->discount ?? 0) }}"
                       step="0.01" min="0" class="field-input">
            </div>
            <div class="totals-row grand" style="padding-top:4px;border-top:1px solid #e5e7eb;margin-top:4px">
                <span class="label">Grand Total</span>
                <span class="val" id="grand-total-display">৳0.00</span>
            </div>
        </div>

        <hr class="divider">

        {{-- Payment --}}
        <div class="field-group">
            <div class="field-label">Payment Status</div>
            <select name="payment_status" id="payment_status" class="field-input">
                <option value="due"     @selected(old('payment_status', $sale?->payment_status ?? 'due') === 'due')>Due</option>
                <option value="paid"    @selected(old('payment_status', $sale?->payment_status) === 'paid')>Paid</option>
                <option value="partial" @selected(old('payment_status', $sale?->payment_status) === 'partial')>Partial</option>
            </select>
        </div>

        <div id="paid-field" class="field-group" style="display:none">
            <div class="field-label">Paid Amount (৳)</div>
            <input type="number" name="paid" id="paid"
                   value="{{ old('paid', $sale?->paid ?? 0) }}"
                   step="0.01" min="0" class="field-input">
        </div>

        <div id="due-field" class="field-group" style="display:none">
            <div class="field-label">Due Amount (৳)</div>
            <input type="number" name="due" id="due"
                   value="{{ old('due', $sale?->due ?? 0) }}"
                   step="0.01" min="0" readonly class="field-input">
        </div>

        <div class="field-group">
            <div class="field-label">Payment Method</div>
            <select name="payment_method" class="field-input">
                <option value="">— Select —</option>
                @foreach (['Cash','Bank','Bkash','Nagad','Card'] as $method)
                    <option value="{{ $method }}" @selected(old('payment_method', $sale?->payment_method) == $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>

        <hr class="divider">

        {{-- Reference Numbers --}}
        <div class="field-group">
            <div class="field-label">Cash Memo #</div>
            <input type="text" name="cash_memo"
                   value="{{ old('cash_memo', $sale?->cash_memo) }}"
                   placeholder="e.g. CM-001"
                   class="field-input">
        </div>

        {{-- Bell No with helper hint --}}
        <div class="field-group">
            <div class="field-label" style="display:flex;align-items:center;gap:5px">
                Bell No
                <span
                    title="Bell No is the counter/token number called at the shop — like a queue token used in cash counters."
                    style="display:inline-flex;align-items:center;justify-content:center;width:14px;height:14px;border-radius:50%;background:#e2e8f0;color:#64748b;font-size:9px;font-weight:700;cursor:help;flex-shrink:0"
                >?</span>
            </div>
            <input type="text" name="bell_no"
                   value="{{ old('bell_no', $sale?->bell_no) }}"
                   placeholder="e.g. Token 42"
                   class="field-input">
            <p style="font-size:11px;color:#94a3b8;margin:0;line-height:1.4">
                Counter/token number — like a queue bell number used at cash counters.
            </p>
        </div>

        {{-- Note --}}
        <div class="field-group">
            <div class="field-label">Note</div>
            <textarea name="note" rows="2" class="field-input">{{ old('note', $sale?->note) }}</textarea>
        </div>

    </div>
</div>

{{-- Hidden reference --}}
<input type="hidden" name="reference" value="{{ $nextReference ?? $sale?->reference }}">

{{-- ============ Customer Modal ============ --}}
<div
    id="customerModal"
    class="hidden"
    style="position:fixed;inset:0;z-index:50;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.4);padding:16px"
    onclick="closeCustomerModal(event)"
>
    <div
        style="background:#fff;border-radius:14px;width:100%;max-width:440px;box-shadow:0 20px 50px rgba(0,0,0,.15)"
        onclick="event.stopPropagation()"
    >
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <span style="font-weight:700;font-size:14px">New Customer</span>
            <button type="button" onclick="closeCustomerModal()" style="border:none;background:none;font-size:18px;color:#94a3b8;cursor:pointer">✕</button>
        </div>

        <div style="padding:20px;display:flex;flex-direction:column;gap:12px">
            <input type="text" id="new_customer_name" placeholder="Full Name *" class="field-input">
            <input type="text" id="new_customer_phone" placeholder="Phone" class="field-input">
            <input type="text" id="new_customer_alternative_phone" placeholder="Alternative Phone" class="field-input">
            <div id="modal-error" class="hidden" style="font-size:12px;color:#ef4444"></div>
        </div>

        <div class="customer-modal-actions" style="padding:12px 20px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:8px">
            <button
                type="button"
                onclick="closeCustomerModal()"
                style="height:36px;padding:0 16px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;cursor:pointer;background:#fff"
            >Cancel</button>
            <button
                type="button"
                id="save-customer-btn"
                style="height:36px;padding:0 18px;border:none;border-radius:8px;font-size:13px;font-weight:600;background:#2563eb;color:#fff;cursor:pointer"
            >Save</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ============================================================
//  State
// ============================================================
let cartItems = @json($initialCart);

const ALL_PRODUCTS = @json($productPayload);

const shopInput = document.querySelector('[name="shop_id"]');
cartItems = cartItems.map(item => ({ ...item, stock_qty: getAvailableStock(item.product_id) }));

// ============================================================
//  DOM refs
// ============================================================
const searchInput   = document.getElementById('product-search');
const dropdown      = document.getElementById('search-dropdown');
const cartList      = document.getElementById('cart-list');
const cartEmpty     = document.getElementById('cart-empty');
const discountInput = document.getElementById('discount');
const subtotalSpan  = document.getElementById('subtotal-display');
const grandSpan     = document.getElementById('grand-total-display');
const payStatus     = document.getElementById('payment_status');
const paidField     = document.getElementById('paid-field');
const dueField      = document.getElementById('due-field');
const paidInput     = document.getElementById('paid');
const dueInput      = document.getElementById('due');

// ============================================================
//  Search
// ============================================================
searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    if (!q) { dropdown.classList.remove('open'); return; }

    const matches = ALL_PRODUCTS.filter(p =>
        p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
    );

    dropdown.innerHTML = matches.length
        ? matches.slice(0, 12).map(p => `
            <div class="search-option" data-id="${p.id}" data-name="${escHtml(p.name)}" data-sku="${escHtml(p.sku)}" data-price="${p.price}">
                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;color:#1e293b;word-break:break-word">${escHtml(p.name)}</div>
                    <div class="sku">${escHtml(p.sku)} · Stock: ${formatQty(getAvailableStock(p.id))}</div>
                </div>
                <div style="font-size:12px;font-weight:600;color:#2563eb;flex-shrink:0">৳${p.price.toFixed(2)}</div>
            </div>`).join('')
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
            addToCart(opt.dataset.id, opt.dataset.name, opt.dataset.sku, toNumber(opt.dataset.price), getAvailableStock(opt.dataset.id));
            searchInput.value = '';
            dropdown.classList.remove('open');
        });
    });
}

// ============================================================
//  Cart logic
// ============================================================
function addToCart(id, name, sku, price, stockQty = 0) {
    price = toNumber(price);
    const existing = cartItems.find(i => i.product_id == id);
    if (existing) {
        existing.qty += 1;
        existing.stock_qty = stockQty;
        existing.line_total = existing.qty * existing.price_on_sale;
    } else {
        const batch = getFirstBatch(id);
        cartItems.push({ product_id: id, product_name: name, sku: sku || '', stock_qty: stockQty, qty: 4, price_on_sale: price, line_total: price * 4, purchase_item_id: batch?.id || '', batch: batch?.batch || '' });
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
        <div class="prod-stock">Available: ${formatQty(item.stock_qty)}${item.batch ? ' · Batch: ' + escHtml(item.batch) : ''}</div>
    </div>
    <div class="qty-stepper">
        <button type="button" class="btn-minus" data-idx="${idx}">−</button>
        <input type="number" name="items[${idx}][qty]" class="item-qty" data-idx="${idx}" value="${item.qty}" step="0.01" min="0.01">
        <button type="button" class="btn-plus" data-idx="${idx}">+</button>
    </div>
    <input type="text"
           name="items[${idx}][price_on_sale]"
           class="price-edit item-price"
           data-idx="${idx}"
           value="${formatMoney(item.price_on_sale)}"
           inputmode="decimal" autocomplete="off" title="Unit price">
    <select name="items[${idx}][purchase_item_id]" class="price-edit item-batch" data-idx="${idx}" title="Batch">
        ${getBatchOptions(item.product_id, item.purchase_item_id)}
    </select>
    <div class="line-total item-total">৳${item.line_total.toFixed(2)}</div>
    <button type="button" class="btn-remove" data-idx="${idx}" title="Remove">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path d="M18 6 6 18M6 6l12 12"/>
        </svg>
    </button>`;
        cartList.appendChild(card);
    });

    attachCardEvents();
    recalc();
}

function attachCardEvents() {
    cartList.querySelectorAll('.btn-minus').forEach(b => b.addEventListener('click', e => {
        const i = +e.currentTarget.dataset.idx;
        if (cartItems[i].qty > 1) { cartItems[i].qty -= 1; updateItem(i); }
        else removeItem(i);
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
        cartItems[i].price_on_sale = toNumber(e.target.value);
        e.target.value = formatMoney(cartItems[i].price_on_sale);
        updateItem(i);
    }));

    cartList.querySelectorAll('.item-batch').forEach(sel => sel.addEventListener('change', e => {
        const i = +e.target.dataset.idx;
        const selected = getProductBatches(cartItems[i].product_id).find(batch => String(batch.id) === String(e.target.value));
        cartItems[i].purchase_item_id = e.target.value;
        cartItems[i].batch = selected?.batch || '';
        updateItem(i);
    }));

    cartList.querySelectorAll('.btn-remove').forEach(b => b.addEventListener('click', e => {
        removeItem(+e.currentTarget.dataset.idx);
    }));
}

function updateItem(idx) {
    cartItems[idx].line_total = toNumber(cartItems[idx].qty) * toNumber(cartItems[idx].price_on_sale);
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

// ============================================================
//  Totals
// ============================================================
function recalc() {
    const sub   = cartItems.reduce((s, i) => s + i.line_total, 0);
    const disc  = parseFloat(discountInput.value) || 0;
    const grand = Math.max(0, sub - disc);

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
        let paid = Math.min(parseFloat(paidInput.value) || 0, grand);
        paidInput.value = paid.toFixed(2);
        dueInput.value  = (grand - paid).toFixed(2);
    }
}

discountInput.addEventListener('input', recalc);
payStatus.addEventListener('change', recalc);
paidInput.addEventListener('input', () => {
    if (payStatus.value === 'partial') {
        const g = getGrand();
        let p   = Math.min(parseFloat(paidInput.value) || 0, g);
        dueInput.value = (g - p).toFixed(2);
    }
});

document.querySelector('form')?.addEventListener('submit', () => {
    cartList.querySelectorAll('.item-price').forEach(inp => {
        inp.value = formatMoney(toNumber(inp.value));
    });
});

shopInput?.addEventListener('change', () => {
    cartItems = cartItems.map(item => ({ ...item, stock_qty: getAvailableStock(item.product_id) }));
    renderCart();
});

function getGrand() {
    return Math.max(0, cartItems.reduce((s, i) => s + i.line_total, 0) - (parseFloat(discountInput.value) || 0));
}

// ============================================================
//  Customer modal
// ============================================================
function openCustomerModal() {
    const m = document.getElementById('customerModal');
    m.style.display = 'flex';
    m.classList.remove('hidden');
}

function closeCustomerModal(e) {
    if (!e || e.target === document.getElementById('customerModal')) {
        const m = document.getElementById('customerModal');
        m.style.display = 'none';
        m.classList.add('hidden');
    }
}

document.getElementById('save-customer-btn').addEventListener('click', async () => {
    const name  = document.getElementById('new_customer_name').value.trim();
    const phone = document.getElementById('new_customer_phone').value.trim();
    const alternativePhone = document.getElementById('new_customer_alternative_phone').value.trim();
    const err   = document.getElementById('modal-error');

    if (!name) { err.textContent = 'Name is required'; err.classList.remove('hidden'); return; }

    err.classList.add('hidden');
    const btn = document.getElementById('save-customer-btn');
    btn.disabled = true;

    try {
        const res = await fetch('{{ route('customers.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ full_name: name, phone, alternative_phone: alternativePhone })
        });

        const data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error saving customer');

        const sel = document.getElementById('customer_id');
        sel.add(new Option(data.full_name + (data.phone ? ' · ' + data.phone : ''), data.id, true, true));
        sel.value = data.id;

        closeCustomerModal();
        document.getElementById('new_customer_name').value  = '';
        document.getElementById('new_customer_phone').value = '';
        document.getElementById('new_customer_alternative_phone').value = '';
    } catch (ex) {
        err.textContent = ex.message;
        err.classList.remove('hidden');
    } finally {
        btn.disabled = false;
    }
});

// ============================================================
//  Helpers
// ============================================================
function escHtml(s) {
    return String(s).replace(/[&<>"']/g, c =>
        ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])
    );
}

function toNumber(value) {
    const normalized = String(value ?? '')
        .replace(/[^\d.,-]/g, '')
        .replace(',', '.');

    const number = Number.parseFloat(normalized);
    return Number.isFinite(number) ? number : 0;
}

function formatMoney(value) {
    return toNumber(value).toFixed(2);
}

function getAvailableStock(productId) {
    const product = ALL_PRODUCTS.find(p => String(p.id) === String(productId));
    const shopId = shopInput?.value || '';

    return Number(product?.stocks?.[shopId] ?? 0);
}

function getProductBatches(productId) {
    const product = ALL_PRODUCTS.find(p => String(p.id) === String(productId));
    return product?.batches || [];
}

function getFirstBatch(productId) {
    return getProductBatches(productId).find(batch => Number(batch.available_qty) > 0) || getProductBatches(productId)[0] || null;
}

function getBatchOptions(productId, selectedId) {
    const batches = getProductBatches(productId);
    if (!batches.length) {
        return '<option value="">No batch</option>';
    }

    return batches.map(batch => {
        const selected = String(batch.id) === String(selectedId) ? ' selected' : '';
        const label = `${escHtml(batch.batch)} · Cost: ৳${Number(batch.price).toFixed(2)} · Qty: ${formatQty(batch.available_qty)}`;
        return `<option value="${batch.id}"${selected}>${label}</option>`;
    }).join('');
}

function formatQty(qty) {
    const value = Number(qty) || 0;
    return Number.isInteger(value) ? String(value) : value.toFixed(2);
}

// ============================================================
//  Init
// ============================================================
renderCart();
recalc();
setTimeout(recalc, 100);
</script>
@endpush
