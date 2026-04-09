{{-- Shared by create.blade.php and edit.blade.php --}}
@php $customer = $customer ?? null; @endphp

{{-- Full Name --}}
<div class="space-y-1.5">
    <label for="full_name" class="block text-sm font-medium text-gray-700">
        Full Name <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="full_name"
        name="full_name"
        value="{{ old('full_name', $customer?->full_name) }}"
        placeholder="e.g. Rahim Uddin"
        class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
               @error('full_name') border-red-400 bg-red-50 focus:ring-red-400
               @else border-gray-200 focus:ring-blue-500 @enderror
               focus:outline-none focus:ring-2 focus:border-transparent"
    />
    @error('full_name')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Phone --}}
<div class="space-y-1.5">
    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
    <input
        type="text"
        id="phone"
        name="phone"
        value="{{ old('phone', $customer?->phone) }}"
        placeholder="e.g. 01700-000000"
        class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
    />
    @error('phone')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Financial fields --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Total Sale --}}
    <div class="space-y-1.5">
        <label for="total_sale" class="block text-sm font-medium text-gray-700">Total Sale (৳)</label>
        <input
            type="number"
            id="total_sale"
            name="total_sale"
            value="{{ old('total_sale', $customer?->total_sale ?? '0') }}"
            min="0"
            step="0.01"
            class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                   @error('total_sale') border-red-400 bg-red-50 focus:ring-red-400
                   @else border-gray-200 focus:ring-blue-500 @enderror
                   focus:outline-none focus:ring-2 focus:border-transparent"
            oninput="calcDue()"
        />
        @error('total_sale')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Total Paid --}}
    <div class="space-y-1.5">
        <label for="total_paid" class="block text-sm font-medium text-gray-700">Total Paid (৳)</label>
        <input
            type="number"
            id="total_paid"
            name="total_paid"
            value="{{ old('total_paid', $customer?->total_paid ?? '0') }}"
            min="0"
            step="0.01"
            class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                   @error('total_paid') border-red-400 bg-red-50 focus:ring-red-400
                   @else border-gray-200 focus:ring-blue-500 @enderror
                   focus:outline-none focus:ring-2 focus:border-transparent"
            oninput="calcDue()"
        />
        @error('total_paid')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Due (read-only, auto calculated) --}}
<div class="space-y-1.5">
    <label class="block text-sm font-medium text-gray-700">Due (৳)
        <span class="text-xs font-normal text-gray-400 ml-1">auto-calculated</span>
    </label>
    <div id="due-display"
         class="w-full px-3.5 py-2.5 text-sm font-medium rounded-lg border bg-gray-50 border-gray-200 text-gray-500 select-none">
        ৳{{ number_format(max(0, old('total_sale', $customer?->total_sale ?? 0) - old('total_paid', $customer?->total_paid ?? 0)), 2) }}
    </div>
    <p class="text-xs text-gray-400">Calculated as Total Sale − Total Paid</p>
</div>

@push('scripts')
<script>
function calcDue() {
    const sale = parseFloat(document.getElementById('total_sale').value) || 0;
    const paid = parseFloat(document.getElementById('total_paid').value) || 0;
    const due  = Math.max(0, sale - paid);

    const el = document.getElementById('due-display');
    el.textContent = '৳' + due.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    if (due > 0) {
        el.classList.remove('text-gray-500', 'bg-gray-50', 'border-gray-200');
        el.classList.add('text-red-700', 'bg-red-50', 'border-red-200');
    } else {
        el.classList.remove('text-red-700', 'bg-red-50', 'border-red-200');
        el.classList.add('text-gray-500', 'bg-gray-50', 'border-gray-200');
    }
}
// Run once on page load to colour correctly
calcDue();
</script>
@endpush