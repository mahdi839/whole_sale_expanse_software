<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $suppliers = Supplier::query()
            ->when($search, fn($q) => $q
                ->where('name',  'like', "%{$search}%")
                ->orWhere('code',  'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
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

        return view('suppliers.show', compact('supplier', 'logs'));
    }

    public function exportTransactions(Supplier $supplier)
    {
        $logs = $this->supplierLogs($supplier);
        $fileName = 'supplier-'.$supplier->code.'-transactions-'.now()->format('Y-m-d-H-i-s').'.csv';

        return Response::stream(function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Type', 'Reference', 'Amount', 'Paid', 'Due', 'Note']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    optional($log['date'])->format('Y-m-d'),
                    $log['type'],
                    $log['reference'],
                    $log['amount'],
                    $log['paid'],
                    $log['due'],
                    $log['note'],
                ]);
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
            'email'          => 'nullable|email|max:255',
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
                'paid' => (float) $purchase->paid_amount,
                'due' => (float) $purchase->due_amount,
                'note' => $purchase->items->map(fn ($item) => $item->product?->product_name.' x'.$item->qty)->implode(', '),
                'url' => route('purchases.show', $purchase),
            ]))
            ->merge($supplier->purchaseReturns()->latest('date')->latest()->get()->map(fn ($return) => [
                'date' => $return->date,
                'type' => 'Purchase Return',
                'reference' => $return->reference,
                'amount' => -1 * (float) $return->return_amount,
                'paid' => $return->return_type === 'refund' ? -1 * (float) $return->return_amount : 0,
                'due' => 0,
                'note' => ucfirst($return->return_type).' / '.ucfirst($return->return_status),
                'url' => route('purchase-returns.show', $return),
            ]))
            ->merge($supplier->cashTransactions()->latest('date')->latest()->get()->map(fn ($cash) => [
                'date' => $cash->date,
                'type' => 'Payment',
                'reference' => $cash->reference,
                'amount' => $cash->direction === 'out' ? (float) $cash->amount : -1 * (float) $cash->amount,
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
                'paid' => 0,
                'due' => (float) $due->amount,
                'note' => $due->note,
                'url' => route('dues.manual'),
            ]))
            ->sortByDesc('date')
            ->values();
    }
}
