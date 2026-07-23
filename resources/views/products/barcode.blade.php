<x-app-layout>
    <x-slot name="header">Barcode Stickers</x-slot>

    <style>
        .barcode-controls {
            display: block;
        }

        .barcode-actions {
            position: sticky;
            top: 12px;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }

        .barcode-action-copy {
            min-width: 0;
        }

        .barcode-action-title {
            color: #1f2937;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.2;
        }

        .barcode-action-subtitle {
            color: #6b7280;
            font-size: 12px;
            margin-top: 4px;
        }

        .barcode-print-button {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            height: 42px;
            padding: 0 18px;
            border: 0;
            border-radius: 8px;
            background: #059669;
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .barcode-print-button:hover {
            background: #047857;
        }

        .barcode-generate-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            padding: 0 16px;
            border: 0;
            border-radius: 8px;
            background: #1f2937;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .barcode-form-actions {
            display: flex;
            gap: 8px;
        }

        .label-sheet {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }

        .sticker-page {
            display: grid;
            grid-template-columns: repeat(5, 39mm);
            gap: 1mm;
            align-content: start;
        }

        /* ---- Sticker card ---- */
        .sticker {
            width: 39mm;
            height: 22mm;
            box-sizing: border-box;
            overflow: hidden;
            border: .15mm solid #000;
            background: #fff;
            padding: .55mm 1.1mm;
            color: #000;
            break-inside: avoid;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
        }

        .sticker-brand {
            flex: 0 0 auto;
            font: 700 5.5pt/1 Arial, sans-serif;
            letter-spacing: .3mm;
            text-transform: uppercase;
            text-align: center;
            color: #111;
            padding-bottom: .3mm;
            margin-bottom: .35mm;
            border-bottom: .15mm solid #000;
        }

        .sticker-product {
            flex: 0 0 auto;
            font: 700 7.5pt/1.15 Arial, sans-serif;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: .3mm;
        }

        .sticker-meta {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.5mm;
            font: 400 6.5pt/1.1 Arial, sans-serif;
            margin-bottom: 1px;
            padding-left: 10px;
        }

        .meta-label {
            flex: 0 0 auto;
            color: #333;
            font-weight: 700;
            font-size: 6.5pt;
            white-space: nowrap;
        }

        .meta-value {
            flex: 1 1 auto;
            min-width: 0;
            text-align: center;
            font-weight: 700;
            font-size: 6.5pt;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-design {
            flex: 1 1 auto;
            min-width: 0;
            text-align: left;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #333;
        }

        .meta-price {
            flex: 0 0 auto;
            font-weight: 700;
            font-size: 7.5pt;
            white-space: nowrap;
        }

       .sticker-brand,
.sticker-product,
.sticker-meta {
    font-family: Arial, Helvetica, "Liberation Sans", sans-serif;
}

.barcode-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    flex: 1 1 auto;
    min-height: 8.5mm; /* reserve space so text rows can never crowd it out */
    padding-top: 2px;
    padding-bottom: 2px;
}

        .barcode-svg {
            display: block;
            width: 35mm;
            height: 7.5mm;
            overflow: visible;
        }

        .barcode-text {
            flex: 0 0 auto;
            margin-top: .2mm;
            font: 6pt/1 monospace;
            letter-spacing: .3mm;
            white-space: nowrap;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm;
            }

            html,
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body * {
                visibility: hidden;
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                overflow: visible !important;
            }

            .barcode-controls,
            .barcode-actions,
            nav,
            header {
                display: none !important;
            }

            .label-sheet {
                display: block;
            }

            .sticker-page {
                display: grid;
                grid-template-columns: repeat(5, 39mm);
                gap: 1mm;
                break-after: page;
                page-break-after: always;
            }

            .sticker-page:last-child {
                break-after: auto;
                page-break-after: auto;
            }
        }
    </style>

    <div class="space-y-4">
        <nav class="barcode-controls flex items-center gap-2 text-xs text-gray-400">
            <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition">Products</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6" />
            </svg>
            <span class="text-gray-600">{{ $product->product_name }}</span>
        </nav>

        <div class="barcode-controls bg-white border border-gray-200 rounded-xl p-5">
            <div class="barcode-actions mb-4">
                <div class="barcode-action-copy">
                    <h2 class="barcode-action-title">Barcode Sticker Print</h2>
                    <p class="barcode-action-subtitle">Set quantity, generate stickers, then print the full sheet.</p>
                </div>
                <button id="printTopBtn" type="button" class="barcode-print-button">
                    Print Stickers
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Product</label>
                    <div class="h-10 px-3 flex items-center text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        {{ $product->product_name }}
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                    <input id="labelQty" type="number" min="1" max="300" value="1"
                        class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                </div>
                <div class="barcode-form-actions">
                    <button id="generateBtn" type="button" class="barcode-generate-button flex-1">
                        Generate
                    </button>
                    <button id="printBtn" type="button" class="barcode-print-button flex-1">
                        Print
                    </button>
                </div>
            </div>
        </div>

        <div id="printArea" class="bg-white border border-gray-200 rounded-xl p-4 overflow-auto">
            <div id="labelSheet" class="label-sheet"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            const product = {
                design: @json($product->sku),
                code: @json($product->product_code ?: $product->sku),
                name: @json($product->product_name),
                price: @json((float) $product->selling_price),
            };

            const patterns = [
                '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
                '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
                '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
                '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
                '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
                '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
                '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
                '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
                '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
                '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
                '114131', '311141', '411131', '211412', '211214', '211232', '2331112'
            ];

            const qtyInput = document.getElementById('labelQty');
            const labelSheet = document.getElementById('labelSheet');
            const labelsPerPage = 65;

            function renderLabels() {
                const qty = Math.max(1, Math.min(300, parseInt(qtyInput.value, 10) || 1));
                qtyInput.value = qty;
                labelSheet.innerHTML = '';
                let stickerPage = null;

                for (let i = 0; i < qty; i++) {
                    if (i % labelsPerPage === 0) {
                        labelSheet.insertAdjacentHTML('beforeend', '<div class="sticker-page"></div>');
                        stickerPage = labelSheet.lastElementChild;
                    }

                    stickerPage.insertAdjacentHTML('beforeend', labelHtml());
                }

                labelSheet.querySelectorAll('.barcode-svg').forEach(svg => {
                    drawCode128(svg, product.code || product.design || '0000');
                });
            }

            function formatPrice(value) {
                const num = Number(value) || 0;
                return num % 1 === 0 ? num.toFixed(0) : num.toFixed(2);
            }

            function labelHtml() {
                return `
        <section class="sticker">
            <div class="sticker-brand">Inaya Creation</div>
            <div class="sticker-product">${escapeHtml(product.name || '-')}</div>
            <div class="sticker-meta">
                <span class="meta-label">Design Code:</span>
                <span class="meta-value">${escapeHtml(product.design || '-')}</span>
            </div>
            <div class="barcode-wrap">
                <svg class="barcode-svg" role="img" aria-label="Barcode"></svg>
                <div class="barcode-text">${escapeHtml(product.code || product.design || '0000')}</div>
            </div>
        </section>
    `;
            }

            function code128Values(text) {
                const values = [104];
                for (const char of String(text)) {
                    const code = char.charCodeAt(0);
                    values.push(code >= 32 && code <= 127 ? code - 32 : 0);
                }

                let checksum = values[0];
                for (let i = 1; i < values.length; i++) {
                    checksum += values[i] * i;
                }
                values.push(checksum % 103, 106);

                return values;
            }

            function drawCode128(svg, text) {
                const values = code128Values(text);
                const quiet = 10;
                const height = 54;
                let x = quiet;
                let bars = '';

                values.forEach(value => {
                    const pattern = patterns[value];
                    [...pattern].forEach((width, index) => {
                        const w = Number(width);
                        if (index % 2 === 0) {
                            bars += `<rect x="${x}" y="0" width="${w}" height="${height}" fill="#050505"/>`;
                        }
                        x += w;
                    });
                });

                const totalWidth = x + quiet;
                svg.setAttribute('viewBox', `0 0 ${totalWidth} ${height}`);
                svg.setAttribute('preserveAspectRatio', 'none');
                svg.innerHTML = `<rect width="${totalWidth}" height="${height}" fill="#fff"/>${bars}`;
            }

            function escapeHtml(value) {
                return String(value).replace(/[&<>"']/g, char => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [char]));
            }

            function printStickers() {
                renderLabels();
                setTimeout(() => window.print(), 100);
            }

            document.getElementById('generateBtn').addEventListener('click', renderLabels);
            document.getElementById('printBtn').addEventListener('click', printStickers);
            document.getElementById('printTopBtn').addEventListener('click', printStickers);

            renderLabels();
        </script>
    @endpush
</x-app-layout>
