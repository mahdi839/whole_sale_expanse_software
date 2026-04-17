{{-- Shared by suppliers/create.blade.php and suppliers/edit.blade.php --}}
@php $supplier = $supplier ?? null; @endphp

{{-- Name --}}
<div class="space-y-1.5">
    <label for="name" class="block text-sm font-medium text-gray-700">
        Supplier Name <span class="text-red-500">*</span>
    </label>
    <input type="text" id="name" name="name"
           value="{{ old('name', $supplier?->name) }}"
           placeholder="e.g. ABC Trading Co."
           class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                  @error('name') border-red-400 bg-red-50 focus:ring-red-400
                  @else border-gray-200 focus:ring-blue-500 @enderror
                  focus:outline-none focus:ring-2 focus:border-transparent"/>
    @error('name')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Phone + Email --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="space-y-1.5">
        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
        <input type="text" id="phone" name="phone"
               value="{{ old('phone', $supplier?->phone) }}"
               placeholder="e.g. 01700-000000"
               class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg
                      focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"/>
        @error('phone')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-1.5">
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email"
               value="{{ old('email', $supplier?->email) }}"
               placeholder="e.g. supplier@example.com"
               class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
                      @error('email') border-red-400 bg-red-50 focus:ring-red-400
                      @else border-gray-200 focus:ring-blue-500 @enderror
                      focus:outline-none focus:ring-2 focus:border-transparent"/>
        @error('email')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Address --}}
<div class="space-y-1.5">
    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
    <textarea id="address" name="address" rows="3"
              placeholder="Street, City, Country…"
              class="w-full px-3.5 py-2.5 text-sm border border-gray-200 rounded-lg resize-none
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">{{ old('address', $supplier?->address) }}</textarea>
    @error('address')
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

@push('scripts')
<script>
function calcDue() {
    const purchase = parseFloat(document.getElementById('total_purchase').value) || 0;
    const paid     = parseFloat(document.getElementById('total_paid').value)     || 0;
    const due      = Math.max(0, purchase - paid);

    const el = document.getElementById('due-display');
    el.textContent = '৳' + due.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    if (due > 0) {
        el.className = 'w-full px-3.5 py-2.5 text-sm font-semibold rounded-lg border select-none transition bg-red-50 border-red-200 text-red-700';
    } else {
        el.className = 'w-full px-3.5 py-2.5 text-sm font-semibold rounded-lg border select-none transition bg-green-50 border-green-200 text-green-700';
    }
}
calcDue();
</script>
@endpush