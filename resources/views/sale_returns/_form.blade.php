@php
    $saleReturn = $saleReturn ?? null;
    $prefillSale = $sale ?? null;   // passed from create when coming from a sale page

    $sectionClass  = 'bg-white border border-gray-200 rounded-xl overflow-hidden';
    $headerClass   = 'flex items-center gap-2.5 px-5 py-3 border-b border-gray-100 bg-gray-50/60';
    $bodyClass     = 'p-5';
    $labelClass    = 'block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5';
    $inputClass    = 'w-full h-9 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg
                      text-gray-800 placeholder-gray-400
                      focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition';
    $inputErrClass = 'w-full h-9 px-3 text-sm bg-red-50 border border-red-300 rounded-lg
                      text-gray-800 placeholder-gray-400
                      focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 transition';
@endphp

{{-- ══════════════════════
     1 — Original Sale
══════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-violet-50 text-violet-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Original Sale (optional)</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div>
            <label for="sale_id" class="{{ $labelClass }}">Link to Sale</label>
            <select id="sale_id" name="sale_id"
                    onchange="prefillFromSale(this)"
                    class="{{ $inputClass }}">
                <option value="">— No linked sale —</option>
                @foreach(\App\Models\Sale::orderByDesc('id')->get(['id','reference','customer_id','product_id','product_name','product_code','price_on_sale']) as $s)
                    <option value="{{ $s->id }}"
                            data-customer="{{ $s->customer_id }}"
                            data-product="{{ $s->product_id }}"
                            data-product-name="{{ $s->product_name }}"
                            data-product-code="{{ $s->product_code }}"
                            data-price="{{ $s->price_on_sale }}"
                        {{ old('sale_id', $saleReturn?->sale_id ?? $prefillSale?->id) == $s->id ? 'selected' : '' }}>
                        {{ $s->reference }}
                    </option>
                @endforeach
            </select>
            @error('sale_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            <p class="mt-1.5 text-xs text-gray-400">Selecting a sale auto-fills customer, product, and price.</p>
        </div>
    </div>
</div>

{{-- ══════════════════════
     2 — Customer
══════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Customer</span>
    </div>
    <div class="{{ $bodyClass }}">
        <label for="customer_id" class="{{ $labelClass }}">Customer</label>
        <select id="customer_id" name="customer_id"
                class="{{ $errors->has('customer_id') ? $inputErrClass : $inputClass }}">
            <option value="">— Walk-in / No customer —</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}"
                    {{ old('customer_id', $saleReturn?->customer_id ?? $prefillSale?->customer_id) == $c->id ? 'selected' : '' }}>
                    {{ $c->full_name }}{{ $c->phone ? ' · '.$c->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('customer_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>

{{-- ══════════════════════
     3 — Product
══════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-violet-50 text-violet-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Product</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="product_id" class="{{ $labelClass }}">Product</label>
                <select id="product_id" name="product_id"
                        onchange="fillReturnProductFields(this)"
                        class="{{ $errors->has('product_id') ? $inputErrClass : $inputClass }}">
                    <option value="">— Select product —</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}"
                                data-name="{{ $p->product_name }}"
                                data-sku="{{ $p->sku }}"
                            {{ old('product_id', $saleReturn?->product_id ?? $prefillSale?->product_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->product_name }}{{ $p->sku ? ' ['.$p->sku.']' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="product_code" class="{{ $labelClass }}">Product Code / SKU</label>
                <input type="text" id="product_code" name="product_code"
                       value="{{ old('product_code', $saleReturn?->product_code ?? $prefillSale?->product_code) }}"
                       placeholder="Auto-filled or enter manually"
                       class="{{ $inputClass }}"/>
            </div>
        </div>
        <input type="hidden" id="product_name_hidden" name="product_name"
               value="{{ old('product_name', $saleReturn?->product_name ?? $prefillSale?->product_name) }}">
    </div>
</div>

{{-- ══════════════════════
     4 — Return Quantities & Pricing
══════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-green-50 text-green-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Qty &amp; Pricing</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="qty" class="{{ $labelClass }}">Return Qty <span class="text-red-400 normal-case">*</span></label>
                <input type="number" id="qty" name="qty"
                       value="{{ old('qty', $saleReturn?->qty ?? '1') }}"
                       min="0.01" step="0.01"
                       oninput="calcReturnTotal()"
                       class="{{ $errors->has('qty') ? $inputErrClass : $inputClass }}"/>
                @error('qty') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="price_on_sale" class="{{ $labelClass }}">
                    Original Price (৳) <span class="text-red-400 normal-case">*</span>
                </label>
                <input type="number" id="price_on_sale" name="price_on_sale"
                       value="{{ old('price_on_sale', $saleReturn?->price_on_sale ?? $prefillSale?->price_on_sale ?? '0') }}"
                       min="0" step="0.01"
                       oninput="calcReturnTotal()"
                       class="{{ $errors->has('price_on_sale') ? $inputErrClass : $inputClass }}"/>
                @error('price_on_sale') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="discount" class="{{ $labelClass }}">Discount (৳)</label>
                <input type="number" id="discount" name="discount"
                       value="{{ old('discount', $saleReturn?->discount ?? '0') }}"
                       min="0" step="0.01"
                       oninput="calcReturnTotal()"
                       class="{{ $inputClass }}"/>
            </div>
        </div>

        {{-- Live summary --}}
        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Subtotal</span>
                <span id="subtotal-display" class="text-base font-semibold text-gray-800">৳0.00</span>
            </div>
            <div class="flex items-center justify-between bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Return Amount</span>
                <span id="return-amount-display" class="text-base font-semibold text-red-600">৳0.00</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════
     5 — Return Type & Status
══════════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-amber-50 text-amber-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Return Type &amp; Status</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="return_type" class="{{ $labelClass }}">
                    Return type <span class="text-red-400 normal-case">*</span>
                </label>
                <select id="return_type" name="return_type"
                        class="{{ $errors->has('return_type') ? $inputErrClass : $inputClass }}">
                    @foreach(['refund'=>'Refund','exchange'=>'Exchange','credit'=>'Credit Note'] as $val=>$label)
                        <option value="{{ $val }}"
                            {{ old('return_type', $saleReturn?->return_type ?? 'refund') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('return_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="return_status" class="{{ $labelClass }}">
                    Return status <span class="text-red-400 normal-case">*</span>
                </label>
                <select id="return_status" name="return_status"
                        class="{{ $errors->has('return_status') ? $inputErrClass : $inputClass }}">
                    @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $val=>$label)
                        <option value="{{ $val }}"
                            {{ old('return_status', $saleReturn?->return_status ?? 'pending') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('return_status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="payment_method" class="{{ $labelClass }}">Payment method</label>
                <select id="payment_method" name="payment_method" class="{{ $inputClass }}">
                    <option value="">— Select —</option>
                    @foreach(['Cash','Bank','Bkash','Nagad','Card'] as $m)
                        <option value="{{ $m }}"
                            {{ old('payment_method', $saleReturn?->payment_method) == $m ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════
     6 — Reference & Details
══════════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-gray-100 text-gray-500 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Reference &amp; Details</span>
    </div>
    <div class="{{ $bodyClass }} space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="cash_memo" class="{{ $labelClass }}">Cash memo #</label>
                <input type="text" id="cash_memo" name="cash_memo"
                       value="{{ old('cash_memo', $saleReturn?->cash_memo) }}"
                       placeholder="Memo number"
                       class="{{ $inputClass }}"/>
            </div>
            <div>
                <label for="date" class="{{ $labelClass }}">
                    Date <span class="text-red-400 normal-case">*</span>
                </label>
                <input type="date" id="date" name="date"
                       value="{{ old('date', optional($saleReturn?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="{{ $errors->has('date') ? $inputErrClass : $inputClass }}"/>
                @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label for="reason" class="{{ $labelClass }}">Return Reason</label>
            <textarea id="reason" name="reason" rows="2"
                      placeholder="Why is this being returned?"
                      class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none
                             text-gray-800 placeholder-gray-400
                             focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">{{ old('reason', $saleReturn?->reason) }}</textarea>
        </div>

        <div>
            <label for="note" class="{{ $labelClass }}">Note</label>
            <textarea id="note" name="note" rows="2"
                      placeholder="Any additional notes…"
                      class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none
                             text-gray-800 placeholder-gray-400
                             focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">{{ old('note', $saleReturn?->note) }}</textarea>
        </div>

        <div>
            <label for="document" class="{{ $labelClass }}">Attachment</label>
            <input type="file" id="document" name="document"
                   class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer
                          file:h-7 file:mr-3 file:px-3 file:rounded-md file:border-0
                          file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                          hover:file:bg-blue-100 transition"/>
            @if($saleReturn?->document)
                <a href="{{ asset('storage/'.$saleReturn->document) }}" target="_blank"
                   class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View current file
                </a>
            @endif
            @error('document') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@push('scripts')
<script>
function calcReturnTotal() {
    const qty      = parseFloat(document.getElementById('qty').value)           || 0;
    const price    = parseFloat(document.getElementById('price_on_sale').value) || 0;
    const discount = parseFloat(document.getElementById('discount').value)      || 0;
    const subtotal = (qty * price) - discount;
    const fmt = v => '৳' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('subtotal-display').textContent      = fmt(subtotal);
    document.getElementById('return-amount-display').textContent = fmt(subtotal);
}

function fillReturnProductFields(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('product_name_hidden').value = opt.dataset.name || '';
    document.getElementById('product_code').value        = opt.dataset.sku  || '';
}

function prefillFromSale(sel) {
    const opt = sel.options[sel.selectedIndex];
    if (!sel.value) return;

    // customer
    const custSel = document.getElementById('customer_id');
    if (custSel && opt.dataset.customer) custSel.value = opt.dataset.customer;

    // product
    const prodSel = document.getElementById('product_id');
    if (prodSel && opt.dataset.product) {
        prodSel.value = opt.dataset.product;
        document.getElementById('product_name_hidden').value = opt.dataset.productName || '';
        // find sku from selected product option
        const prodOpt = prodSel.querySelector(`option[value="${opt.dataset.product}"]`);
        if (prodOpt) document.getElementById('product_code').value = prodOpt.dataset.sku || '';
    }

    // price
    const priceEl = document.getElementById('price_on_sale');
    if (priceEl && opt.dataset.price) {
        priceEl.value = opt.dataset.price;
        calcReturnTotal();
    }
}

document.addEventListener('DOMContentLoaded', () => calcReturnTotal());
</script>
@endpush