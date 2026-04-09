{{--
    Shared form partial used by both create and edit views.
    Usage:
        @include('products._form', ['product' => $product])  ← edit
        @include('products._form')                            ← create
--}}

@php $product = $product ?? null; @endphp

{{-- Product Name --}}
<div class="space-y-1.5">
    <label for="product_name" class="block text-sm font-medium text-gray-700">
        Product Name <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="product_name"
        name="product_name"
        value="{{ old('product_name', $product?->product_name) }}"
        placeholder="e.g. Wireless Mouse"
        class="w-full px-3.5 py-2.5 text-sm border rounded-lg transition
               @error('product_name') border-red-400 bg-red-50 focus:ring-red-400
               @else border-gray-200 focus:ring-blue-500 @enderror
               focus:outline-none focus:ring-2 focus:border-transparent"
    />
    @error('product_name')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- SKU --}}
<div class="space-y-1.5">
    <label for="sku" class="block text-sm font-medium text-gray-700">
        SKU <span class="text-red-500">*</span>
    </label>
    <input
        type="text"
        id="sku"
        name="sku"
        value="{{ old('sku', $product?->sku) }}"
        placeholder="e.g. WM-001-BLK"
        class="w-full px-3.5 py-2.5 text-sm font-mono border rounded-lg transition
               @error('sku') border-red-400 bg-red-50 focus:ring-red-400
               @else border-gray-200 focus:ring-blue-500 @enderror
               focus:outline-none focus:ring-2 focus:border-transparent"
    />
    @error('sku')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
    <p class="text-xs text-gray-400">Must be unique. Example: CAT-ELECTRONICS-001</p>
</div>

{{-- Image upload --}}
<div class="space-y-1.5">
    <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>

    {{-- Current image preview (edit mode) --}}
    @if($product?->image)
        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
            <img
                src="{{ Storage::url($product->image) }}"
                alt="Current image"
                class="w-14 h-14 object-cover rounded-lg border border-gray-200"
                id="current-preview"
            />
            <div>
                <p class="text-xs font-medium text-gray-600">Current image</p>
                <p class="text-xs text-gray-400">Upload a new one to replace it</p>
            </div>
        </div>
    @endif

    {{-- Drop zone --}}
    <div
        id="drop-zone"
        class="relative border-2 border-dashed border-gray-200 rounded-xl p-6 text-center
               hover:border-blue-400 hover:bg-blue-50/30 transition-colors cursor-pointer"
        onclick="document.getElementById('image').click()"
    >
        <input
            type="file"
            id="image"
            name="image"
            accept="image/jpg,image/jpeg,image/png,image/webp"
            class="sr-only"
            onchange="previewImage(event)"
        />

        {{-- Preview (shown after selection) --}}
        <img id="new-preview" class="hidden mx-auto mb-3 w-24 h-24 object-cover rounded-xl border border-gray-200" />

        <div id="drop-prompt">
            <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm text-gray-500">Click to upload or drag & drop</p>
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP · max 2 MB</p>
        </div>
        <p id="file-name" class="hidden text-xs text-blue-600 mt-2 font-medium"></p>
    </div>

    @error('image')
        <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

@push('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (!file) return;

    const preview = document.getElementById('new-preview');
    const prompt  = document.getElementById('drop-prompt');
    const name    = document.getElementById('file-name');

    const reader = new FileReader();
    reader.onload = (e) => {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        prompt.classList.add('hidden');
        name.textContent = file.name;
        name.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

// Drag & drop support
const zone = document.getElementById('drop-zone');
zone.addEventListener('dragover',  (e) => { e.preventDefault(); zone.classList.add('border-blue-400', 'bg-blue-50/30'); });
zone.addEventListener('dragleave', ()  => { zone.classList.remove('border-blue-400', 'bg-blue-50/30'); });
zone.addEventListener('drop', (e) => {
    e.preventDefault();
    zone.classList.remove('border-blue-400', 'bg-blue-50/30');
    const input = document.getElementById('image');
    input.files = e.dataTransfer.files;
    previewImage({ target: input });
});
</script>
@endpush