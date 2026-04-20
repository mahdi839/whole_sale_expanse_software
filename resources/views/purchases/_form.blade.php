@php
    $purchase = $purchase ?? null;

    $initialCart = [];
    if ($purchase && $purchase->relationLoaded('items') && $purchase->items->count()) {
        $initialCart = $purchase->items
            ->map(fn($item) => [
                'product_id' => $item->product_id,
                'product_name' => $item->product->product_name ?? 'Unknown',
                'sku' => $item->product->sku ?? '',
                'qty' => (float) $item->qty,
                'price' => (float) $item->price,
                'line_total' => (float) $item->line_total,
            ])
            ->values()
            ->toArray();
    }
@endphp

<style>
    .pos-wrap {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 0;
        min-height: 600px;
    }
    @media (max-width: 900px) {
        .pos-wrap { grid-template-columns: 1fr; }
        .pos-sidebar { border-left: none !important; border-top: 1px solid #e5e7eb; }
    }

    .pos-left  { padding: 24px; display: flex; flex-direction: column; gap: 20px; }
    .pos-sidebar { padding: 24px; background: #f8fafc; border-left: 1px solid #e5e7eb; display: flex; flex-direction: column; gap: 18px; }

    /* ── Product search ── */
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
    .search-wrap input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
    .search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
    .search-dropdown {
        position: absolute; top: calc(100% + 6px); left: 0; right: 0;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.09);
        max-height: 240px; overflow-y: auto; z-index: 50;
        display: none;
    }
    .search-dropdown.open { display: block; }
    .search-option {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 14px; cursor: pointer; font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .1s;
    }
    .search-option:last-child { border-bottom: none; }
    .search-option:hover { background: #f0f9ff; }
    .search-option .sku { font-size: 11px; color: #94a3b8; font-family: monospace; }

    /* ── Cart ── */
    .cart-empty {
        flex: 1; display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        color: #cbd5e1; gap: 10px; padding: 40px;
        border: 2px dashed #e2e8f0; border-radius: 12px;
        font-size: 13px; text-align: center;
    }
    .cart-list { display: flex; flex-direction: column; gap: 10px; }
    .cart-card {
        display: flex; align-items: center; gap: 12px;
        background: #fff; border: 1px solid #e5e7eb;
        border-radius: 10px; padding: 12px 14px;
        animation: slideIn .18s ease;
    }
    @keyframes slideIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }
    .cart-card .prod-info { flex: 1; min-width: 0; }
    .cart-card .prod-name { font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cart-card .prod-sku  { font-size: 11px; color: #94a3b8; font-family: monospace; }
    .cart-card .price-col { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
    .cart-card .line-total { font-size: 13px; font-weight: 700; color: #16a34a; }
    .price-edit { width: 90px; height: 32px; padding: 0 8px; font-size: 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: right; }
    .price-edit:focus { outline: none; border-color: #3b82f6; }

    .qty-stepper { display: flex; align-items: center; gap: 0; border: 1px solid #e2e8f0; border-radius: 7px; overflow: hidden; }
    .qty-stepper button {
        width: 28px; height: 32px; font-size: 16px; font-weight: 600;
        background: #f8fafc; border: none; cursor: pointer; color: #475569;
        transition: background .12s;
    }
    .qty-stepper button:hover { background: #e2e8f0; }
    .qty-stepper input {
        width: 40px; height: 32px; border: none; border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0; text-align: center; font-size: 13px;
        font-weight: 600; outline: none; background: #fff; color: #0f172a;
    }
    .qty-stepper input::-webkit-inner-spin-button,
    .qty-stepper input::-webkit-outer-spin-button { -webkit-appearance: none; }

    .btn-remove {
        width: 28px; height: 28px; border-radius: 6px;
        border: none; background: #fef2f2; color: #ef4444;
        cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center;
        transition: background .12s;
    }
    .btn-remove:hover { background: #fee2e2; }

    /* ── Form fields ── */
    .field-group { display: flex; flex-direction: column; gap: 6px; }
    .field-label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .field-input {
        width: 100%; height: 38px; padding: 0 12px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; background: #fff; outline: none;
        transition: border .15s, box-shadow .15s;
    }
    .field-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .field-input[readonly] { background: #f1f5f9; color: #64748b; cursor: default; }
    select.field-input { cursor: pointer; }
    textarea.field-input { height: auto; padding: 8px 12px; resize: vertical; }

    /* ── Supplier searchable dropdown ── */
    .sup-wrap { position: relative; }
    .sup-display {
        width: 100%; height: 38px; padding: 0 34px 0 12px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; background: #fff; outline: none;
        transition: border .15s, box-shadow .15s;
        cursor: pointer; color: #1e293b;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        text-align: left; appearance: none;
    }
    .sup-display:focus, .sup-display.open { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .sup-display::placeholder { color: #94a3b8; }
    .sup-chevron {
        position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
        color: #9ca3af; pointer-events: none;
        transition: transform .2s;
    }
    .sup-chevron.open { transform: translateY(-50%) rotate(180deg); }
    .sup-dropdown {
        position: absolute; top: calc(100% + 5px); left: 0; right: 0;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.09);
        z-index: 100; display: none; flex-direction: column;
        overflow: hidden;
    }
    .sup-dropdown.open { display: flex; }
    .sup-search-box { padding: 8px; border-bottom: 1px solid #f1f5f9; }
    .sup-search-box input {
        width: 100%; height: 32px; padding: 0 10px;
        font-size: 13px; border: 1px solid #e2e8f0;
        border-radius: 6px; background: #f8fafc;
        outline: none; color: #1e293b;
        transition: border .15s;
    }
    .sup-search-box input:focus { border-color: #3b82f6; }
    .sup-list { max-height: 200px; overflow-y: auto; }
    .sup-option {
        display: flex; flex-direction: column;
        padding: 9px 12px; cursor: pointer; font-size: 13px;
        border-bottom: 1px solid #f1f5f9; transition: background .1s;
    }
    .sup-option:last-child { border-bottom: none; }
    .sup-option:hover { background: #f0f9ff; }
    .sup-option.sup-selected { background: #eff6ff; }
    .sup-option .sup-name { font-weight: 600; color: #1e293b; }
    .sup-option .sup-phone { font-size: 11px; color: #94a3b8; margin-top: 1px; }
    .sup-option.sup-clear .sup-name { color: #94a3b8; font-style: italic; font-weight: 400; }
    .sup-no-results { padding: 12px; font-size: 13px; color: #94a3b8; text-align: center; }

    /* ── Misc ── */
    .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0; }
    .totals-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .totals-row.grand { font-size: 16px; font-weight: 700; margin-top: 4px; }
    .totals-row .label { color: #64748b; }
    .totals-row .val   { color: #1e293b; }
    .totals-row.grand .val { color: #16a34a; font-size: 20px; }
    .text-error { font-size: 12px; color: #dc2626; }
</style>

<div class="pos-wrap">

    {{-- ════════════════════════════════════════
         LEFT — Product search + cart
    ════════════════════════════════════════ --}}
    <div class="pos-left">
        <div>
            <div class="field-label mb-2">Search Products</div>
            <div class="search-wrap" id="search-wrap">
                <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" id="product-search" placeholder="Search by name or SKU…" autocomplete="off">
                <div class="search-dropdown" id="search-dropdown">
                    @foreach ($products as $product)
                        <div class="search-option"
                             data-id="{{ $product->id }}"
                             data-name="{{ $product->product_name }}"
                             data-sku="{{ $product->sku }}"
                             data-price="{{ $product->buying_price ?? 0 }}">
                            <div style="flex:1">
                                <div style="font-weight:600;color:#1e293b">{{ $product->product_name }}</div>
                                <div class="sku">{{ $product->sku }}</div>
                            </div>
                            <div style="font-size:12px;font-weight:600;color:#16a34a">৳{{ number_format($product->buying_price ?? 0, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @error('items')
                <p class="text-error mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div id="cart-container" style="flex:1; display:flex; flex-direction:column; gap:10px;">
            <div class="field-label">Purchase Items</div>
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

    {{-- ════════════════════════════════════════
         RIGHT — Sidebar / form fields
    ════════════════════════════════════════ --}}
    <div class="pos-sidebar">

        {{-- Supplier (searchable) --}}
        <div class="field-group">
            <div class="field-label">Supplier</div>
            <div class="sup-wrap" id="sup-wrap">
                <input type="text"
                       id="sup-display"
                       class="sup-display"
                       placeholder="— Select supplier —"
                       readonly>
                <input type="hidden"
                       name="supplier_id"
                       id="supplier_id"
                       value="{{ old('supplier_id', $purchase?->supplier_id) }}">
                <svg class="sup-chevron" id="sup-chevron"
                     width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
                <div class="sup-dropdown" id="sup-dropdown">
                    <div class="sup-search-box">
                        <input type="text" id="sup-search" placeholder="Search by name or phone…" autocomplete="off">
                    </div>
                    <div class="sup-list" id="sup-list"></div>
                </div>
            </div>
        </div>

        <div class="field-group">
            <div class="field-label">Seller / Store Name</div>
            <input type="text" name="seller_store_name"
                   value="{{ old('seller_store_name', $purchase?->seller_store_name) }}"
                   class="field-input"
                   placeholder="Optional store name">
        </div>

        <div class="field-group">
            <div class="field-label">Purchased By</div>
            <input type="text" name="purchased_by"
                   value="{{ old('purchased_by', $purchase?->purchased_by) }}"
                   class="field-input"
                   placeholder="Enter purchaser name">
            @error('purchased_by')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <hr class="divider">

        <div style="display:flex;flex-direction:column;gap:8px">
            <div class="totals-row">
                <span class="label">Items Total</span>
                <span class="val" id="subtotal-display">৳0.00</span>
            </div>

            <div class="field-group">
                <div class="field-label">Discount (৳)</div>
                <input type="number" name="discount" id="discount"
                       value="{{ old('discount', $purchase?->discount ?? 0) }}"
                       step="0.01" min="0" class="field-input">
                @error('discount')
                    <p class="text-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="field-group">
                <div class="field-label">Other Cost (৳)</div>
                <input type="number" name="other_cost" id="other_cost"
                       value="{{ old('other_cost', $purchase?->other_cost ?? 0) }}"
                       step="0.01" min="0" class="field-input">
                @error('other_cost')
                    <p class="text-error">{{ $message }}</p>
                @enderror
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
                <option value="partial"  @selected(old('purchase_status', $purchase?->purchase_status) === 'partial')>Partial</option>
                <option value="pending"  @selected(old('purchase_status', $purchase?->purchase_status ?? 'pending') === 'pending')>Pending</option>
                <option value="ordered"  @selected(old('purchase_status', $purchase?->purchase_status) === 'ordered')>Ordered</option>
            </select>
            @error('purchase_status')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Payment Status</div>
            <select name="payment_status" id="payment_status" class="field-input">
                <option value="due"     @selected(old('payment_status', $purchase?->payment_status ?? 'due') === 'due')>Due</option>
                <option value="paid"    @selected(old('payment_status', $purchase?->payment_status) === 'paid')>Paid</option>
                <option value="partial" @selected(old('payment_status', $purchase?->payment_status) === 'partial')>Partial</option>
            </select>
            @error('payment_status')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div id="paid-field" class="field-group" style="display:none">
            <div class="field-label">Paid Amount (৳)</div>
            <input type="number" name="paid_amount" id="paid_amount"
                   value="{{ old('paid_amount', $purchase?->paid_amount ?? 0) }}"
                   step="0.01" min="0" class="field-input">
            @error('paid_amount')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div id="due-field" class="field-group" style="display:none">
            <div class="field-label">Due Amount (৳)</div>
            <input type="number" name="due_amount" id="due_amount"
                   value="{{ old('due_amount', $purchase?->due_amount ?? 0) }}"
                   step="0.01" min="0" readonly class="field-input">
        </div>

        <div class="field-group">
            <div class="field-label">Payment Method</div>
            <select name="payment_method" class="field-input">
                <option value="">— Select —</option>
                @foreach (['Cash','Bank','Bkash','Nagad','Card'] as $method)
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
            <input type="text" name="cash_memo"
                   value="{{ old('cash_memo', $purchase?->cash_memo) }}"
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
            @if($purchase?->document)
                <a href="{{ asset('storage/'.$purchase->document) }}" target="_blank" class="text-xs text-blue-600 hover:underline">
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

@push('scripts')
<script>
// ─────────────────────────────────────────────────────────────
// Cart state
// ─────────────────────────────────────────────────────────────
let cartItems = @json($initialCart);

const ALL_PRODUCTS = [
    @foreach($products as $p)
    {
        id: "{{ $p->id }}",
        name: @json($p->product_name),
        sku: "{{ $p->sku }}",
        price: {{ $p->buying_price ?? 0 }}
    },
    @endforeach
];

// ─────────────────────────────────────────────────────────────
// Supplier data
// ─────────────────────────────────────────────────────────────
const ALL_SUPPLIERS = [
    @foreach($suppliers as $s)
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
const searchInput    = document.getElementById('product-search');
const dropdown       = document.getElementById('search-dropdown');
const cartList       = document.getElementById('cart-list');
const cartEmpty      = document.getElementById('cart-empty');
const discountInput  = document.getElementById('discount');
const otherCostInput = document.getElementById('other_cost');
const subtotalSpan   = document.getElementById('subtotal-display');
const grandSpan      = document.getElementById('grand-total-display');
const payStatus      = document.getElementById('payment_status');
const paidField      = document.getElementById('paid-field');
const dueField       = document.getElementById('due-field');
const paidInput      = document.getElementById('paid_amount');
const dueInput       = document.getElementById('due_amount');

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
                <div style="flex:1">
                    <div style="font-weight:600;color:#1e293b">${escHtml(p.name)}</div>
                    <div class="sku">${escHtml(p.sku)}</div>
                </div>
                <div style="font-size:12px;font-weight:600;color:#16a34a">৳${Number(p.price).toFixed(2)}</div>
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
            addToCart(opt.dataset.id, opt.dataset.name, opt.dataset.sku, parseFloat(opt.dataset.price));
            searchInput.value = '';
            dropdown.classList.remove('open');
        });
    });
}

// ─────────────────────────────────────────────────────────────
// Cart logic
// ─────────────────────────────────────────────────────────────
function addToCart(id, name, sku, price) {
    const existing = cartItems.find(i => String(i.product_id) === String(id));
    if (existing) {
        existing.qty += 1;
        existing.line_total = existing.qty * existing.price;
    } else {
        cartItems.push({ product_id: id, product_name: name, sku: sku || '', qty: 1, price, line_total: price });
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
            </div>
            <div class="qty-stepper">
                <button type="button" class="btn-minus" data-idx="${idx}">−</button>
                <input type="number" name="items[${idx}][qty]" class="item-qty" data-idx="${idx}" value="${item.qty}" step="0.01" min="0.01">
                <button type="button" class="btn-plus" data-idx="${idx}">+</button>
            </div>
            <div class="price-col">
                <input type="number" name="items[${idx}][price]" class="price-edit item-price" data-idx="${idx}" value="${Number(item.price).toFixed(2)}" step="0.01" min="0" title="Unit price">
                <div class="line-total item-total">৳${Number(item.line_total).toFixed(2)}</div>
            </div>
            <button type="button" class="btn-remove" data-idx="${idx}" title="Remove">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </button>
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
        cartItems[i].qty += 1; updateItem(i);
    }));
    cartList.querySelectorAll('.item-qty').forEach(inp => inp.addEventListener('change', e => {
        const i = +e.target.dataset.idx;
        const v = parseFloat(e.target.value) || 1;
        cartItems[i].qty = v < 0.01 ? 0.01 : v; updateItem(i);
    }));
    cartList.querySelectorAll('.item-price').forEach(inp => inp.addEventListener('change', e => {
        const i = +e.target.dataset.idx;
        cartItems[i].price = parseFloat(e.target.value) || 0; updateItem(i);
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
    const disc  = parseFloat(discountInput?.value) || 0;
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
        cartItems.reduce((s, i) => s + i.line_total, 0)
        + (parseFloat(otherCostInput?.value) || 0)
        - (parseFloat(discountInput?.value)  || 0)
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

    // Pre-fill label on edit page
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

        // "Clear" option
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
            d.innerHTML = `<span class="sup-name">${escHtml(s.name)}</span>`
                        + (s.phone ? `<span class="sup-phone">${escHtml(s.phone)}</span>` : '');
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
function escHtml(s) {
    return String(s).replace(/[&<>"']/g, c =>
        ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c])
    );
}

// ─────────────────────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────────────────────
renderCart();
recalc();
setTimeout(recalc, 100);
</script>
@endpush