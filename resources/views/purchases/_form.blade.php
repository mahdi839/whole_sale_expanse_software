@php $purchase = $purchase ?? null; @endphp

{{-- ════════════════════════════════════════════
     SECTION 1 — Supplier & Purchaser
════════════════════════════════════════════ --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4 shadow-sm">
    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">
        <span>🏪</span> Supplier & Purchaser
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

        {{-- Supplier dropdown --}}
        <div class="space-y-1.5">
            <label for="supplier_id" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Supplier <span class="text-red-400">*</span>
            </label>

            <div class="flex items-stretch gap-2">
                <select id="supplier_id" name="supplier_id"
                        onchange="fillSupplierName(this)"
                        class="flex-1 px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                               {{ $errors->has('supplier_id') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
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
                        title="Add new supplier"
                        class="flex-none flex items-center justify-center w-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
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
                       class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400"/>
            </div>
            @error('supplier_id')      <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            @error('seller_store_name')<p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Purchased by --}}
        <div class="space-y-1.5">
            <label for="purchased_by" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Purchased By <span class="text-red-400">*</span>
            </label>
            <input type="text" id="purchased_by" name="purchased_by"
                   value="{{ old('purchased_by', $purchase?->purchased_by) }}"
                   placeholder="e.g. Hasan"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400
                          {{ $errors->has('purchased_by') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"/>
            @error('purchased_by') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECTION 2 — Product
════════════════════════════════════════════ --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4 shadow-sm">
    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">
        <span>📦</span> Product
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

        {{-- Product dropdown --}}
        <div class="space-y-1.5">
            <label for="product_id" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Product <span class="text-red-400">*</span>
            </label>

            <div class="flex items-stretch gap-2">
                <select id="product_id" name="product_id"
                        onchange="fillProductName(this)"
                        class="flex-1 px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                               {{ $errors->has('product_id') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
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
                        title="Add new product"
                        class="flex-none flex items-center justify-center w-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 hover:bg-blue-100 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                </button>
            </div>

            <div id="manual-product-wrap" class="mt-2 {{ old('product_id', $purchase?->product_id) ? 'hidden' : '' }}">
                <input type="text" id="product_name_manual"
                       placeholder="Or type product name"
                       value="{{ old('product_name', $purchase?->product_name) }}"
                       oninput="document.getElementById('product_name_hidden').value = this.value"
                       class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400"/>
            </div>
            <input type="hidden" id="product_name_hidden" name="product_name"
                   value="{{ old('product_name', $purchase?->product_name) }}">
            @error('product_id')   <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            @error('product_name') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Product Code --}}
        <div class="space-y-1.5">
            <label for="product_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Product Code / SKU
            </label>
            <input type="text" id="product_code" name="product_code"
                   value="{{ old('product_code', $purchase?->product_code) }}"
                   placeholder="Auto-filled or enter manually"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400"/>
            @error('product_code') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECTION 3 — Pricing
