<?php

namespace App\Http\Controllers;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{  public function index(Request $request)
    {
        $filters = [
            'date' => $request->input('date'),
            'seller_store_name' => $request->input('seller_store_name'),
            'purchased_by' => $request->input('purchased_by'),
            'purchase_status' => $request->input('purchase_status'),
            'payment_status' => $request->input('payment_status'),
            'search' => $request->input('search'),
        ];

        $purchases = Purchase::query()
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['seller_store_name'], fn ($q) =>
                $q->where('seller_store_name', 'like', '%' . $filters['seller_store_name'] . '%'))
            ->when($filters['purchased_by'], fn ($q) =>
                $q->where('purchased_by', 'like', '%' . $filters['purchased_by'] . '%'))
            ->when($filters['purchase_status'], fn ($q) =>
                $q->where('purchase_status', $filters['purchase_status']))
            ->when($filters['payment_status'], fn ($q) =>
                $q->where('payment_status', $filters['payment_status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('cash_memo', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Purchase::selectRaw('
            count(*) as total_purchases,
            sum(qty) as total_qty,
            sum(subtotal) as total_subtotal,
            sum(grand_total) as total_amount
        ')->first();

        return view('purchases.index', compact('purchases', 'filters', 'totals'));
    }

    public function create()
    {
        $nextReference = Purchase::generateReference();
        return view('purchases.create', compact('nextReference'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('purchases', 'public');
        }

        $validated['subtotal'] = (float) $validated['qty'] * (float) $validated['price'];
        $validated['grand_total'] = $validated['subtotal'] + (float) ($validated['other_cost'] ?? 0);

        Purchase::create($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        return view('purchases.edit', compact('purchase'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $this->validatePurchase($request, $purchase->id);

        if ($request->hasFile('document')) {
            if ($purchase->document) {
                Storage::disk('public')->delete($purchase->document);
            }

            $validated['document'] = $request->file('document')->store('purchases', 'public');
        }

        $validated['subtotal'] = (float) $validated['qty'] * (float) $validated['price'];
        $validated['grand_total'] = $validated['subtotal'] + (float) ($validated['other_cost'] ?? 0);

        $purchase->update($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->document) {
            Storage::disk('public')->delete($purchase->document);
        }

        $purchase->delete();

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'purchases-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $rows = Purchase::query()
            ->when($request->date, fn ($q) => $q->whereDate('date', $request->date))
            ->when($request->seller_store_name, fn ($q) =>
                $q->where('seller_store_name', 'like', '%' . $request->seller_store_name . '%'))
            ->when($request->purchased_by, fn ($q) =>
                $q->where('purchased_by', 'like', '%' . $request->purchased_by . '%'))
            ->when($request->purchase_status, fn ($q) =>
                $q->where('purchase_status', $request->purchase_status))
            ->when($request->payment_status, fn ($q) =>
                $q->where('payment_status', $request->payment_status))
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('product_name', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('cash_memo', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Reference',
                'Seller/Store',
                'Purchased By',
                'Product Name',
                'Product Code',
                'Qty',
                'Price',
                'Subtotal',
                'Other Cost',
                'Grand Total',
                'Cash Memo',
                'Date',
                'Payment Method',
                'Purchase Status',
                'Payment Status',
                'Note',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->reference,
                    $row->seller_store_name,
                    $row->purchased_by,
                    $row->product_name,
                    $row->product_code,
                    $row->qty,
                    $row->price,
                    $row->subtotal,
                    $row->other_cost,
                    $row->grand_total,
                    $row->cash_memo,
                    optional($row->date)->format('Y-m-d'),
                    $row->payment_method,
                    $row->purchase_status,
                    $row->payment_status,
                    $row->note,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function validatePurchase(Request $request, ?int $purchaseId = null): array
    {
        return $request->validate([
            'reference' => 'nullable|string|max:50|unique:purchases,reference,' . $purchaseId,
            'seller_store_name' => 'required|string|max:255',
            'purchased_by' => 'required|string|max:255',
            'product_name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:100',
            'qty' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
            'cash_memo' => 'nullable|string|max:100',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'other_cost' => 'nullable|numeric|min:0',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'purchase_status' => 'required|in:received,partial,pending,ordered',
            'payment_status' => 'required|in:due,paid,partial',
            'note' => 'nullable|string|max:2000',
        ]);
    }
}
