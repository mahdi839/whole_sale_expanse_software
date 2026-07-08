<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Support\SimplePdf;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $totals = Supplier::query()
            ->selectRaw('count(*) as cnt, sum(total_purchase) as tp, sum(total_paid) as tpd, sum(due) as td')
            ->selectSub(function ($query) {
                $query->from('purchase_items')
                    ->selectRaw('COALESCE(SUM(purchase_items.qty), 0)')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id');
            }, 'total_purchase_qty')
            ->first();

        $currencyTotals = Supplier::query()
            ->selectRaw('currency, SUM(total_purchase) as total_purchase, SUM(total_paid) as total_paid, SUM(due) as total_due')
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        $suppliers = $this->supplierIndexQuery($search)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search', 'totals', 'currencyTotals'));
    }

    public function exportIndexPdf(Request $request)
    {
        $search = $request->input('search');
        $suppliers = $this->supplierIndexQuery($search)
            ->orderBy('name')
            ->get();

        $headers = ['Code', 'Full Name', 'Phone', 'Address', 'Total Quantity', 'Total Purchase', 'Total Paid', 'Total Due'];
        $rows = $suppliers->map(fn (Supplier $supplier) => [
            $supplier->code,
            $supplier->name,
            $supplier->phone ?? '-',
            $supplier->address ?? '-',
            number_format((float) ($supplier->total_purchase_qty ?? 0), 2),
            trim(($supplier->currency ?? 'BDT').' '.number_format((float) $supplier->total_purchase, 2)),
            trim(($supplier->currency ?? 'BDT').' '.number_format((float) $supplier->total_paid, 2)),
            trim(($supplier->currency ?? 'BDT').' '.number_format((float) $supplier->due, 2)),
        ]);

        return Response::make(SimplePdf::table('Inaya Creation - Suppliers', $headers, $rows, null, [
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="suppliers-'.now()->format('Y-m-d-H-i-s').'.pdf"',
        ]);
    }

    public function create()
    {
        $nextCode = Supplier::generateCode();
        return view('suppliers.create', compact('nextCode'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSupplier($request);
        $validated['due'] = max(0, $validated['total_purchase'] - $validated['total_paid']);

        $supplier = Supplier::create($validated);

        // JSON response for AJAX (quick-add from purchase form)
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($supplier, 201);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $logs = $this->supplierLogs($supplier);
        $totalQty = (float) DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchases.supplier_id', $supplier->id)
            ->sum('purchase_items.qty');

        return view('suppliers.show', compact('supplier', 'logs', 'totalQty'));
    }

    public function exportTransactions(Supplier $supplier)
    {
        $logs = $this->supplierLogs($supplier);
        $totalQty = (float) DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchases.supplier_id', $supplier->id)
            ->sum('purchase_items.qty');
        $headers = ['Date & Time', 'Type', 'Reference', 'Amount', 'Qty', 'Paid', 'Due','Note'];
        $rows = $logs->map(fn ($log) => [
            optional($log['display_at'] ?? $log['date'])->format('Y-m-d h:i A'),
            $log['type'],
            $log['reference'],
            $log['amount'],
            $log['qty'],
            $log['paid'],
            $log['due'],
            $log['note'],
        ]);

        if (request('format') === 'pdf') {
            $fileName = 'supplier-'.$supplier->code.'-transactions-'.now()->format('Y-m-d-H-i-s').'.pdf';
            $summary = [
                ['label' => 'Total Paid', 'value' => number_format((float) $supplier->total_paid, 2)],
                ['label' => 'Total Due', 'value' => number_format((float) $supplier->due, 2)],
                ['label' => 'Total Qty', 'value' => number_format($totalQty, 2)],
                ['label' => 'Total Purchase', 'value' => number_format((float) $supplier->total_purchase, 2)],
            ];

            return Response::make(SimplePdf::table('Inaya Creation - Supplier Transactions - '.$supplier->name, $headers, $rows, null, [
                'summary' => $summary,
                'logo_path' => public_path('inaya_creation_logo.jpeg'),
            ]), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            ]);
        }

        $fileName = 'supplier-'.$supplier->code.'-transactions-'.now()->format('Y-m-d-H-i-s').'.csv';

        return Response::stream(function () use ($rows, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validateSupplier($request);
        $validated['due'] = max(0, $validated['total_purchase'] - $validated['total_paid']);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    private function validateSupplier(Request $request): array
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'currency'       => 'nullable|in:BDT,INR,USD',
            'total_purchase' => 'nullable|numeric|min:0',
            'total_paid'     => 'nullable|numeric|min:0',
        ]);

        $data['total_purchase'] = $data['total_purchase'] ?? 0;
        $data['total_paid']     = $data['total_paid']     ?? 0;
        $data['currency']       = $data['currency']       ?? 'BDT';

        return $data;
    }

    private function supplierLogs(Supplier $supplier)
    {
        return collect()
            ->merge($supplier->purchases()->with('items.product')->get()->map(fn ($purchase) => [
                'date' => $purchase->date,
                'sort_at' => $this->logSortAt($purchase->date, $purchase->created_at),
                'type' => 'Purchase',
                'reference' => $purchase->reference,
                'amount' => (float) $purchase->grand_total,
                'qty' => $purchase->items->sum(fn ($item) => (float) $item->qty),
                'paid' => (float) $purchase->paid_amount,
                'due' => (float) $purchase->due_amount,
                'products' => $purchase->items->map(fn ($item) => $item->product?->product_name.' x'.$item->qty)->filter()->implode(', '),
                'note' => $purchase->note,
                'url' => route('purchases.edit', $purchase),
            ]))
            ->merge($supplier->purchaseReturns()->with('items.product')->get()->map(function ($return) {
                $products = $return->items->map(fn ($item) => $item->product?->product_name.' x'.$item->qty)->filter()->implode(', ');

                return [
                    'date' => $return->date,
                    'sort_at' => $this->logSortAt($return->date, $return->created_at),
                    'type' => 'Purchase Return',
                    'reference' => $return->reference,
                    'amount' => -1 * (float) $return->return_amount,
                    'qty' => -1 * $return->items->sum(fn ($item) => (float) $item->qty),
                    'paid' => $return->return_type === 'refund' ? -1 * (float) $return->return_amount : 0,
                    'due' => 0,
                    'products' => $products,
                    'note' => ucfirst($return->return_type).' / '.ucfirst($return->return_status).($return->note ? ' - '.$return->note : ''),
                    'url' => route('purchase-returns.show', $return),
                ];
            }))
            ->merge($supplier->cashTransactions()->whereNull('source_type')->get()->map(fn ($cash) => [
                'date' => $cash->date,
                'sort_at' => $this->logSortAt($cash->date, $cash->created_at),
                'type' => 'Payment',
                'reference' => $cash->reference,
                'amount' => $cash->direction === 'out'
                    ? (float) ($cash->supplier_amount ?? $cash->amount)
                    : -1 * (float) ($cash->supplier_amount ?? $cash->amount),
                'qty' => null,
                'paid' => (float) ($cash->supplier_amount ?? $cash->amount),
                'due' => "-",
                'products' => '',
                'note' => $cash->note,
                'url' => route('cash-transactions.index', ['search' => $cash->reference]),
            ]))
            ->merge($supplier->manualDues()->get()->map(fn ($due) => [
                'date' => $due->date,
                'sort_at' => $this->logSortAt($due->date, $due->created_at),
                'type' => 'Manual Due',
                'reference' => $due->reference ?? 'Manual',
                'amount' => (float) $due->amount,
                'qty' => null,
                'paid' => 0,
                'due' => (float) $due->amount,
                'products' => '',
                'note' => $due->note,
                'url' => route('dues.manual'),
            ]))
            ->sortBy('sort_at')
            ->map(function ($log) {
                $log['display_at'] = $log['sort_at'] ?? $log['date'];
                unset($log['sort_at']);

                return $log;
            })
            ->values();
    }

    private function supplierIndexQuery(?string $search)
    {
        return Supplier::query()
            ->addSelect([
                'total_purchase_qty' => DB::table('purchase_items')
                    ->selectRaw('COALESCE(SUM(purchase_items.qty), 0)')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id'),
            ])
            ->when($search, fn($q) => $q
                ->where('name',  'like', "%{$search}%")
                ->orWhere('code',  'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
            );
    }

    private function logSortAt($date, $createdAt): ?Carbon
    {
        if (! $date) {
            return $createdAt;
        }

        $sortAt = $date instanceof Carbon
            ? $date->copy()
            : ($date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date));

        if ($createdAt instanceof DateTimeInterface) {
            return $sortAt->setTime(
                (int) $createdAt->format('H'),
                (int) $createdAt->format('i'),
                (int) $createdAt->format('s')
            );
        }

        return $sortAt->startOfDay();
    }
}
