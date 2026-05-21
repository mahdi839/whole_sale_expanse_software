<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
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

        $suppliers = Supplier::query()
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
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search', 'totals'));
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
        $headers = ['Date', 'Type', 'Reference', 'Amount', 'Qty', 'Paid', 'Due', 'Products / Note'];
        $rows = $logs->map(fn ($log) => [
            optional($log['date'])->format('Y-m-d'),
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
            $rows = collect([
                ['Shop', 'Inaya Creation', 'Logo', 'inaya_creation_logo.jpeg', '', '', '', ''],
                ['Supplier', $supplier->name, 'Address', $supplier->address ?? '-', '', '', '', ''],
                ['Totals', '', '', (float) $supplier->total_purchase, '', (float) $supplier->total_paid, (float) $supplier->due, ''],
            ])->merge($rows);

            return Response::make(SimplePdf::table('Inaya Creation - Supplier Transactions - '.$supplier->name, $headers, $rows), 200, [
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
            'total_purchase' => 'nullable|numeric|min:0',
            'total_paid'     => 'nullable|numeric|min:0',
        ]);

        $data['total_purchase'] = $data['total_purchase'] ?? 0;
        $data['total_paid']     = $data['total_paid']     ?? 0;

        return $data;
    }

    private function supplierLogs(Supplier $supplier)
    {
        return collect()
            ->merge($supplier->purchases()->with('items.product')->latest('date')->latest()->get()->map(fn ($purchase) => [
                'date' => $purchase->date,
                'type' => 'Purchase',
                'reference' => $purchase->reference,
                'amount' => (float) $purchase->grand_total,
                'qty' => $purchase->items->sum(fn ($item) => (float) $item->qty),
                'paid' => (float) $purchase->paid_amount,
                'due' => (float) $purchase->due_amount,
                'note' => $purchase->items->map(fn ($item) => $item->product?->product_name.' x'.$item->qty)->implode(', '),
                'url' => route('purchases.show', $purchase),
            ]))
            ->merge($supplier->purchaseReturns()->with('items.product')->latest('date')->latest()->get()->map(fn ($return) => [
                'date' => $return->date,
                'type' => 'Purchase Return',
                'reference' => $return->reference,
                'amount' => -1 * (float) $return->return_amount,
                'qty' => -1 * $return->items->sum(fn ($item) => (float) $item->qty),
                'paid' => $return->return_type === 'refund' ? -1 * (float) $return->return_amount : 0,
                'due' => 0,
                'note' => $return->items->map(fn ($item) => $item->product?->product_name.' x'.$item->qty)->filter()->implode(', ')
                    ?: ucfirst($return->return_type).' / '.ucfirst($return->return_status).($return->note ? ' - '.$return->note : ''),
                'url' => route('purchase-returns.show', $return),
            ]))
            ->merge($supplier->cashTransactions()->whereNull('source_type')->latest('date')->latest()->get()->map(fn ($cash) => [
                'date' => $cash->date,
                'type' => 'Payment',
                'reference' => $cash->reference,
                'amount' => $cash->direction === 'out' ? (float) $cash->amount : -1 * (float) $cash->amount,
                'qty' => null,
                'paid' => (float) $cash->amount,
                'due' => 0,
                'note' => $cash->note,
                'url' => route('cash-transactions.index', ['search' => $cash->reference]),
            ]))
            ->merge($supplier->manualDues()->latest('date')->latest()->get()->map(fn ($due) => [
                'date' => $due->date,
                'type' => 'Manual Due',
                'reference' => $due->reference ?? 'Manual',
                'amount' => (float) $due->amount,
                'qty' => null,
                'paid' => 0,
                'due' => (float) $due->amount,
                'note' => $due->note,
                'url' => route('dues.manual'),
            ]))
            ->sortByDesc('date')
            ->values();
    }
}
