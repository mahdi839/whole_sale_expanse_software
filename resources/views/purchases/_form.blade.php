@php $purchase = $purchase ?? null; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Seller / Store Name --}}
    <div class="space-y-1.5">
        <label for="seller_store_name" class="block text-sm font-medium text-gray-700">
            Seller / Store Name <span class="text-red-500">*</span>
        </label>
        <input type="text" id="seller_store_name" name="seller_store_name"
               value="{{ old('seller_store_name', $purchase?->seller_store_name) }}"
               placeholder="e.g. ABC Traders"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('seller_store_name') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('seller_store_name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Purchased By --}}
    <div class="space-y-1.5">
        <label for="purchased_by" class="block text-sm font-medium text-gray-700">
            Purchased By <span class="text-red-500">*</span>
        </label>
        <input type="text" id="purchased_by" name="purchased_by"
               value="{{ old('purchased_by', $purchase?->purchased_by) }}"
               placeholder="e.g. Hasan"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('purchased_by') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('purchased_by')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Product Name --}}
    <div class="space-y-1.5">
        <label for="product_name" class="block text-sm font-medium text-gray-700">
            Product Name <span class="text-red-500">*</span>
        </label>
        <input type="text" id="product_name" name="product_name"
               value="{{ old('product_name', $purchase?->product_name) }}"
               placeholder="e.g. Rice Bag 25kg"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('product_name') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('product_name')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Product Code --}}
    <div class="space-y-1.5">
        <label for="product_code" class="block text-sm font-medium text-gray-700">Product Code</label>
        <input type="text" id="product_code" name="product_code"
               value="{{ old('product_code', $purchase?->product_code) }}"
               placeholder="e.g. PRD-001"
               class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
        @error('product_code')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    {{-- Qty --}}
    <div class="space-y-1.5">
        <label for="qty" class="block text-sm font-medium text-gray-700">
            Qty <span class="text-red-500">*</span>
        </label>
        <input type="number" id="qty" name="qty"
               value="{{ old('qty', $purchase?->qty ?? '1') }}"
               min="0.01" step="0.01"
               oninput="calcPurchaseTotal()"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('qty') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('qty')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Price --}}
    <div class="space-y-1.5">
        <label for="price" class="block text-sm font-medium text-gray-700">
            Price <span class="text-red-500">*</span>
        </label>
        <input type="number" id="price" name="price"
               value="{{ old('price', $purchase?->price ?? '0') }}"
               min="0" step="0.01"
               oninput="calcPurchaseTotal()"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('price') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('price')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Other Cost --}}
    <div class="space-y-1.5">
        <label for="other_cost" class="block text-sm font-medium text-gray-700">Other Cost</label>
        <input type="number" id="other_cost" name="other_cost"
               value="{{ old('other_cost', $purchase?->other_cost ?? '0') }}"
               min="0" step="0.01"
               oninput="calcPurchaseTotal()"
               class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
        @error('other_cost')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    {{-- Cash Memo --}}
    <div class="space-y-1.5">
        <label for="cash_memo" class="block text-sm font-medium text-gray-700">Cash Memo</label>
        <input type="text" id="cash_memo" name="cash_memo"
               value="{{ old('cash_memo', $purchase?->cash_memo) }}"
               placeholder="Memo number"
               class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
        @error('cash_memo')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Date --}}
    <div class="space-y-1.5">
        <label for="date" class="block text-sm font-medium text-gray-700">
            Date <span class="text-red-500">*</span>
        </label>
        <input type="date" id="date" name="date"
               value="{{ old('date', optional($purchase?->date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('date') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('date')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Payment Method --}}
    <div class="space-y-1.5">
        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
        <select id="payment_method" name="payment_method"
                class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            <option value="">Select payment method</option>
            <option value="Cash" {{ old('payment_method', $purchase?->payment_method) == 'Cash' ? 'selected' : '' }}>Cash</option>
            <option value="Bank" {{ old('payment_method', $purchase?->payment_method) == 'Bank' ? 'selected' : '' }}>Bank</option>
            <option value="Bkash" {{ old('payment_method', $purchase?->payment_method) == 'Bkash' ? 'selected' : '' }}>Bkash</option>
            <option value="Nagad" {{ old('payment_method', $purchase?->payment_method) == 'Nagad' ? 'selected' : '' }}>Nagad</option>
            <option value="Card" {{ old('payment_method', $purchase?->payment_method) == 'Card' ? 'selected' : '' }}>Card</option>
        </select>
        @error('payment_method')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- Purchase Status --}}
    <div class="space-y-1.5">
        <label for="purchase_status" class="block text-sm font-medium text-gray-700">
            Purchase Status <span class="text-red-500">*</span>
        </label>
        <select id="purchase_status" name="purchase_status"
                class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                       @error('purchase_status') border-red-400 bg-red-50 focus:ring-red-400
                       @else border-gray-200 focus:ring-blue-500 @enderror
                       focus:outline-none focus:ring-2 focus:border-transparent">
            <option value="received" {{ old('purchase_status', $purchase?->purchase_status ?? 'pending') == 'received' ? 'selected' : '' }}>Received</option>
            <option value="partial" {{ old('purchase_status', $purchase?->purchase_status) == 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="pending" {{ old('purchase_status', $purchase?->purchase_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="ordered" {{ old('purchase_status', $purchase?->purchase_status) == 'ordered' ? 'selected' : '' }}>Ordered</option>
        </select>
        @error('purchase_status')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Payment Status --}}
    <div class="space-y-1.5">
        <label for="payment_status" class="block text-sm font-medium text-gray-700">
            Payment Status <span class="text-red-500">*</span>
        </label>
        <select id="payment_status" name="payment_status"
                class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                       @error('payment_status') border-red-400 bg-red-50 focus:ring-red-400
                       @else border-gray-200 focus:ring-blue-500 @enderror
                       focus:outline-none focus:ring-2 focus:border-transparent">
            <option value="due" {{ old('payment_status', $purchase?->payment_status ?? 'due') == 'due' ? 'selected' : '' }}>Due</option>
            <option value="paid" {{ old('payment_status', $purchase?->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="partial" {{ old('payment_status', $purchase?->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
        </select>
        @error('payment_status')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="space-y-1.5">
    <label for="document" class="block text-sm font-medium text-gray-700">Document</label>
    <input type="file" id="document" name="document"
           class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
    @if($purchase?->document)
        <a href="{{ asset('storage/' . $purchase->document) }}" target="_blank"
           class="inline-flex mt-2 text-xs text-blue-600 hover:underline">
            View uploaded document
        </a>
    @endif
    @error('document')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-1.5">
    <label for="note" class="block text-sm font-medium text-gray-700">Note</label>
    <textarea id="note" name="note" rows="4"
              placeholder="Additional purchase note..."
              class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg resize-none
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">{{ old('note', $purchase?->note) }}</textarea>
    @error('note')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="border-t border-gray-100 pt-4">
    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-4">Purchase Summary</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">Subtotal (৳)</label>
            <div id="subtotal-display"
                 class="w-full px-3.5 py-2.5 text-sm font-semibold rounded-lg border bg-gray-50 border-gray-200 text-gray-700 select-none">
                ৳0.00
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="block text-sm font-medium text-gray-700">Grand Total (৳)</label>
            <div id="grand-total-display"
                 class="w-full px-3.5 py-2.5 text-sm font-semibold rounded-lg border bg-green-50 border-green-200 text-green-700 select-none">
                ৳0.00
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calcPurchaseTotal() {
    const qty = parseFloat(document.getElementById('qty').value) || 0;
    const price = parseFloat(document.getElementById('price').value) || 0;
    const otherCost = parseFloat(document.getElementById('other_cost').value) || 0;

    const subtotal = qty * price;
    const grandTotal = subtotal + otherCost;

    document.getElementById('subtotal-display').textContent =
        '৳' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    document.getElementById('grand-total-display').textContent =
        '৳' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

calcPurchaseTotal();
</script>
@endpush