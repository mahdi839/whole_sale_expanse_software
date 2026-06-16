@php
    $record = $workLog ?? $computerManWorkLog ?? null;
    $productOptions = $products->mapWithKeys(fn ($product) => [
        $product->id => [
            'name' => $product->product_name,
            'design_code' => $product->sku ?: $product->product_code,
        ],
    ]);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <div class="flex items-center justify-between gap-3 mb-1">
            <label class="block text-sm font-medium text-gray-700">Computer Man</label>
            <a href="{{ route('computer-men.create') }}" class="text-xs text-blue-600 hover:underline">Add Computer Man</a>
        </div>
        <select name="computer_man_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select computer man</option>
            @foreach($computerMen as $computerMan)
                <option value="{{ $computerMan->id }}" @selected((string) old('computer_man_id', $record?->computer_man_id) === (string) $computerMan->id)>
                    {{ $computerMan->name }}{{ $computerMan->phone ? ' - '.$computerMan->phone : '' }}
                </option>
            @endforeach
        </select>
        @error('computer_man_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', optional($record?->date)->format('Y-m-d') ?? now()->toDateString()) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Memo No</label>
        <input type="text" name="memo_no" value="{{ old('memo_no', $record?->memo_no) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('memo_no')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
        <select name="product_id" id="computer-product-select" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
            <option value="">Select product</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected((string) old('product_id', $record?->product_id) === (string) $product->id)>
                    {{ $product->product_name }}{{ ($product->sku ?: $product->product_code) ? ' - '.($product->sku ?: $product->product_code) : '' }}
                </option>
            @endforeach
        </select>
        @error('product_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Design Code</label>
        <input type="text" id="computer-design-code" readonly
            class="w-full h-10 px-3 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-700">
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4" data-total-calculator data-qty-field="computer_design_qty" data-rate-field="rate_per_piece">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Computer Design Qty</label>
        <input type="number" step="0.01" min="0" name="computer_design_qty" value="{{ old('computer_design_qty', $record?->computer_design_qty ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('computer_design_qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Received Qty</label>
        <input type="number" step="0.01" min="0" name="received_qty" value="{{ old('received_qty', $record?->received_qty ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('received_qty')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Rate Per Piece</label>
        <input type="number" step="0.01" min="0" name="rate_per_piece" value="{{ old('rate_per_piece', $record?->rate_per_piece ?? 0) }}"
            class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
        @error('rate_per_piece')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Total Rate</label>
        <input type="text" data-total-output value="{{ number_format((float) old('total_rate', $record?->total_rate ?? 0), 2, '.', '') }}" readonly
            class="w-full h-10 px-3 text-sm bg-gray-100 border border-gray-200 rounded-lg text-gray-700">
    </div>
</div>

@push('scripts')
    @include('shared._work_log_total_script')
    <script>
        window.computerWorkLogProducts = @json($productOptions);
        (() => {
            const select = document.getElementById('computer-product-select');
            const design = document.getElementById('computer-design-code');
            const update = () => {
                const product = window.computerWorkLogProducts[select.value];
                design.value = product?.design_code || '';
            };

            select?.addEventListener('change', update);
            setTimeout(update, 0);
        })();
    </script>
@endpush
