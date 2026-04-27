<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->reference }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f1f5f9;
            padding: 20px;
        }

        .invoice-wrap {
            background: #fff;
            max-width: 760px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
        }

        /* ---- Header ---- */
        .inv-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            color: #fff;
            padding: 28px 32px 24px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        .inv-header .company h1 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -.3px;
        }

        .inv-header .company p {
            font-size: 12px;
            opacity: .75;
            margin-top: 3px;
        }

        .inv-header .inv-meta {
            text-align: right;
        }

        .inv-header .inv-meta .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            opacity: .65;
        }

        .inv-header .inv-meta .ref {
            font-size: 18px;
            font-weight: 700;
            font-family: monospace;
            margin-top: 2px;
        }

        .inv-header .inv-meta .date {
            font-size: 12px;
            opacity: .75;
            margin-top: 4px;
        }

        /* ---- Info strip ---- */
        .inv-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .inv-info-cell {
            padding: 16px 24px;
            border-right: 1px solid #e2e8f0;
        }

        .inv-info-cell:last-child { border-right: none; }

        .inv-info-cell .cell-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            font-weight: 600;
        }

        .inv-info-cell .cell-val {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 3px;
            word-break: break-word;
        }

        .inv-info-cell .cell-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 1px;
        }

        /* ---- Items table ---- */
        .inv-body { padding: 24px 32px; }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }

        table.items thead tr {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        table.items th {
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
            font-weight: 600;
        }

        table.items th.r { text-align: right; }

        table.items td {
            padding: 11px 12px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #334155;
        }

        table.items td.r { text-align: right; }

        table.items tbody tr:last-child td { border-bottom: none; }

        table.items tbody tr:hover { background: #f8fafc; }

        /* ---- Totals ---- */
        .inv-totals {
            border-top: 2px solid #e2e8f0;
            padding: 16px 32px 24px;
        }

        .totals-grid {
            max-width: 280px;
            margin-left: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .tot-row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            font-size: 13px;
        }

        .tot-row .tl { color: #64748b; }
        .tot-row .tv { font-weight: 600; color: #1e293b; font-family: monospace; }

        .tot-row.grand {
            border-top: 1.5px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 2px;
            font-size: 15px;
        }

        .tot-row.grand .tv { color: #16a34a; font-size: 17px; }

        .tot-row.paid-row .tv { color: #2563eb; }
        .tot-row.due-row  .tv { color: #dc2626; }

        /* ---- Payment badge ---- */
        .pay-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .pay-badge.paid    { background: #dcfce7; color: #15803d; }
        .pay-badge.due     { background: #fee2e2; color: #dc2626; }
        .pay-badge.partial { background: #fef3c7; color: #d97706; }

        /* ---- Note / footer ---- */
        .inv-footer {
            padding: 16px 32px 28px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            flex-wrap: wrap;
        }

        .inv-footer .note-block {
            max-width: 380px;
        }

        .inv-footer .note-block .note-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .inv-footer .note-block p {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .inv-footer .sig-block {
            text-align: center;
            flex-shrink: 0;
        }

        .inv-footer .sig-block .sig-line {
            width: 140px;
            border-top: 1px solid #cbd5e1;
            margin: 32px auto 4px;
        }

        .inv-footer .sig-block p {
            font-size: 11px;
            color: #94a3b8;
        }

        /* ---- Print button (screen only) ---- */
        .screen-only {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 16px;
        }

        .btn {
            height: 38px;
            padding: 0 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print { background: #1e293b; color: #fff; }
        .btn-close { background: #f1f5f9; color: #475569; }

        /* ---- Print styles ---- */
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-wrap { box-shadow: none; border-radius: 0; }
            .screen-only { display: none !important; }
        }
    </style>
</head>
<body>

<div class="invoice-wrap">

    {{-- Header --}}
    <div class="inv-header">
        <div class="company">
            <h1>Inaya Creation</h1>
            <p> Chista Market,Near Gawsia Market, Dhaka-1205</p>
            <p>Phone: +880 01300665793</p>
        </div>
        <div class="inv-meta">
            <div class="label">Invoice</div>
            <div class="ref">{{ $sale->reference }}</div>
            <div class="date">{{ $sale->created_at->format('d M Y, h:i A') }}</div>
            <div style="margin-top:8px">
                <span class="pay-badge {{ $sale->payment_status }}">{{ ucfirst($sale->payment_status) }}</span>
            </div>
        </div>
    </div>

    {{-- Info strip --}}
    <div class="inv-info">
        {{-- Customer --}}
        <div class="inv-info-cell">
            <div class="cell-label">Bill To</div>
            <div class="cell-val">{{ $sale->customer?->full_name ?? 'Walk-in Customer' }}</div>
            @if($sale->customer?->phone)
                <div class="cell-sub">{{ $sale->customer->phone }}</div>
            @endif
        </div>

        {{-- Payment method --}}
        <div class="inv-info-cell">
            <div class="cell-label">Payment Method</div>
            <div class="cell-val">{{ $sale->payment_method ?? '—' }}</div>
        </div>

        {{-- Reference numbers --}}
        <div class="inv-info-cell">
            <div class="cell-label">Cash Memo</div>
            <div class="cell-val">{{ $sale->cash_memo ?? '—' }}</div>
        </div>

        @if($sale->bill_no || $sale->bell_no)
            <div class="inv-info-cell">
                <div class="cell-label">Bill No / Bell No</div>
                <div class="cell-val">{{ $sale->bill_no ?? '—' }}</div>
                @if($sale->bell_no)
                    <div class="cell-sub">Bell: {{ $sale->bell_no }}</div>
                @endif
            </div>
        @endif
    </div>

    {{-- Items --}}
    <div class="inv-body">
        <table class="items">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th class="r">Qty</th>
                    <th class="r">Unit Price</th>
                    <th class="r">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $i => $item)
                    <tr>
                        <td style="color:#94a3b8;width:32px">{{ $i + 1 }}</td>
                        <td style="font-weight:600">{{ $item->product->product_name }}</td>
                        <td style="font-family:monospace;font-size:12px;color:#94a3b8">{{ $item->product->sku ?? '—' }}</td>
                        <td class="r">{{ $item->qty }}</td>
                        <td class="r" style="font-family:monospace">৳{{ number_format($item->price_on_sale, 2) }}</td>
                        <td class="r" style="font-family:monospace;font-weight:600">৳{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="inv-totals">
        <div class="totals-grid">
            <div class="tot-row">
                <span class="tl">Subtotal</span>
                <span class="tv">৳{{ number_format($sale->items->sum('line_total'), 2) }}</span>
            </div>
            @if($sale->discount > 0)
                <div class="tot-row">
                    <span class="tl">Discount</span>
                    <span class="tv" style="color:#dc2626">− ৳{{ number_format($sale->discount, 2) }}</span>
                </div>
            @endif
            <div class="tot-row grand">
                <span class="tl">Grand Total</span>
                <span class="tv">৳{{ number_format($sale->grand_total, 2) }}</span>
            </div>
            <div class="tot-row paid-row">
                <span class="tl">Paid</span>
                <span class="tv">৳{{ number_format($sale->paid, 2) }}</span>
            </div>
            @if($sale->due > 0)
                <div class="tot-row due-row">
                    <span class="tl">Due</span>
                    <span class="tv">৳{{ number_format($sale->due, 2) }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer / Note --}}
    <div class="inv-footer">
        <div class="note-block">
            @if($sale->note)
                <div class="note-label">Note</div>
                <p>{{ $sale->note }}</p>
            @else
                <p style="font-size:12px;color:#cbd5e1;font-style:italic">Thank you for your business!</p>
            @endif
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <p>Authorised Signature</p>
        </div>
    </div>

</div>

{{-- Screen-only action buttons --}}
<div class="screen-only">
    <button class="btn btn-print" onclick="window.print()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="6 9 6 2 18 2 18 9"/>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
            <rect x="6" y="14" width="12" height="8"/>
        </svg>
        Print
    </button>
    <button class="btn btn-close" onclick="window.close()">Close</button>
</div>

@if(request('print') == '1')
<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>
@endif

</body>
</html>