<x-app-layout>
    <x-slot name="header">Due Management</x-slot>

    <style>
        .due-page {
            --ink: #0f1117;
            --ink-2: #3a3d47;
            --ink-3: #7a7e8a;
            --ink-4: #b0b4be;
            --surface: #ffffff;
            --surface-2: #f7f8fa;
            --surface-3: #f0f2f5;
            --border: rgba(15,17,23,0.08);
            --border-strong: rgba(15,17,23,0.14);
            --accent: #1a56db;
            --accent-bg: #eff4ff;
            --accent-fg: #1e40af;
            --red: #dc2626;
            --red-bg: #fef2f2;
            --red-fg: #991b1b;
            --amber: #d97706;
            --amber-bg: #fffbeb;
            --amber-fg: #92400e;
            --blue: #2563eb;
            --blue-bg: #eff6ff;
            --blue-fg: #1e3a8a;
            --violet: #7c3aed;
            --violet-bg: #f5f3ff;
            --violet-fg: #4c1d95;
            --green: #16a34a;
            --green-bg: #f0fdf4;
            --green-fg: #14532d;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --shadow-xs: 0 1px 2px rgba(15,17,23,0.05);
            --shadow-sm: 0 1px 3px rgba(15,17,23,0.08), 0 1px 2px rgba(15,17,23,0.04);
            --shadow-md: 0 4px 12px rgba(15,17,23,0.08), 0 1px 3px rgba(15,17,23,0.05);
            font-family: 'DM Sans', 'Figtree', ui-sans-serif, system-ui, sans-serif;
            color: var(--ink);
        }

        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

        /* ── metric cards ── */
        .metric-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        @media (max-width: 1024px) { .metric-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .metric-grid { grid-template-columns: 1fr 1fr; } }

        .metric-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 22px 18px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            transition: box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .metric-card:hover {
            box-shadow: var(--shadow-sm);
            border-color: var(--border-strong);
        }
        .metric-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .metric-card.mc-red::before   { background: var(--red); }
        .metric-card.mc-amber::before { background: var(--amber); }
        .metric-card.mc-blue::before  { background: var(--blue); }
        .metric-card.mc-violet::before{ background: var(--violet); }

        .metric-label {
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--ink-3);
            margin: 0 0 10px;
        }
        .metric-value {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: -0.03em;
            line-height: 1;
            margin: 0;
        }
        .metric-card.mc-red   .metric-value { color: var(--red); }
        .metric-card.mc-amber .metric-value { color: var(--amber); }
        .metric-card.mc-blue  .metric-value { color: var(--blue); }
        .metric-card.mc-violet .metric-value { color: var(--violet); }
        .metric-icon {
            position: absolute;
            bottom: 14px;
            right: 16px;
            font-size: 30px;
            opacity: 0.07;
        }

        /* ── panel / card ── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs);
            overflow: hidden;
        }
        .panel-header {
            padding: 16px 22px 14px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .panel-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.01em;
            margin: 0;
        }

        /* ── form ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr 1fr 3fr 1fr;
            gap: 10px;
            align-items: end;
        }
        @media (max-width: 1280px) {
            .form-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
        }

        .form-label {
            display: block;
            font-size: 11px;
            font-weight: 500;
            color: var(--ink-3);
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            height: 38px;
            padding: 0 12px;
            font-size: 13px;
            font-family: inherit;
            color: var(--ink);
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            outline: none;
            transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }
        .form-control:focus {
            background: var(--surface);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(26,86,219,0.1);
        }
        .form-control::placeholder { color: var(--ink-4); }

        select.form-control {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%237a7e8a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
            cursor: pointer;
        }

        .btn-add-quick {
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            background: var(--accent-bg);
            color: var(--accent-fg);
            border: 1px solid rgba(26,86,219,0.2);
            border-radius: var(--radius-sm);
            font-size: 18px;
            font-weight: 300;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }
        .btn-add-quick:hover { background: #dbe8fd; }

        .btn-primary {
            height: 38px;
            padding: 0 18px;
            background: var(--ink);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            white-space: nowrap;
            letter-spacing: -0.01em;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-primary:hover { background: #1e2230; }
        .btn-primary:active { transform: scale(0.98); }

        /* ── tables ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .data-table thead tr {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
        }
        .data-table thead th {
            padding: 10px 18px;
            text-align: left;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--ink-3);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .data-table thead th.text-right { text-align: right; }
        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.1s;
        }
        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: var(--surface-2); }
        .data-table tbody td {
            padding: 11px 18px;
            color: var(--ink-2);
            vertical-align: middle;
        }
        .data-table tbody td.text-right { text-align: right; }

        .amount-danger { color: var(--red); font-weight: 600; font-variant-numeric: tabular-nums; }
        .amount-warn   { color: var(--amber); font-weight: 600; font-variant-numeric: tabular-nums; }

        .badge-ref {
            display: inline-block;
            padding: 3px 8px;
            background: var(--violet-bg);
            color: var(--violet-fg);
            border-radius: 5px;
            font-family: 'DM Mono', ui-monospace, monospace;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.03em;
        }
        .badge-party {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 500;
        }
        .badge-customer { background: var(--blue-bg); color: var(--blue-fg); }
        .badge-supplier { background: var(--amber-bg); color: var(--amber-fg); }

        /* ── action buttons ── */
        .btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: background 0.12s;
            text-decoration: none;
        }
        .btn-sm-edit {
            background: var(--accent-bg);
            color: var(--accent-fg);
            border-color: rgba(26,86,219,0.15);
        }
        .btn-sm-edit:hover { background: #dbe8fd; }
        .btn-sm-delete {
            background: var(--red-bg);
            color: var(--red-fg);
            border-color: rgba(220,38,38,0.15);
        }
        .btn-sm-delete:hover { background: #fee2e2; }

        /* ── alert ── */
        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            background: var(--green-bg);
            border: 1px solid rgba(22,163,74,0.2);
            border-radius: var(--radius-md);
            font-size: 13.5px;
            color: var(--green-fg);
        }
        .alert-success svg { flex-shrink: 0; }

        /* ── empty state ── */
        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: var(--ink-4);
            font-size: 13px;
        }

        /* ── modal overlay ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(15,17,23,0.45);
            backdrop-filter: blur(3px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-card {
            width: 100%;
            max-width: 420px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        .modal-header {
            padding: 18px 22px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .modal-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.01em;
            margin: 0;
        }
        .modal-close {
            width: 28px;
            height: 28px;
            border: none;
            background: var(--surface-3);
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink-3);
            font-size: 16px;
            transition: background 0.12s;
        }
        .modal-close:hover { background: var(--border); }
        .modal-body { padding: 20px 22px; display: flex; flex-direction: column; gap: 12px; }

        /* ── dual table grid ── */
        .table-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 1024px) { .table-grid { grid-template-columns: 1fr; } }

        /* ── form section ── */
        .form-section { padding: 22px 22px 20px; }
        .form-section-title {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: -0.01em;
            margin: 0 0 16px;
        }

        /* ── error text ── */
        .field-error { font-size: 11px; color: var(--red); margin-top: 4px; }

        /* ── pagination ── */
        .pagination-wrap { padding: 12px 18px; border-top: 1px solid var(--border); background: var(--surface-2); }
    </style>

    <div class="due-page space-y-4" x-data="{ partyType: 'customer', customerOpen: false, supplierOpen: false }"
        @customer-created.window="customerOpen = false"
        @supplier-created.window="supplierOpen = false">

        {{-- Alert --}}
        @if(session('success'))
        <div class="alert-success">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Metric Summary --}}
        <div class="metric-grid">
            <div class="metric-card mc-red">
                <p class="metric-label">Customer Due</p>
                <p class="metric-value">৳{{ number_format($totals['customer_due'], 2) }}</p>
                <span class="metric-icon">👤</span>
            </div>
            <div class="metric-card mc-amber">
                <p class="metric-label">Supplier Due</p>
                <p class="metric-value">৳{{ number_format($totals['supplier_due'], 2) }}</p>
                <span class="metric-icon">🏭</span>
            </div>
            <div class="metric-card mc-blue">
                <p class="metric-label">Sale Wise Due</p>
                <p class="metric-value">৳{{ number_format($totals['sale_due'], 2) }}</p>
                <span class="metric-icon">🧾</span>
            </div>
            <div class="metric-card mc-violet">
                <p class="metric-label">Purchase Wise Due</p>
                <p class="metric-value">৳{{ number_format($totals['purchase_due'], 2) }}</p>
                <span class="metric-icon">📦</span>
            </div>
        </div>

        {{-- Add Manual Due --}}
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Add Manual Due</h2>
            </div>
            <div class="form-section">
                <form method="POST" action="{{ route('dues.store') }}" class="form-grid">
                    @csrf

                    {{-- Party Type --}}
                    <div>
                        <label class="form-label">Party Type</label>
                        <select name="party_type" x-model="partyType" class="form-control">
                            <option value="customer">Customer</option>
                            <option value="supplier">Supplier</option>
                        </select>
                    </div>

                    {{-- Customer Select --}}
                    <div x-show="partyType === 'customer'">
                        <label class="form-label">Customer</label>
                        <div style="display:flex;gap:8px;">
                            <select name="customer_id" id="customer-select" class="form-control" style="flex:1;">
                                <option value="">Select customer…</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->full_name }}{{ $customer->phone ? ' · '.$customer->phone : '' }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="customerOpen = true" class="btn-add-quick" title="New customer">+</button>
                        </div>
                        @error('customer_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Supplier Select --}}
                    <div x-show="partyType === 'supplier'">
                        <label class="form-label">Supplier</label>
                        <div style="display:flex;gap:8px;">
                            <select name="supplier_id" id="supplier-select" class="form-control" style="flex:1;">
                                <option value="">Select supplier…</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}{{ $supplier->phone ? ' · '.$supplier->phone : '' }}</option>
                                @endforeach
                            </select>
                            <button type="button" @click="supplierOpen = true" class="btn-add-quick" title="New supplier">+</button>
                        </div>
                        @error('supplier_id')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}"
                            placeholder="0.00" class="form-control">
                        @error('amount')<p class="field-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="form-label">Date</label>
                        <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="form-control">
                    </div>

                    {{-- Note --}}
                    <div>
                        <label class="form-label">Note</label>
                        <input type="text" name="note" value="{{ old('note') }}"
                            placeholder="Reason or bill reference…" class="form-control">
                    </div>

                    {{-- Submit --}}
                    <div style="display:flex;align-items:flex-end;">
                        <button type="submit" class="btn-primary" style="width:100%;">Add Due</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Customer & Supplier Wise Dues --}}
        <div class="table-grid">
            <div class="panel">
                <div class="panel-header"><h2 class="panel-title">Customer Wise Due</h2></div>
                @include('dues.partials.customer_table', ['rows' => $customerDues])
            </div>
            <div class="panel">
                <div class="panel-header"><h2 class="panel-title">Supplier Wise Due</h2></div>
                @include('dues.partials.supplier_table', ['rows' => $supplierDues])
            </div>
            <div class="panel">
                <div class="panel-header"><h2 class="panel-title">Sale Wise Due</h2></div>
                @include('dues.partials.sale_table', ['rows' => $saleDues])
            </div>
            <div class="panel">
                <div class="panel-header"><h2 class="panel-title">Purchase Wise Due</h2></div>
                @include('dues.partials.purchase_table', ['rows' => $purchaseDues])
            </div>
        </div>

        {{-- Manual Dues Table --}}
        <div class="panel">
            <div class="panel-header">
                <h2 class="panel-title">Manual Dues</h2>
                <span style="font-size:12px;color:var(--ink-4);font-variant-numeric:tabular-nums;">{{ $manualDues->total() }} records</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Party</th>
                            <th>Type</th>
                            <th class="text-right">Amount</th>
                            <th>Date</th>
                            <th>Note</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($manualDues as $due)
                        <tr>
                            <td><span class="badge-ref">{{ $due->reference }}</span></td>
                            <td style="font-weight:500;color:var(--ink);">
                                {{ $due->party_type === 'customer' ? $due->customer?->full_name : $due->supplier?->name }}
                            </td>
                            <td>
                                <span class="badge-party {{ $due->party_type === 'customer' ? 'badge-customer' : 'badge-supplier' }}">
                                    {{ ucfirst($due->party_type) }}
                                </span>
                            </td>
                            <td class="text-right amount-danger">৳{{ number_format($due->amount, 2) }}</td>
                            <td style="color:var(--ink-3);font-size:12.5px;white-space:nowrap;">{{ optional($due->date)->format('d M Y') }}</td>
                            <td style="color:var(--ink-3);font-size:12.5px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $due->note ?? '—' }}</td>
                            <td class="text-right">
                                <div style="display:flex;gap:6px;justify-content:flex-end;">
                                    <a href="{{ route('dues.edit', $due) }}" class="btn-sm btn-sm-edit">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('dues.destroy', $due) }}" class="inline" onsubmit="return confirm('Delete this manual due?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-sm btn-sm-delete">
                                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="empty-state">No manual dues recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($manualDues->hasPages())
            <div class="pagination-wrap">{{ $manualDues->links() }}</div>
            @endif
        </div>

        {{-- New Customer Modal --}}
        <div x-show="customerOpen" class="modal-overlay" x-transition.opacity>
            <div class="modal-card" @click.outside="customerOpen = false">
                <div class="modal-header">
                    <h3 class="modal-title">New Customer</h3>
                    <button type="button" @click="customerOpen = false" class="modal-close">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="quick-customer-form" class="modal-body">
                    <div>
                        <label class="form-label">Full Name <span style="color:var(--red)">*</span></label>
                        <input name="full_name" required placeholder="e.g. Rahim Uddin" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input name="phone" placeholder="01XXXXXXXXX" class="form-control">
                    </div>
                    <div style="padding-top:4px;">
                        <button type="submit" class="btn-primary" style="width:100%;">Save Customer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- New Supplier Modal --}}
        <div x-show="supplierOpen" class="modal-overlay" x-transition.opacity>
            <div class="modal-card" @click.outside="supplierOpen = false">
                <div class="modal-header">
                    <h3 class="modal-title">New Supplier</h3>
                    <button type="button" @click="supplierOpen = false" class="modal-close">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form id="quick-supplier-form" class="modal-body">
                    <div>
                        <label class="form-label">Supplier Name <span style="color:var(--red)">*</span></label>
                        <input name="name" required placeholder="e.g. ABC Traders" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input name="phone" placeholder="01XXXXXXXXX" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input name="email" type="email" placeholder="supplier@example.com" class="form-control">
                    </div>
                    <div style="padding-top:4px;">
                        <button type="submit" class="btn-primary" style="width:100%;">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    const token = document.querySelector('meta[name="csrf-token"]').content;

    async function postQuick(form, url) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: new FormData(form)
        });
        if (!response.ok) {
            alert('Could not save. Please check required fields.');
            return null;
        }
        return await response.json();
    }

    document.getElementById('quick-customer-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const customer = await postQuick(this, '{{ route('customers.store') }}');
        if (!customer) return;
        const select = document.getElementById('customer-select');
        select.add(new Option(customer.full_name + (customer.phone ? ' · ' + customer.phone : ''), customer.id, true, true));
        window.dispatchEvent(new CustomEvent('customer-created'));
        this.reset();
    });

    document.getElementById('quick-supplier-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const supplier = await postQuick(this, '{{ route('suppliers.store') }}');
        if (!supplier) return;
        const select = document.getElementById('supplier-select');
        select.add(new Option(supplier.name + (supplier.phone ? ' · ' + supplier.phone : ''), supplier.id, true, true));
        window.dispatchEvent(new CustomEvent('supplier-created'));
        this.reset();
    });
    </script>
    @endpush
</x-app-layout>