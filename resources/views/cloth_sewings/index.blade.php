<x-app-layout>
    <x-slot name="header">Cloth Sewing</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('cloth-sewings.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search tailor or product..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>

            @canany(['manage cloth sewings', 'create cloth sewings'])
                <a href="{{ route('cloth-sewings.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">
                    Add Sewing
                </a>
            @endcanany
        </div>

        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div id="cloth-alert" class="hidden px-4 py-3 text-sm rounded-xl"></div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Last Sewing</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Tailor</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Products</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Sewing Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Received Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Balance</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tailors as $tailor)
                            @php
                                $sewingByProduct = $tailor->clothSewings->groupBy('product_id');
                                $receivedByProduct = $tailor->receivedCloths->groupBy('product_id')->map(fn ($items) => $items->sum(fn ($item) => (float) $item->item_qty));
                                $productRows = $sewingByProduct->map(function ($items, $productId) use ($receivedByProduct) {
                                    $first = $items->first();
                                    $sewingQty = $items->sum(fn ($item) => (float) $item->item_qty);
                                    $receivedQty = (float) ($receivedByProduct[$productId] ?? 0);

                                    return [
                                        'name' => $first->product?->product_name ?? '-',
                                        'design_code' => $first->product?->sku ?? $first->product?->product_code ?? '-',
                                        'sewing_qty' => $sewingQty,
                                        'received_qty' => $receivedQty,
                                        'balance_qty' => $sewingQty - $receivedQty,
                                    ];
                                })->values();
                                $totalSewing = (float) ($tailor->total_sewing_qty ?? 0);
                                $totalReceived = (float) ($tailor->total_received_qty ?? 0);
                            @endphp
                            <tr id="tailor-row-{{ $tailor->id }}">
                                <td class="px-5 py-3 whitespace-nowrap" data-field="latest-date">{{ $tailor->latest_sewing_date ? \Carbon\Carbon::parse($tailor->latest_sewing_date)->format('d M Y') : '-' }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800" data-field="tailor-name">{{ $tailor->name }}</td>
                                <td class="px-5 py-3 text-gray-700" data-field="products">
                                    <div class="space-y-1.5">
                                        @foreach($productRows as $index => $product)
                                            <div class="{{ $index >= 3 ? 'hidden extra-product' : '' }} flex items-center justify-between gap-3 px-2 py-1 rounded-lg bg-gray-50 text-xs text-gray-700 border border-gray-100">
                                                <span>{{ $product['name'] }}</span>
                                                <span class="shrink-0">
                                                    <span class="font-mono text-gray-400">{{ $product['design_code'] }}</span>
                                                    <span class="text-indigo-600 ml-2">{{ number_format($product['balance_qty'], 2) }} left</span>
                                                </span>
                                            </div>
                                        @endforeach
                                        @if($productRows->count() > 3)
                                            <button type="button" class="toggle-products text-xs font-medium text-blue-600 hover:underline">
                                                See more
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-right text-indigo-600" data-field="sewing-qty">{{ number_format($totalSewing, 2) }}</td>
                                <td class="px-5 py-3 text-right text-green-600" data-field="received-qty">{{ number_format($totalReceived, 2) }}</td>
                                <td class="px-5 py-3 text-right text-red-600" data-field="balance-qty">{{ number_format($totalSewing - $totalReceived, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <button type="button"
                                            class="open-logs inline-flex items-center justify-center w-8 h-8 text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100"
                                            title="View logs"
                                            data-tailor-name="{{ $tailor->name }}"
                                            data-url="{{ route('cloth-sewings.tailors.logs', $tailor) }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </button>
                                        @canany(['manage cloth sewings', 'edit cloth sewings'])
                                            <button type="button"
                                                class="open-receive px-3 py-1.5 text-xs text-white bg-green-500 rounded-lg hover:bg-green-700"
                                                data-tailor-id="{{ $tailor->id }}"
                                                data-tailor-name="{{ $tailor->name }}"
                                                data-url="{{ route('cloth-sewings.tailors.receive', $tailor) }}"
                                                data-save-url="{{ route('cloth-sewings.tailors.receive.save', $tailor) }}">
                                                Received
                                            </button>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No cloth sewing records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tailors->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $tailors->links() }}</div>
            @endif
        </div>
    </div>

    <div id="receive-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Received Cloth</h3>
                    <p id="receive-tailor" class="text-xs text-gray-400 mt-0.5"></p>
                </div>
                <button type="button" class="close-modal text-gray-400 hover:text-gray-600">X</button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[65vh]">
                <div id="receive-error" class="hidden mb-3 px-3 py-2 text-xs text-red-700 bg-red-50 rounded-lg"></div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Product</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-400">Sewing</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-400">Received</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-400">Balance</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-400">Set Received</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody id="receive-body" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
            <div class="flex justify-end gap-2 px-5 py-4 border-t bg-gray-50">
                <button type="button" class="close-modal px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-lg">Cancel</button>
                <button type="button" id="save-receive" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Save</button>
            </div>
        </div>
    </div>

    <div id="logs-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-5xl max-h-[90vh] overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Cloth Sewing Logs</h3>
                    <p id="logs-tailor" class="text-xs text-gray-400 mt-0.5"></p>
                </div>
                <div class="flex items-center gap-2">
                    <a id="logs-pdf" href="#" class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">PDF</a>
                    <button type="button" class="close-modal text-gray-400 hover:text-gray-600">X</button>
                </div>
            </div>
            <div class="p-5 overflow-y-auto max-h-[72vh]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Type</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Product</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Design Code</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-400">Qty</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody id="logs-body" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const receiveModal = document.getElementById('receive-modal');
        const logsModal = document.getElementById('logs-modal');
        let activeReceive = null;

        function formatQty(value) {
            return (Number(value) || 0).toFixed(2);
        }

        function formatInputQty(value) {
            const number = Number(value) || 0;
            return Number.isInteger(number) ? String(number) : String(Number(number.toFixed(2)));
        }

        function showModal(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideModals() {
            [receiveModal, logsModal].forEach(modal => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }

        function showAlert(message, type = 'success') {
            const alert = document.getElementById('cloth-alert');
            alert.textContent = message;
            alert.className = `px-4 py-3 text-sm rounded-xl ${type === 'success' ? 'text-green-700 bg-green-50 border border-green-200' : 'text-red-700 bg-red-50 border border-red-200'}`;
            setTimeout(() => alert.classList.add('hidden'), 3500);
        }

        function receiveRowHtml(item) {
            return `
                <tr data-product-id="${item.product_id}" data-sewing="${item.sewing_qty}">
                    <td class="px-3 py-2">
                        <div class="font-medium text-gray-800">${escapeHtml(item.product_name)}</div>
                        <div class="text-xs text-gray-400 font-mono">${escapeHtml(item.design_code || '-')}</div>
                    </td>
                    <td class="px-3 py-2 text-right text-indigo-600">${formatQty(item.sewing_qty)}</td>
                    <td class="px-3 py-2 text-right text-green-600">${formatQty(item.received_qty)}</td>
                    <td class="px-3 py-2 text-right text-red-600 balance-cell">${formatQty(item.balance_qty)}</td>
                    <td class="px-3 py-2">
                        <div class="flex justify-end items-center gap-1">
                            <button type="button" class="qty-minus w-8 h-8 rounded bg-gray-100 text-gray-700">-</button>
                            <input type="number" min="0" max="${item.sewing_qty}" step="1" value="${formatInputQty(item.received_qty)}" class="receive-input w-24 h-8 px-2 text-right border border-gray-200 rounded-lg text-sm">
                            <button type="button" class="qty-plus w-8 h-8 rounded bg-gray-100 text-gray-700">+</button>
                        </div>
                    </td>
                    <td class="px-3 py-2 text-right">
                        <button type="button" class="clear-received px-2.5 py-1 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                    </td>
                </tr>
            `;
        }

        function updateReceiveBalance(row) {
            const sewing = Number(row.dataset.sewing) || 0;
            const input = row.querySelector('.receive-input');
            let value = Math.max(0, Math.min(sewing, Number(input.value) || 0));
            input.value = formatInputQty(value);
            row.querySelector('.balance-cell').textContent = formatQty(sewing - value);
        }

        function updateIndexRow(row) {
            const tr = document.getElementById(`tailor-row-${row.tailor_id}`);
            if (!tr) return;

            tr.querySelector('[data-field="latest-date"]').textContent = row.latest_date || '-';
            tr.querySelector('[data-field="sewing-qty"]').textContent = row.total_sewing_qty;
            tr.querySelector('[data-field="received-qty"]').textContent = row.total_received_qty;
            tr.querySelector('[data-field="balance-qty"]').textContent = row.balance_qty;
            tr.querySelector('[data-field="products"]').innerHTML = productListHtml(row.products);
        }

        function productListHtml(products) {
            return `
                <div class="space-y-1.5">
                    ${products.map((product, index) => `
                        <div class="${index >= 3 ? 'hidden extra-product' : ''} flex items-center justify-between gap-3 px-2 py-1 rounded-lg bg-gray-50 text-xs text-gray-700 border border-gray-100">
                            <span>${escapeHtml(product.name)}</span>
                            <span class="shrink-0">
                                <span class="font-mono text-gray-400">${escapeHtml(product.design_code || '-')}</span>
                                <span class="text-indigo-600 ml-2">${product.balance_qty} left</span>
                            </span>
                        </div>
                    `).join('')}
                    ${products.length > 3 ? '<button type="button" class="toggle-products text-xs font-medium text-blue-600 hover:underline">See more</button>' : ''}
                </div>
            `;
        }

        document.querySelectorAll('.open-receive').forEach(button => {
            button.addEventListener('click', async () => {
                activeReceive = {
                    tailorId: button.dataset.tailorId,
                    saveUrl: button.dataset.saveUrl,
                };
                document.getElementById('receive-tailor').textContent = button.dataset.tailorName;
                document.getElementById('receive-error').classList.add('hidden');
                document.getElementById('receive-body').innerHTML = '<tr><td colspan="6" class="px-3 py-8 text-center text-gray-400">Loading...</td></tr>';
                showModal(receiveModal);

                const response = await fetch(button.dataset.url, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                document.getElementById('receive-body').innerHTML = data.items.map(receiveRowHtml).join('');
            });
        });

        document.getElementById('receive-body').addEventListener('click', event => {
            const row = event.target.closest('tr');
            if (!row) return;

            const input = row.querySelector('.receive-input');
            if (event.target.classList.contains('qty-minus')) input.value = formatInputQty((Number(input.value) || 0) - 1);
            if (event.target.classList.contains('qty-plus')) input.value = formatInputQty((Number(input.value) || 0) + 1);
            if (event.target.classList.contains('clear-received')) input.value = '0';
            updateReceiveBalance(row);
        });

        document.getElementById('receive-body').addEventListener('input', event => {
            if (event.target.classList.contains('receive-input')) {
                updateReceiveBalance(event.target.closest('tr'));
            }
        });

        document.getElementById('save-receive').addEventListener('click', async () => {
            if (!activeReceive) return;

            const rows = [...document.querySelectorAll('#receive-body tr[data-product-id]')];
            const payload = {
                items: rows.map(row => ({
                    product_id: row.dataset.productId,
                    received_qty: row.querySelector('.receive-input').value,
                })),
            };

            const response = await fetch(activeReceive.saveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                const error = document.getElementById('receive-error');
                error.textContent = data.message || Object.values(data.errors || {})[0]?.[0] || 'Could not save received cloth.';
                error.classList.remove('hidden');
                return;
            }

            updateIndexRow(data.row);
            document.getElementById('receive-body').innerHTML = data.items.map(receiveRowHtml).join('');
            hideModals();
            showAlert(data.message || 'Received cloth updated successfully.');
        });

        document.querySelectorAll('.open-logs').forEach(button => {
            button.addEventListener('click', async () => {
                document.getElementById('logs-tailor').textContent = button.dataset.tailorName;
                document.getElementById('logs-body').innerHTML = '<tr><td colspan="6" class="px-3 py-8 text-center text-gray-400">Loading...</td></tr>';
                showModal(logsModal);

                const response = await fetch(button.dataset.url, { headers: { Accept: 'application/json' } });
                const data = await response.json();
                document.getElementById('logs-pdf').href = data.pdf_url;
                document.getElementById('logs-body').innerHTML = data.logs.length
                    ? data.logs.map(log => `
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">${escapeHtml(log.date || '-')}</td>
                            <td class="px-3 py-2">${escapeHtml(log.type)}</td>
                            <td class="px-3 py-2">${escapeHtml(log.product)}</td>
                            <td class="px-3 py-2 font-mono text-xs text-gray-500">${escapeHtml(log.design_code || '-')}</td>
                            <td class="px-3 py-2 text-right ${Number(log.qty) < 0 ? 'text-red-600' : 'text-gray-700'}">${escapeHtml(log.qty)}</td>
                            <td class="px-3 py-2 text-gray-500">${escapeHtml(log.note || '-')}</td>
                        </tr>
                    `).join('')
                    : '<tr><td colspan="6" class="px-3 py-8 text-center text-gray-400">No logs found.</td></tr>';
            });
        });

        document.querySelectorAll('.close-modal').forEach(button => button.addEventListener('click', hideModals));
        [receiveModal, logsModal].forEach(modal => modal.addEventListener('click', event => {
            if (event.target === modal) hideModals();
        }));

        document.addEventListener('click', event => {
            if (!event.target.classList.contains('toggle-products')) return;

            const wrap = event.target.closest('[data-field="products"]');
            const isExpanded = event.target.dataset.expanded === 'true';
            wrap.querySelectorAll('.extra-product').forEach(item => item.classList.toggle('hidden', isExpanded));
            event.target.dataset.expanded = isExpanded ? 'false' : 'true';
            event.target.textContent = isExpanded ? 'See more' : 'See less';
        });

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));
        }
    </script>
</x-app-layout>