════════════════════════════════════════════ --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4 shadow-sm">
    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">
        <span>💰</span> Pricing
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="space-y-1.5">
            <label for="qty" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Qty <span class="text-red-400">*</span>
            </label>
            <input type="number" id="qty" name="qty"
                   value="{{ old('qty', $purchase?->qty ?? '1') }}"
                   min="0.01" step="0.01"
                   oninput="calcPurchaseTotal()"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                          {{ $errors->has('qty') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"/>
            @error('qty') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="price" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Unit Price (৳) <span class="text-red-400">*</span>
            </label>
            <input type="number" id="price" name="price"
                   value="{{ old('price', $purchase?->price ?? '0') }}"
                   min="0" step="0.01"
                   oninput="calcPurchaseTotal()"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                          {{ $errors->has('price') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"/>
            @error('price') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="other_cost" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Other Cost (৳)
            </label>
            <input type="number" id="other_cost" name="other_cost"
                   value="{{ old('other_cost', $purchase?->other_cost ?? '0') }}"
                   min="0" step="0.01"
                   oninput="calcPurchaseTotal()"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400"/>
            @error('other_cost') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Live summary strip --}}
    <div class="grid grid-cols-2 gap-4 pt-1">
        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Subtotal</span>
            <span id="subtotal-display" class="text-lg font-bold text-gray-800">৳0.00</span>
        </div>
        <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Grand Total</span>
            <span id="grand-total-display" class="text-lg font-bold text-green-700">৳0.00</span>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECTION 4 — Status & Payment
════════════════════════════════════════════ --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4 shadow-sm">
    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">
        <span>🔖</span> Status & Payment
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="space-y-1.5">
            <label for="purchase_status" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Purchase Status <span class="text-red-400">*</span>
            </label>
            <select id="purchase_status" name="purchase_status"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                           {{ $errors->has('purchase_status') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
                @foreach(['received'=>'Received','partial'=>'Partial','pending'=>'Pending','ordered'=>'Ordered'] as $val=>$label)
                    <option value="{{ $val }}"
                        {{ old('purchase_status', $purchase?->purchase_status ?? 'pending') == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('purchase_status') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="payment_status" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Payment Status <span class="text-red-400">*</span>
            </label>
            <select id="payment_status" name="payment_status"
                    onchange="togglePaymentFields()"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                           {{ $errors->has('payment_status') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
                @foreach(['due'=>'Due','paid'=>'Paid','partial'=>'Partial'] as $val=>$label)
                    <option value="{{ $val }}"
                        {{ old('payment_status', $purchase?->payment_status ?? 'due') == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('payment_status') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="payment_method" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Payment Method
            </label>
            <select id="payment_method" name="payment_method"
                    class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                <option value="">— Select —</option>
                @foreach(['Cash','Bank','Bkash','Nagad','Card'] as $m)
                    <option value="{{ $m }}"
                        {{ old('payment_method', $purchase?->payment_method) == $m ? 'selected' : '' }}>
                        {{ $m }}
                    </option>
                @endforeach
            </select>
            @error('payment_method') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Dynamic payment amount fields --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div id="due-amount-field" class="hidden space-y-1.5">
            <label for="due_amount" class="block text-xs font-semibold text-red-600 uppercase tracking-wide">
                Due Amount (৳)
            </label>
            <input type="number" id="due_amount" name="due_amount"
                   value="{{ old('due_amount', $purchase?->due_amount ?? '0') }}"
                   min="0" step="0.01"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-red-200 bg-red-50/40 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-red-400"/>
            @error('due_amount') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div id="paid-amount-field" class="hidden space-y-1.5">
            <label for="paid_amount" class="block text-xs font-semibold text-green-700 uppercase tracking-wide">
                Paid Amount (৳)
            </label>
            <input type="number" id="paid_amount" name="paid_amount"
                   value="{{ old('paid_amount', $purchase?->paid_amount ?? '0') }}"
                   min="0" step="0.01"
                   oninput="syncDueFromPaid()"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-green-200 bg-green-50/40 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-green-400"/>
            @error('paid_amount') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     SECTION 5 — Reference & Details
════════════════════════════════════════════ --}}
<div class="bg-white border border-gray-100 rounded-2xl p-5 space-y-4 shadow-sm">
    <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-700 border-b border-gray-100 pb-3">
        <span>📋</span> Reference & Details
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="space-y-1.5">
            <label for="cash_memo" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Cash Memo #
            </label>
            <input type="text" id="cash_memo" name="cash_memo"
                   value="{{ old('cash_memo', $purchase?->cash_memo) }}"
                   placeholder="Memo number"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400"/>
            @error('cash_memo') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="date" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Date <span class="text-red-400">*</span>
            </label>
            <input type="date" id="date" name="date"
                   value="{{ old('date', optional($purchase?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl border bg-white transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400
                          {{ $errors->has('date') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}"/>
            @error('date') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1.5">
            <label for="document" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                Attachment
            </label>
            <input type="file" id="document" name="document"
                   class="w-full text-sm text-gray-500 border border-gray-200 rounded-xl cursor-pointer transition
                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                          file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700
                          hover:file:bg-blue-100"/>
            @if($purchase?->document)
                <a href="{{ asset('storage/'.$purchase->document) }}" target="_blank"
                   class="mt-1 inline-flex items-center gap-1 text-xs text-blue-600 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View current file
                </a>
            @endif
            @error('document') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="space-y-1.5">
        <label for="note" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Note</label>
        <textarea id="note" name="note" rows="3"
                  placeholder="Any additional notes about this purchase…"
                  class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white resize-none transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400">{{ old('note', $purchase?->note) }}</textarea>
        @error('note') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
    </div>
</div>


{{-- ════════════════════════════════════════════
     MODAL — Quick-add Supplier
════════════════════════════════════════════ --}}
<div id="supplierModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
     onclick="closeModal('supplierModal', event)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg ring-1 ring-black/5 overflow-hidden"
         onclick="event.stopPropagation()">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-base font-semibold text-gray-800">New Supplier</h3>
            <button type="button" onclick="closeModal('supplierModal')"
                    class="w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition text-lg leading-none">
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                        Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="new_supplier_name" placeholder="Supplier name"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400 transition"/>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Phone</label>
                    <input type="text" id="new_supplier_phone" placeholder="01XXXXXXXXX"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400 transition"/>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Email</label>
                    <input type="email" id="new_supplier_email" placeholder="email@example.com"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400 transition"/>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">Address</label>
                    <input type="text" id="new_supplier_address" placeholder="Address"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400 transition"/>
                </div>
            </div>
            <div id="supplier-modal-error"
                 class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
        </div>

        <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100 bg-gray-50">
            <button type="button" onclick="closeModal('supplierModal')"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" onclick="saveSupplier()" id="save-supplier-btn"
                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7"/>
                </svg>
                Save Supplier
            </button>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════
     MODAL — Quick-add Product
════════════════════════════════════════════ --}}
<div id="productModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4"
     onclick="closeModal('productModal', event)">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg ring-1 ring-black/5 overflow-hidden"
         onclick="event.stopPropagation()">

        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-base font-semibold text-gray-800">New Product</h3>
            <button type="button" onclick="closeModal('productModal')"
                    class="w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition text-lg leading-none">
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                        Product Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="new_product_name" placeholder="Product name"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400 transition"/>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                        SKU <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="new_product_sku" placeholder="PRD-001"
                           class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 placeholder:text-gray-400 transition"/>
                </div>
            </div>
            <div id="product-modal-error"
                 class="hidden text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2"></div>
        </div>

        <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-100 bg-gray-50">
            <button type="button" onclick="closeModal('productModal')"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                Cancel
            </button>
            <button type="button" onclick="saveProduct()" id="save-product-btn"
                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7"/>
                </svg>
                Save Product
            </button>
        </div>
    </div>
</div>


@push('scripts')
<script>
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