{{--
    Shared form partial used by both create and edit views.
    Usage:
        @include('products._form', ['product' => $product])  ← edit
        @include('products._form')                            ← create
--}}

@php $product = $product ?? null; @endphp

<style>
    /* ── Premium Form Variables ── */
    .pf-field { display: flex; flex-direction: column; gap: 6px; }

    .pf-label {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: #6b7280;
    }
    .pf-label .req {
        color: #ef4444;
        margin-left: 2px;
        font-size: 13px;
        vertical-align: middle;
        line-height: 1;
    }

    /* Base input */
    .pf-input {
        width: 100%;
        padding: 11px 14px;
        font-size: 14px;
        color: #111827;
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        outline: none;
        box-shadow: 0 1px 2px rgba(0,0,0,.04);
    }
    .pf-input::placeholder { color: #c4c9d4; }
    .pf-input:hover:not(:focus) { border-color: #d1d5db; }
    .pf-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3.5px rgba(59,130,246,.13), 0 1px 2px rgba(0,0,0,.04);
        background: #fafcff;
    }
    .pf-input.pf-error {
        border-color: #fca5a5;
        background: #fff8f8;
    }
    .pf-input.pf-error:focus {
        border-color: #ef4444;
        box-shadow: 0 0 0 3.5px rgba(239,68,68,.11);
    }
    .pf-input.pf-mono { font-family: 'JetBrains Mono', 'Fira Code', 'Menlo', monospace; letter-spacing: 0.03em; }

    /* Hint text */
    .pf-hint {
        font-size: 11.5px;
        color: #9ca3af;
        line-height: 1.4;
    }

    /* Error message */
    .pf-err-msg {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #dc2626;
        font-weight: 500;
    }
    .pf-err-msg svg { flex-shrink: 0; opacity: .8; }

    /* ── Stock stepper ── */
    .pf-stepper {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .pf-step-btn {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid #e5e7eb;
        border-radius: 9px;
        background: #f9fafb;
        color: #374151;
        font-size: 18px;
        font-weight: 400;
        cursor: pointer;
        transition: background 0.14s, border-color 0.14s, transform 0.1s;
        user-select: none;
        line-height: 1;
    }
    .pf-step-btn:hover { background: #f3f4f6; border-color: #d1d5db; }
    .pf-step-btn:active { transform: scale(.93); }
    .pf-step-input {
        width: 88px;
        text-align: center;
        font-size: 15px;
        font-weight: 500;
        color: #111827;
        padding: 8px 10px;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        outline: none;
        transition: border-color 0.18s, box-shadow 0.18s;
        box-shadow: 0 1px 2px rgba(0,0,0,.04);
        -moz-appearance: textfield;
    }
    .pf-step-input::-webkit-inner-spin-button,
    .pf-step-input::-webkit-outer-spin-button { -webkit-appearance: none; }
    .pf-step-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3.5px rgba(59,130,246,.13);
    }
    .pf-step-input.pf-error { border-color: #fca5a5; background: #fff8f8; }

    /* ── Image upload zone ── */
    .pf-current-img {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: #f9fafb;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
    }
    .pf-current-img img {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
    }
    .pf-current-img-label { font-size: 12px; font-weight: 600; color: #374151; }
    .pf-current-img-sub   { font-size: 11.5px; color: #9ca3af; margin-top: 1px; }

    .pf-dropzone {
        position: relative;
        border: 1.5px dashed #d1d5db;
        border-radius: 12px;
        padding: 28px 20px;
        text-align: center;
        cursor: pointer;
        background: #fafafa;
        transition: border-color 0.18s, background 0.18s;
    }
    .pf-dropzone:hover,
    .pf-dropzone.pf-dz-active {
        border-color: #3b82f6;
        background: #f0f7ff;
    }
    .pf-dz-icon {
        width: 38px;
        height: 38px;
        margin: 0 auto 10px;
        color: #d1d5db;
        transition: color 0.18s;
    }
    .pf-dropzone:hover .pf-dz-icon,
    .pf-dropzone.pf-dz-active .pf-dz-icon { color: #93c5fd; }
    .pf-dz-title {
        font-size: 13.5px;
        font-weight: 500;
        color: #4b5563;
    }
    .pf-dz-sub {
        font-size: 11.5px;
        color: #9ca3af;
        margin-top: 3px;
    }
    #new-preview {
        display: none;
        margin: 0 auto 10px;
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        border: 1.5px solid #e5e7eb;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    #file-name {
        display: none;
        font-size: 12px;
        color: #3b82f6;
        font-weight: 500;
        margin-top: 6px;
    }
</style>

{{-- Product Name --}}
<div class="pf-field">
    <label for="product_name" class="pf-label">
        Product Name<span class="req">*</span>
    </label>
    <input
        type="text" id="product_name" name="product_name"
        value="{{ old('product_name', $product?->product_name) }}"
        placeholder="e.g. Kashmiri"
        class="pf-input @error('product_name') pf-error @enderror"
    />
    @error('product_name')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Design Code --}}
<div class="pf-field">
    <label for="sku" class="pf-label">
        Design Code<span class="req">*</span>
    </label>
    <input
        type="text" id="sku" name="sku"
        value="{{ old('sku', $product?->sku) }}"
        placeholder="e.g. DESIGN-001"
        class="pf-input pf-mono @error('sku') pf-error @enderror"
    />
    @error('sku')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
    <p class="pf-hint">Must be unique.</p>
</div>

{{-- Product Code --}}
<div class="pf-field">
    <label for="product_code" class="pf-label">Product Code</label>
    <input
        type="text" id="product_code" name="product_code"
        value="{{ old('product_code', $product?->product_code) }}"
        placeholder="e.g. 24434"
        class="pf-input pf-mono @error('product_code') pf-error @enderror"
    />
    @error('product_code')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
    <p class="pf-hint">Enter the middle code. The system stores it with 2 random digits before and after.</p>
</div>

{{-- Purchase Price --}}
@if(auth()->user()->hasRole('Super Admin') || auth()->user()->is_admin)
<div class="pf-field">
    <label for="purchase_price" class="pf-label">
        Purchase Price<span class="req">*</span>
    </label>
    <input
        type="number" id="purchase_price" name="purchase_price"
        value="{{ old('purchase_price', $product?->purchase_price ?? 0) }}"
        step="0.01" min="0"
        placeholder="0.00"
        class="pf-input @error('purchase_price') pf-error @enderror"
    />
    @error('purchase_price')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
    <p class="pf-hint">This price is used for sale profit calculation.</p>
</div>
@endif

{{-- Sell Price --}}
<div class="pf-field">
    <label for="selling_price" class="pf-label">
        Sale Price<span class="req">*</span>
    </label>
    <input
        type="number" id="selling_price" name="selling_price"
        value="{{ old('selling_price', $product?->selling_price ?? 0) }}"
        step="0.01" min="0"
        placeholder="0.00"
        class="pf-input @error('selling_price') pf-error @enderror"
    />
    @error('selling_price')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Stock Quantity --}}
<div class="pf-field">
    <label for="stock_qty" class="pf-label">
        Stock Quantity<span class="req">*</span>
    </label>
    <div class="pf-stepper">
        <button type="button" onclick="adjustStock(-1)" class="pf-step-btn" aria-label="Decrease">−</button>
        <input
            type="number" id="stock_qty" name="stock_qty"
            value="{{ old('stock_qty', $product?->stock?->stock_qty ?? 0) }}"
            min="0"
            class="pf-step-input @error('stock_qty') pf-error @enderror"
        />
        <button type="button" onclick="adjustStock(1)" class="pf-step-btn" aria-label="Increase">+</button>
    </div>
    @error('stock_qty')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>

{{-- Image upload --}}
<div class="pf-field">
    <label class="pf-label">Product Image</label>

    {{-- Current image preview (edit mode) --}}
    @if ($product?->image)
        <div class="pf-current-img">
            <img src="{{ Storage::url($product->image) }}" alt="Current image" id="current-preview" />
            <div>
                <p class="pf-current-img-label">Current image</p>
                <p class="pf-current-img-sub">Upload a new file to replace it</p>
            </div>
        </div>
    @endif

    {{-- Drop zone --}}
    <div id="drop-zone" class="pf-dropzone" onclick="document.getElementById('image').click()">
        <input
            type="file" id="image" name="image"
            accept="image/jpg,image/jpeg,image/png,image/webp"
            class="sr-only"
            onchange="previewImage(event)"
        />

        <img id="new-preview" alt="New preview" />

        <div id="drop-prompt">
            <svg class="pf-dz-icon" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="pf-dz-title">Click to upload or drag &amp; drop</p>
            <p class="pf-dz-sub">JPG, PNG, WEBP &middot; max 2 MB</p>
        </div>

        <p id="file-name"></p>
    </div>
    <p id="image-client-error" class="pf-err-msg hidden">
        <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        Product image must be 2 MB or smaller.
    </p>

    @if (request('upload_error') === 'too_large')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            The uploaded file is too large. Product images must be 2 MB or smaller.
        </p>
    @endif

    @error('image')
        <p class="pf-err-msg">
            <svg width="13" height="13" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>

@push('scripts')
    <script>
        const productImageMaxBytes = 2 * 1024 * 1024;

        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const preview = document.getElementById('new-preview');
            const prompt  = document.getElementById('drop-prompt');
            const name    = document.getElementById('file-name');
            const input   = document.getElementById('image');
            const error   = document.getElementById('image-client-error');

            if (file.size > productImageMaxBytes) {
                input.value = '';
                preview.removeAttribute('src');
                preview.style.display = 'none';
                prompt.classList.remove('hidden');
                name.textContent = '';
                name.style.display = 'none';
                error.classList.remove('hidden');
                return;
            }

            error.classList.add('hidden');

            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                prompt.classList.add('hidden');
                name.textContent = file.name;
                name.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Drag & drop support
        const zone = document.getElementById('drop-zone');
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('pf-dz-active');
        });
        zone.addEventListener('dragleave', () => {
            zone.classList.remove('pf-dz-active');
        });
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('pf-dz-active');
            const input = document.getElementById('image');
            if (!e.dataTransfer.files.length) return;
            input.files = e.dataTransfer.files;
            previewImage({ target: input });
        });

        function adjustStock(delta) {
            const input = document.getElementById('stock_qty');
            const current = parseInt(input.value) || 0;
            input.value = Math.max(0, current + delta);
        }
    </script>
@endpush
