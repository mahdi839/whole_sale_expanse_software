@php
    $purchaseReturn = $purchaseReturn ?? null;
    $prefillPurchase = $purchase ?? null;

    $initialCart = [];
    if ($purchaseReturn && $purchaseReturn->relationLoaded('items') && $purchaseReturn->items->count()) {
        $initialCart = $purchaseReturn->items
            ->map(fn($item) => [
                'product_id'       => $item->product_id,
                'purchase_item_id' => $item->purchase_item_id,
                'product_name'     => $item->product->product_name ?? 'Unknown',
                'sku'              => $item->product->sku ?? '',
                'qty'              => (float) $item->qty,
                'price'            => (float) $item->price,
                'line_total'       => (float) $item->line_total,
            ])
            ->values()
            ->toArray();
    } elseif ($prefillPurchase && $prefillPurchase->relationLoaded('items') && $prefillPurchase->items->count()) {
        $initialCart = $prefillPurchase->items
            ->map(fn($item) => [
                'product_id'       => $item->product_id,
                'purchase_item_id' => $item->id,
                'product_name'     => $item->product->product_name ?? 'Unknown',
                'sku'              => $item->product->sku ?? '',
                'qty'              => (float) $item->qty,
                'price'            => (float) $item->price,
                'line_total'       => (float) $item->line_total,
            ])
            ->values()
            ->toArray();
    }

    $selectedPurchaseId = old('purchase_id', $purchaseReturn?->purchase_id ?? $prefillPurchase?->id);
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
    }
    .cart-card .prod-info { flex: 1; min-width: 0; }
    .cart-card .prod-name { font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cart-card .prod-sku  { font-size: 11px; color: #94a3b8; font-family: monospace; }
    .cart-card .price-col { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; }
    .cart-card .line-total { font-size: 13px; font-weight: 700; color: #dc2626; }
    .price-edit { width: 90px; height: 32px; padding: 0 8px; font-size: 12px; border: 1px solid #e2e8f0; border-radius: 6px; text-align: right; }
    .price-edit:focus { outline: none; border-color: #3b82f6; }

    .qty-stepper { display: flex; align-items: center; gap: 0; border: 1px solid #e2e8f0; border-radius: 7px; overflow: hidden; }
    .qty-stepper button {
        width: 28px; height: 32px; font-size: 16px; font-weight: 600;
        background: #f8fafc; border: none; cursor: pointer; color: #475569;
    }
    .qty-stepper input {
        width: 40px; height: 32px; border: none; border-left: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0; text-align: center; font-size: 13px;
        font-weight: 600; outline: none; background: #fff; color: #0f172a;
    }

    .btn-remove {
        width: 28px; height: 28px; border-radius: 6px;
        border: none; background: #fef2f2; color: #ef4444;
        cursor: pointer; font-size: 14px; display: flex; align-items: center; justify-content: center;
    }

    .field-group { display: flex; flex-direction: column; gap: 6px; }
    .field-label { font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
    .field-input {
        width: 100%; height: 38px; padding: 0 12px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 13px; background: #fff; outline: none;
    }
    .field-input[readonly] { background: #f1f5f9; color: #64748b; }
    select.field-input { cursor: pointer; }
    textarea.field-input { height: auto; padding: 8px 12px; resize: vertical; }

    .divider { border: none; border-top: 1px solid #e5e7eb; margin: 0; }

    .totals-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .totals-row.grand { font-size: 16px; font-weight: 700; margin-top: 4px; }
    .totals-row .label { color: #64748b; }
    .totals-row .val   { color: #1e293b; }
    .totals-row.grand .val { color: #dc2626; font-size: 20px; }

    .text-error { font-size: 12px; color: #dc2626; }
</style>

<div class="pos-wrap">
    <div class="pos-left">
        <div class="field-group">
            <div class="field-label">Original Purchase</div>
            <select name="purchase_id" id="purchase_id" class="field-input">
                <option value="">— Select purchase —</option>
                @foreach(\App\Models\Purchase::with(['supplier', 'items.product'])->latest()->get() as $p)
                    <option value="{{ $p->id }}" @selected((string)$selectedPurchaseId === (string)$p->id)>
                        {{ $p->reference }}{{ $p->supplier ? ' — '.$p->supplier->name : '' }}
                    </option>
                @endforeach
            </select>
            @error('purchase_id')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

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
                            <div style="font-size:12px;font-weight:600;color:#dc2626">৳{{ number_format($product->buying_price ?? 0, 2) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @error('items')
                <p class="text-error mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div id="cart-container" style="flex:1; display:flex; flex-direction:column; gap:10px;">
            <div class="field-label">Return Items</div>
            <div id="cart-empty" class="cart-empty">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                    <path d="M16 3v4M8 3v4M2 11h20"/>
                </svg>
                <span>No return items added yet.<br>Search and select a product above.</span>
            </div>
            <div id="cart-list" class="cart-list"></div>
        </div>
    </div>

    <div class="pos-sidebar">
        <div class="field-group">
            <div class="field-label">Supplier</div>
            <select name="supplier_id" id="supplier_id" class="field-input">
                <option value="">— Select supplier —</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id', $purchaseReturn?->supplier_id ?? $prefillPurchase?->supplier_id) == $supplier->id)>
                        {{ $supplier->name }}{{ $supplier->phone ? ' · '.$supplier->phone : '' }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <hr class="divider">

        <div style="display:flex;flex-direction:column;gap:8px">
            <div class="totals-row">
                <span class="label">Subtotal</span>
                <span class="val" id="subtotal-display">৳0.00</span>
            </div>

            <div class="field-group">
                <div class="field-label">Discount (৳)</div>
                <input type="number" name="discount" id="discount"
                       value="{{ old('discount', $purchaseReturn?->discount ?? 0) }}"
                       step="0.01" min="0" class="field-input">
            </div>

            <div class="totals-row grand" style="padding-top:4px;border-top:1px solid #e5e7eb;margin-top:4px">
                <span class="label">Return Amount</span>
                <span class="val" id="grand-total-display">৳0.00</span>
            </div>
        </div>

        <hr class="divider">

        <div class="field-group">
            <div class="field-label">Return Type</div>
            <select name="return_type" class="field-input">
                <option value="refund" @selected(old('return_type', $purchaseReturn?->return_type ?? 'refund') === 'refund')>Refund</option>
                <option value="exchange" @selected(old('return_type', $purchaseReturn?->return_type) === 'exchange')>Exchange</option>
                <option value="credit" @selected(old('return_type', $purchaseReturn?->return_type) === 'credit')>Credit</option>
            </select>
            @error('return_type')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Return Status</div>
            <select name="return_status" class="field-input">
                <option value="pending" @selected(old('return_status', $purchaseReturn?->return_status ?? 'pending') === 'pending')>Pending</option>
                <option value="approved" @selected(old('return_status', $purchaseReturn?->return_status) === 'approved')>Approved</option>
                <option value="rejected" @selected(old('return_status', $purchaseReturn?->return_status) === 'rejected')>Rejected</option>
            </select>
            @error('return_status')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Payment Method</div>
            <select name="payment_method" class="field-input">
                <option value="">— Select —</option>
                @foreach (['Cash','Bank','Bkash','Nagad','Card'] as $method)
                    <option value="{{ $method }}" @selected(old('payment_method', $purchaseReturn?->payment_method) == $method)>{{ $method }}</option>
                @endforeach
            </select>
        </div>

        <hr class="divider">

        <div class="field-group">
            <div class="field-label">Cash Memo #</div>
            <input type="text" name="cash_memo" value="{{ old('cash_memo', $purchaseReturn?->cash_memo) }}" class="field-input">
        </div>

        <div class="field-group">
            <div class="field-label">Date</div>
            <input type="date" name="date"
                   value="{{ old('date', optional($purchaseReturn?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   class="field-input">
            @error('date')
                <p class="text-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field-group">
            <div class="field-label">Document</div>
            <input type="file" name="document" class="field-input" style="padding:6px 10px;height:auto">
            @if($purchaseReturn?->document)
                <a href="{{ asset('storage/'.$purchaseReturn->document) }}" target="_blank" class="text-xs text-blue-600 hover:underline">
                    View current file
                </a>
            @endif
        </div>

        <div class="field-group">
            <div class="field-label">Note</div>
            <textarea name="note" rows="2" class="field-input">{{ old('note', $purchaseReturn?->note) }}</textarea>
        </div>
    </div>
</div>

<input type="hidden" name="reference" value="{{ $nextReference ?? $purchaseReturn?->reference }}">

@push('scripts')
<script>
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

const searchInput   = document.getElementById('product-search');
const dropdown      = document.getElementById('search-dropdown');
const cartList      = document.getElementById('cart-list');
const cartEmpty     = document.getElementById('cart-empty');
const discountInput = document.getElementById('discount');
const subtotalSpan  = document.getElementById('subtotal-display');
const grandSpan     = document.getElementById('grand-total-display');

searchInput?.addEventListener('input', () => {
    const q = searchInput.value.trim().toLowerCase();
    if (!q) {
        dropdown.classList.remove('open');
        return;
    }

    const matches = ALL_PRODUCTS.filter(p =>
        p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
    );

    dropdown.innerHTML = matches.length
        ? matches.slice(0, 12).map(p => `
            <div class="search-option" data-id="${p.id}" data-name="${escHtml(p.name)}" data-sku="${escHtml(p.sku)}" data-price="${p.price}">
                <div style="flex:1">
                    <div style="font-weight:600;color:#1e293b">${escHtml(p.name)}</div>
                    <div class="sku">${escHtml(p.sku)}</div>
                </div>
                <div style="font-size:12px;font-weight:600;color:#dc2626">৳${Number(p.price).toFixed(2)}</div>
            </div>
        `).join('')
        : '<div style="padding:14px 16px;font-size:13px;color:#94a3b8">No products found</div>';

    dropdown.classList.add('open');
    attachDropdownEvents();
});

document.addEventListener('click', e => {
    const wrap = document.getElementById('search-wrap');
    if (wrap && !wrap.contains(e.target)) {
        dropdown.classList.remove('open');
    }
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

function addToCart(id, name, sku, price) {
    const existing = cartItems.find(i => String(i.product_id) === String(id));
    if (existing) {
        existing.qty += 1;
        existing.line_total = existing.qty * existing.price;
    } else {
        cartItems.push({
            product_id: id,
            purchase_item_id: '',
            product_name: name,
            sku: sku || '',
            qty: 1,
            price: price,
            line_total: price
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
            <input type="hidden" name="items[${idx}][purchase_item_id]" value="${item.purchase_item_id || ''}">
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
        if (cartItems[i].qty > 1) {
            cartItems[i].qty -= 1;
            updateItem(i);
        } else {
            removeItem(i);
        }
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

function recalc() {
    const sub = cartItems.reduce((s, i) => s + i.line_total, 0);
    const disc = parseFloat(discountInput?.value) || 0;
    const grand = Math.max(0, sub - disc);

    subtotalSpan.textContent = '৳' + sub.toFixed(2);
    grandSpan.textContent = '৳' + grand.toFixed(2);
}

discountInput?.addEventListener('input', recalc);

function escHtml(s) {
    return String(s).replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])
    );
}

renderCart();
recalc();
</script>
@endpush