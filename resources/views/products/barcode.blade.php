<x-app-layout>
    <x-slot name="header">Barcode Stickers</x-slot>

    <style>
        .barcode-controls { display: block; }
        .label-sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 10px;
            align-items: start;
        }
        .sticker {
            width: 280px;
            min-height: 178px;
            border: 2px solid #b98725;
            border-radius: 12px;
            background: #fffdf8;
            padding: 8px;
            color: #050505;
            break-inside: avoid;
            page-break-inside: avoid;
            position: relative;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }
        .sticker::before {
            content: "";
            position: absolute;
            inset: 5px;
            border: 1px solid #c99b3c;
            border-radius: 8px;
            pointer-events: none;
        }
        .sticker-head {
            display: grid;
            grid-template-columns: 48px 1fr;
            gap: 8px;
            align-items: center;
            margin-bottom: 7px;
            position: relative;
            z-index: 1;
        }
        .brand-logo {
            width: 46px;
            height: 46px;
            object-fit: cover;
            border-radius: 999px;
            border: 2px solid #b98725;
            background: #050505;
        }
        .brand-title {
            font-family: Georgia, serif;
            font-size: 22px;
            line-height: 1;
            font-weight: 700;
            white-space: nowrap;
        }
        .brand-subtitle {
            margin-top: 5px;
            color: #b98725;
            font: 700 8px Arial, sans-serif;
            letter-spacing: 2px;
        }
        .info-table {
            display: grid;
            grid-template-columns: 98px 1fr;
            border: 1px solid #c99b3c;
            border-radius: 7px;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .info-label,
        .info-value {
            min-height: 27px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #c99b3c;
        }
        .info-label:nth-last-child(2),
        .info-value:last-child { border-bottom: 0; }
        .info-label {
            background: #050505;
            color: #d5a642;
            padding: 4px 7px;
            font: 700 8px Arial, sans-serif;
            letter-spacing: 1px;
        }
        .info-value {
            padding: 4px 10px;
            font: 800 12px Arial, sans-serif;
            overflow-wrap: anywhere;
        }
        .info-value.product-name {
            font-size: clamp(12px, 2vw, 12px);
            line-height: 1.05;
        }
        .cut-line {
            border-top: 1px dashed #111;
            margin: 7px 4px 5px;
            position: relative;
            z-index: 1;
        }
        .barcode-wrap {
            text-align: center;
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .barcode-svg {
            display: block;
            width: 190px;
            height: 34px;
            margin: 0 auto;
        }
        .barcode-text {
            margin-top: 1px;
            font: 10px monospace;
            letter-spacing: 3px;
        }
        .thanks {
            margin-top: 2px;
            color: #111;
            font: 700 7px Arial, sans-serif;
            letter-spacing: 1.5px;
        }
        @media print {
            @page { size: A4; margin: 8mm; }
            body { background: #fff !important; }
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .barcode-controls,
            nav,
            header { display: none !important; }
            .label-sheet {
                grid-template-columns: repeat(3, 1fr);
                gap: 4mm;
            }
            .sticker {
                width: 60mm;
                min-height: 38mm;
                box-shadow: none;
            }
            .brand-title { font-size: 17px; }
            .brand-subtitle { font-size: 6px; letter-spacing: 1.5px; }
            .brand-logo { width: 34px; height: 34px; }
            .sticker-head { grid-template-columns: 36px 1fr; gap: 6px; }
            .info-table { grid-template-columns: 75px 1fr; }
            .info-label { font-size: 6px; padding: 3px 5px; }
            .info-value { font-size: 13px; min-height: 21px; padding: 3px 7px; }
            .info-value.product-name { font-size: 11px; }
            .barcode-svg { width: 155px; height: 28px; }
            .barcode-text { font-size: 8px; letter-spacing: 2px; }
            .thanks { font-size: 5.5px; }
        }
    </style>

    <div class="space-y-4">
        <nav class="barcode-controls flex items-center gap-2 text-xs text-gray-400">
            <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition">Products</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-gray-600">{{ $product->product_name }}</span>
        </nav>

        <div class="barcode-controls bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-4 border-b border-gray-100">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Barcode Sticker Print</h2>
                    <p class="text-xs text-gray-500 mt-1">Set quantity, generate stickers, then print the full sheet.</p>
                </div>
                <button id="printTopBtn" type="button"
                    class="inline-flex items-center justify-center h-10 px-5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700">
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
                <div class="flex gap-2">
                    <button id="generateBtn" type="button" class="flex-1 h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">
                        Generate
                    </button>
                    <button id="printBtn" type="button" class="flex-1 h-10 px-4 bg-emerald-600 text-white rounded-lg text-sm">
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
        logo: @json(asset('inaya_creation_logo.jpeg')),
    };

    const patterns = [
        '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
        '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
        '221231','213212','223112','312131','311222','321122','321221','312212','322112','322211',
        '212123','212321','232121','111323','131123','131321','112313','132113','132311','211313',
        '231113','231311','112133','112331','132131','113123','113321','133121','313121','211331',
        '231131','213113','213311','213131','311123','311321','331121','312113','312311','332111',
        '314111','221411','431111','111224','111422','121124','121421','141122','141221','112214',
        '112412','122114','122411','142112','142211','241211','221114','413111','241112','134111',
        '111242','121142','121241','114212','124112','124211','411212','421112','421211','212141',
        '214121','412121','111143','111341','131141','114113','114311','411113','411311','113141',
        '114131','311141','411131','211412','211214','211232','2331112'
    ];

    const qtyInput = document.getElementById('labelQty');
    const labelSheet = document.getElementById('labelSheet');

    function renderLabels() {
        const qty = Math.max(1, Math.min(300, parseInt(qtyInput.value, 10) || 1));
        qtyInput.value = qty;
        labelSheet.innerHTML = '';

        for (let i = 0; i < qty; i++) {
            labelSheet.insertAdjacentHTML('beforeend', labelHtml());
        }

        document.querySelectorAll('.barcode-svg').forEach(svg => {
            drawCode128(svg, product.code || product.design || '0000');
        });
    }

    function labelHtml() {
        return `
            <section class="sticker">
                <div class="sticker-head">
                    <img class="brand-logo" src="${escapeAttr(product.logo)}" alt="Inaya creation logo">
                    <div>
                        <div class="brand-title">Inaya creation</div>
                        <div class="brand-subtitle">STYLE WITH ELEGANCE</div>
                    </div>
                </div>
                <div class="info-table">
                    <div class="info-label">DESIGN CODE.</div>
                    <div class="info-value">${escapeHtml(product.design || '-')}</div>
                    <div class="info-label">PRODUCT NAME</div>
                    <div class="info-value product-name">${escapeHtml(product.name || '-')}</div>
                    <div class="info-label">PRICE</div>
                    <div class="info-value">৳ ${Number(product.price || 0).toLocaleString('en-US')}</div>
                </div>
                <div class="cut-line"></div>
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
        svg.innerHTML = `<rect width="${totalWidth}" height="${height}" fill="#fff"/>${bars}`;
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function printStickers() {
        renderLabels();
        window.print();
    }

    document.getElementById('generateBtn').addEventListener('click', renderLabels);
    document.getElementById('printBtn').addEventListener('click', printStickers);
    document.getElementById('printTopBtn').addEventListener('click', printStickers);

    renderLabels();
    </script>
    @endpush
</x-app-layout>
