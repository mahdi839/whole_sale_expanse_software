@php $purchase = $purchase ?? null; @endphp

{{-- Section helper macro --}}
@php
    $sectionClass = 'bg-white border border-gray-200 rounded-xl overflow-hidden';
    $headerClass  = 'flex items-center gap-2.5 px-5 py-3 border-b border-gray-100 bg-gray-50/60';
    $bodyClass    = 'p-5';
    $labelClass   = 'block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5';
    $inputClass   = 'w-full h-9 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg
                     text-gray-800 placeholder-gray-400
                     focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition';
    $inputErrClass = 'w-full h-9 px-3 text-sm bg-red-50 border border-red-300 rounded-lg
                      text-gray-800 placeholder-gray-400
                      focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 transition';
@endphp

{{-- ══════════════════════════
     1 — Supplier & Purchaser
══════════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Supplier &amp; Purchaser</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Supplier --}}
            <div>
                <label for="supplier_id" class="{{ $labelClass }}">
                    Supplier <span class="text-red-400 normal-case">*</span>
                </label>
                <div class="flex gap-2">
                    <select id="supplier_id" name="supplier_id"
                            onchange="fillSupplierName(this)"
                            class="{{ $errors->has('supplier_id') ? $inputErrClass : $inputClass }} flex-1">
                        <option value="">— Select supplier —</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}"
                                    data-name="{{ $s->name }}"
                                {{ old('supplier_id', $purchase?->supplier_id) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}{{ $s->phone ? ' · '.$s->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="openModal('supplierModal')"
                            class="flex-none flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50
                                   border border-blue-200 text-blue-600 hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
                <input type="hidden" id="seller_store_name_hidden" name="seller_store_name"
                       value="{{ old('seller_store_name', $purchase?->seller_store_name) }}">
                <div id="manual-seller-wrap" class="mt-2 {{ old('supplier_id', $purchase?->supplier_id) ? 'hidden' : '' }}">
                    <input type="text" id="seller_store_name_manual"
                           placeholder="Or type seller / store name"
                           value="{{ old('seller_store_name', $purchase?->seller_store_name) }}"
                           oninput="document.getElementById('seller_store_name_hidden').value = this.value"
                           class="{{ $inputClass }}"/>
                </div>
                @error('supplier_id')       <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                @error('seller_store_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Purchased by --}}
            <div>
                <label for="purchased_by" class="{{ $labelClass }}">
                    Purchased by <span class="text-red-400 normal-case">*</span>
                </label>
                <input type="text" id="purchased_by" name="purchased_by"
                       value="{{ old('purchased_by', $purchase?->purchased_by) }}"
                       placeholder="e.g. Hasan"
                       class="{{ $errors->has('purchased_by') ? $inputErrClass : $inputClass }}"/>
                @error('purchased_by') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════
     2 — Product
══════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-violet-50 text-violet-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                <path d="M16 3v4M8 3v4"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Product</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Product dropdown --}}
            <div>
                <label for="product_id" class="{{ $labelClass }}">
                    Product <span class="text-red-400 normal-case">*</span>
                </label>
                <div class="flex gap-2">
                    <select id="product_id" name="product_id"
                            onchange="fillProductName(this)"
                            class="{{ $errors->has('product_id') ? $inputErrClass : $inputClass }} flex-1">
                        <option value="">— Select product —</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}"
                                    data-name="{{ $p->product_name }}"
                                    data-sku="{{ $p->sku }}"
                                {{ old('product_id', $purchase?->product_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->product_name }}{{ $p->sku ? ' ['.$p->sku.']' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="openModal('productModal')"
                            class="flex-none flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50
                                   border border-blue-200 text-blue-600 hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>
                </div>
                <div id="manual-product-wrap" class="mt-2 {{ old('product_id', $purchase?->product_id) ? 'hidden' : '' }}">
                    <input type="text" id="product_name_manual"
                           placeholder="Or type product name"
                           value="{{ old('product_name', $purchase?->product_name) }}"
                           oninput="document.getElementById('product_name_hidden').value = this.value"
                           class="{{ $inputClass }}"/>
                </div>
                <input type="hidden" id="product_name_hidden" name="product_name"
                       value="{{ old('product_name', $purchase?->product_name) }}">
                @error('product_id')   <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                @error('product_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- SKU --}}
            <div>
                <label for="product_code" class="{{ $labelClass }}">Product code / SKU</label>
                <input type="text" id="product_code" name="product_code"
                       value="{{ old('product_code', $purchase?->product_code) }}"
                       placeholder="Auto-filled or enter manually"
                       class="{{ $inputClass }}"/>
                @error('product_code') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════
     3 — Pricing
══════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-green-50 text-green-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Pricing</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="qty" class="{{ $labelClass }}">
                    Qty <span class="text-red-400 normal-case">*</span>
                </label>
                <input type="number" id="qty" name="qty"
                       value="{{ old('qty', $purchase?->qty ?? '1') }}"
                       min="0.01" step="0.01"
                       oninput="calcPurchaseTotal()"
                       class="{{ $errors->has('qty') ? $inputErrClass : $inputClass }}"/>
                @error('qty') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="price" class="{{ $labelClass }}">
                    Unit price (৳) <span class="text-red-400 normal-case">*</span>
                </label>
                <input type="number" id="price" name="price"
                       value="{{ old('price', $purchase?->price ?? '0') }}"
                       min="0" step="0.01"
                       oninput="calcPurchaseTotal()"
                       class="{{ $errors->has('price') ? $inputErrClass : $inputClass }}"/>
                @error('price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="other_cost" class="{{ $labelClass }}">Other cost (৳)</label>
                <input type="number" id="other_cost" name="other_cost"
                       value="{{ old('other_cost', $purchase?->other_cost ?? '0') }}"
                       min="0" step="0.01"
                       oninput="calcPurchaseTotal()"
                       class="{{ $inputClass }}"/>
                @error('other_cost') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Live summary --}}
        <div class="grid grid-cols-2 gap-3 mt-4">
            <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Subtotal</span>
                <span id="subtotal-display" class="text-base font-semibold text-gray-800">৳0.00</span>
            </div>
            <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-4 py-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Grand total</span>
                <span id="grand-total-display" class="text-base font-semibold text-green-700">৳0.00</span>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════
     4 — Status & Payment
══════════════════════════ --}}
<div class="{{ $sectionClass }}">
    <div class="{{ $headerClass }}">
        <span class="flex items-center justify-center w-6 h-6 rounded-md bg-amber-50 text-amber-700 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>
        <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Status &amp; Payment</span>
    </div>
    <div class="{{ $bodyClass }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="purchase_status" class="{{ $labelClass }}">
                    Purchase status <span class="text-red-400 normal-case">*</span>
                </label>
                <select id="purchase_status" name="purchase_status"
                        class="{{ $errors->has('purchase_status') ? $inputErrClass : $inputClass }}">
                    @foreach(['received'=>'Received','partial'=>'Partial','pending'=>'Pending','ordered'=>'Ordered'] as $val=>$label)
                        <option value="{{ $val }}"
                            {{ old('purchase_status', $purchase?->purchase_status ?? 'pending') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('purchase_status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="payment_status" class="{{ $labelClass }}">
                    Payment status <span class="text-red-400 normal-case">*</span>
                </label>
                <select id="payment_status" name="payment_status"
                        onchange="togglePaymentFields()"
                        class="{{ $errors->has('payment_status') ? $inputErrClass : $inputClass }}">
                    @foreach(['due'=>'Due','paid'=>'Paid','partial'=>'Partial'] as $val=>$label)
                        <option value="{{ $val }}"
                            {{ old('payment_status', $purchase?->payment_status ?? 'due') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('payment_status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="payment_method" class="{{ $labelClass }}">Payment method</label>
                <select id="payment_method" name="payment_method" class="{{ $inputClass }}">
                    <option value="">— Select —</option>
                    @foreach(['Cash','Bank','Bkash','Nagad','Card'] as $m)
                        <option value="{{ $m }}"
                            {{ old('payment_method', $purchase?->payment_method) == $m ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
                @error('payment_method') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <div id="paid-amount-field" class="hidden">
                <label for="paid_amount" class="block text-xs font-medium text-green-700 uppercase tracking-wide mb-1.5">
                    Paid amount (৳)
                </label>
                <input type="number" id="paid_amount" name="paid_amount"
                       value="{{ old('paid_amount', $purchase?->paid_amount ?? '0') }}"
                       min="0" step="0.01"
                       oninput="syncDueFromPaid()"
                       class="w-full h-9 px-3 text-sm bg-green-50 border border-green-200 rounded-lg
                              text-gray-800 focus:outline-none focus:ring-2 focus:ring-green-400/20 focus:border-green-400 transition"/>
                @error('paid_amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div id="due-amount-field" class="hidden">
                <label for="due_amount" class="block text-xs font-medium text-red-600 uppercase tracking-wide mb-1.5">
                    Due amount (৳)
                </label>
                <input type="number" id="due_amount" name="due_amount"
                       value="{{ old('due_amount', $purchase?->due_amount ?? '0') }}"
                       min="0" step="0.01"
                       class="w-full h-9 px-3 text-sm bg-red-50 border border-red-200 rounded-lg
                              text-gray-800 focus:outline-none focus:ring-2 focus:ring-red-400/20 focus:border-red-400 transition"/>
                @error('due_amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════
     5 — Reference & Details
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="cash_memo" class="{{ $labelClass }}">Cash memo #</label>
                <input type="text" id="cash_memo" name="cash_memo"
                       value="{{ old('cash_memo', $purchase?->cash_memo) }}"
                       placeholder="Memo number"
                       class="{{ $inputClass }}"/>
                @error('cash_memo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="date" class="{{ $labelClass }}">
                    Date <span class="text-red-400 normal-case">*</span>
                </label>
                <input type="date" id="date" name="date"
                       value="{{ old('date', optional($purchase?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="{{ $errors->has('date') ? $inputErrClass : $inputClass }}"/>
                @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="document" class="{{ $labelClass }}">Attachment</label>
                <input type="file" id="document" name="document"
                       class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg cursor-pointer
                              file:h-7 file:mr-3 file:px-3 file:rounded-md file:border-0
                              file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100 transition"/>
                @if($purchase?->document)
                    <a href="{{ asset('storage/'.$purchase->document) }}" target="_blank"
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

        <div>
            <label for="note" class="{{ $labelClass }}">Note</label>
            <textarea id="note" name="note" rows="3"
                      placeholder="Any additional notes about this purchase…"
                      class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg resize-none
                             text-gray-800 placeholder-gray-400
                             focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">{{ old('note', $purchase?->note) }}</textarea>
            @error('note') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>


{{-- ══════════════════════
     Modal — New Supplier
══════════════════════ --}}
<div id="supplierModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
     onclick="closeModal('supplierModal', event)">
    <div class="bg-white rounded-xl w-full max-w-lg border border-gray-200 overflow-hidden"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50/60">
            <h3 class="text-sm font-semibold text-gray-800">New Supplier</h3>
            <button type="button" onclick="closeModal('supplierModal')"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition text-base leading-none">✕</button>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">Name <span class="text-red-400 normal-case">*</span></label>
                <input type="text" id="new_supplier_name" placeholder="Supplier name" class="{{ $inputClass }}"/>
            </div>
            <div>
                <label class="{{ $labelClass }}">Phone</label>
                <input type="text" id="new_supplier_phone" placeholder="01XXXXXXXXX" class="{{ $inputClass }}"/>
            </div>
            <div>
                <label class="{{ $labelClass }}">Email</label>
                <input type="email" id="new_supplier_email" placeholder="email@example.com" class="{{ $inputClass }}"/>
            </div>
            <div>
                <label class="{{ $labelClass }}">Address</label>
                <input type="text" id="new_supplier_address" placeholder="Address" class="{{ $inputClass }}"/>
            </div>
            <div id="supplier-modal-error" class="hidden col-span-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
        </div>
        <div class="flex items-center justify-end gap-2.5 px-5 py-4 border-t border-gray-100 bg-gray-50/60">
            <button type="button" onclick="closeModal('supplierModal')"
                    class="h-8 px-3.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="button" onclick="saveSupplier()" id="save-supplier-btn"
                    class="h-8 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                Save supplier
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════
     Modal — New Product
══════════════════════ --}}
<div id="productModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
     onclick="closeModal('productModal', event)">
    <div class="bg-white rounded-xl w-full max-w-lg border border-gray-200 overflow-hidden"
         onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50/60">
            <h3 class="text-sm font-semibold text-gray-800">New Product</h3>
            <button type="button" onclick="closeModal('productModal')"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition text-base leading-none">✕</button>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="{{ $labelClass }}">Product name <span class="text-red-400 normal-case">*</span></label>
                <input type="text" id="new_product_name" placeholder="Product name" class="{{ $inputClass }}"/>
            </div>
            <div>
                <label class="{{ $labelClass }}">SKU <span class="text-red-400 normal-case">*</span></label>
                <input type="text" id="new_product_sku" placeholder="PRD-001" class="{{ $inputClass }}"/>
            </div>
            <div id="product-modal-error" class="hidden col-span-2 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
        </div>
        <div class="flex items-center justify-end gap-2.5 px-5 py-4 border-t border-gray-100 bg-gray-50/60">
            <button type="button" onclick="closeModal('productModal')"
                    class="h-8 px-3.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <button type="button" onclick="saveProduct()" id="save-product-btn"
                    class="h-8 px-4 inline-flex items-center gap-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                Save product
            </button>
        </div>
    </div>
</div>


@push('scripts')
<script>
{{-- All JS is unchanged from the original --}}
function calcPurchaseTotal() {
    const qty       = parseFloat(document.getElementById('qty').value)        || 0;
    const price     = parseFloat(document.getElementById('price').value)       || 0;
    const otherCost = parseFloat(document.getElementById('other_cost').value)  || 0;
    const subtotal  = qty * price;
    const grand     = subtotal + otherCost;
    const fmt = v => '৳' + v.toLocaleString('en-US', { minimumFractionDigits:2, maximumFractionDigits:2 });
    document.getElementById('subtotal-display').textContent    = fmt(subtotal);
    document.getElementById('grand-total-display').textContent = fmt(grand);
    const ps = document.getElementById('payment_status').value;
    if (ps === 'due')     setVal('due_amount',  grand);
    if (ps === 'paid')    setVal('paid_amount', grand);
    if (ps === 'partial') syncDueFromPaid();
}

function setVal(id, v) {
    const el = document.getElementById(id);
    if (el) el.value = v.toFixed(2);
}

function syncDueFromPaid() {
    const grand = grandTotalNum();
    const paid  = parseFloat(document.getElementById('paid_amount')?.value) || 0;
    setVal('due_amount', Math.max(0, grand - paid));
}

function grandTotalNum() {
    const qty   = parseFloat(document.getElementById('qty').value)        || 0;
    const price = parseFloat(document.getElementById('price').value)       || 0;
    const other = parseFloat(document.getElementById('other_cost').value)  || 0;
    return qty * price + other;
}

function togglePaymentFields() {
    const status    = document.getElementById('payment_status').value;
    const dueField  = document.getElementById('due-amount-field');
    const paidField = document.getElementById('paid-amount-field');
    const grand     = grandTotalNum();
    dueField.classList.toggle('hidden',  !['due',  'partial'].includes(status));
    paidField.classList.toggle('hidden', !['paid', 'partial'].includes(status));
    if (status === 'due')     { setVal('due_amount',  grand); setVal('paid_amount', 0); }
    if (status === 'paid')    { setVal('paid_amount', grand); setVal('due_amount',  0); }
    if (status === 'partial') { syncDueFromPaid(); }
}

function fillSupplierName(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('seller_store_name_hidden').value = opt.dataset.name || '';
    const wrap = document.getElementById('manual-seller-wrap');
    if (sel.value) {
        wrap.classList.add('hidden');
    } else {
        wrap.classList.remove('hidden');
        document.getElementById('seller_store_name_hidden').value =
            document.getElementById('seller_store_name_manual').value;
    }
}

function fillProductName(sel) {
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('product_name_hidden').value = opt.dataset.name || '';
    document.getElementById('product_code').value        = opt.dataset.sku  || '';
    document.getElementById('manual-product-wrap').classList.toggle('hidden', !!sel.value);
}

function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id, e) {
    if (!e || e.target === document.getElementById(id))
        document.getElementById(id).classList.add('hidden');
}

async function saveSupplier() {
    const name    = document.getElementById('new_supplier_name').value.trim();
    const phone   = document.getElementById('new_supplier_phone').value.trim();
    const email   = document.getElementById('new_supplier_email').value.trim();
    const address = document.getElementById('new_supplier_address').value.trim();
    const errEl   = document.getElementById('supplier-modal-error');
    if (!name) { showModalError(errEl, 'Supplier name is required.'); return; }
    errEl.classList.add('hidden');
    const btn = document.getElementById('save-supplier-btn');
    btn.disabled = true;
    try {
        const res  = await fetch('{{ route("suppliers.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ name, phone, email, address }),
        });
        const data = await res.json();
        if (!res.ok) {
            showModalError(errEl, data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error saving supplier.'));
        } else {
            const sel = document.getElementById('supplier_id');
            const opt = new Option(data.name + (data.phone ? ' · ' + data.phone : ''), data.id, true, true);
            opt.dataset.name = data.name;
            sel.add(opt);
            sel.value = data.id;
            fillSupplierName(sel);
            closeModal('supplierModal');
            ['new_supplier_name','new_supplier_phone','new_supplier_email','new_supplier_address']
                .forEach(id => document.getElementById(id).value = '');
        }
    } catch (err) {
        showModalError(errEl, 'Network error. Please try again.');
    } finally {
        btn.disabled = false;
    }
}

async function saveProduct() {
    const name  = document.getElementById('new_product_name').value.trim();
    const sku   = document.getElementById('new_product_sku').value.trim();
    const errEl = document.getElementById('product-modal-error');
    if (!name) { showModalError(errEl, 'Product name is required.'); return; }
    if (!sku)  { showModalError(errEl, 'SKU is required.'); return; }
    errEl.classList.add('hidden');
    const btn = document.getElementById('save-product-btn');
    btn.disabled = true;
    try {
        const res  = await fetch('{{ route("products.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ product_name: name, sku }),
        });
        const data = await res.json();
        if (!res.ok) {
            showModalError(errEl, data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Error saving product.'));
        } else {
            const sel = document.getElementById('product_id');
            const opt = new Option(data.product_name + ' [' + data.sku + ']', data.id, true, true);
            opt.dataset.name = data.product_name;
            opt.dataset.sku  = data.sku;
            sel.add(opt);
            sel.value = data.id;
            fillProductName(sel);
            closeModal('productModal');
            document.getElementById('new_product_name').value = '';
            document.getElementById('new_product_sku').value  = '';
        }
    } catch (err) {
        showModalError(errEl, 'Network error. Please try again.');
    } finally {
        btn.disabled = false;
    }
}

function showModalError(el, msg) {
    el.textContent = msg;
    el.classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    calcPurchaseTotal();
    togglePaymentFields();
});
</script>
@endpush