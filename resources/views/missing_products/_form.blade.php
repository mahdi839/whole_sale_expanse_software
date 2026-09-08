@php
    $record = $missingProduct ?? null;
    $productOptions = $products->mapWithKeys(fn ($product) => [
        $product->id => [
            'name' => $product->product_name,
            'design_code' => $product->sku ?: $product->product_code,
        ],
    ]);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
        <select name="product_id" id="missing-product-select" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select product</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected((string) old('product_id', $record?->product_id) === (string) $product->id)>
                    {{ $product->displayLabel() }}
                </option>
            @endforeach
        </select>
        @error('product_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Design Code</label>
        <input type="text" id="missing-design-code" readonly
            class="w-full h-10 px-3 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-700">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Missing Qty</label>
        <input type="number" step="0.01" min="0.01" name="missing_qty" value="{{ old('missing_qty', $record?->missing_qty) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('missing_qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($record?->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Document</label>
        <input type="file" name="document" class="w-full h-10 px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @if($record?->document)
            <a href="{{ asset('storage/'.$record->document) }}" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Current document</a>
        @endif
        @error('document')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
        <textarea name="note" rows="3" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note', $record?->note) }}</textarea>
        @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

@push('scripts')
<script>
    window.missingProductOptions = @json($productOptions);
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('missing-product-select');
        const design = document.getElementById('missing-design-code');
        const update = () => {
            const product = window.missingProductOptions[select?.value];
            if (design) {
                design.value = product?.design_code || '';
            }
        };

        select?.addEventListener('change', update);
        setTimeout(update, 0);
    });
</script>
@endpush
