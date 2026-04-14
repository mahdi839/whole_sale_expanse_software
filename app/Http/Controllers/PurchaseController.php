<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    public function index(Request $request)
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
            ->with(['supplier', 'product'])
            ->when($filters['date'], fn($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['seller_store_name'], fn($q) => $q->where('seller_store_name', 'like', '%' . $filters['seller_store_name'] . '%'))
            ->when($filters['purchased_by'], fn($q) => $q->where('purchased_by', 'like', '%' . $filters['purchased_by'] . '%'))
            ->when($filters['purchase_status'], fn($q) => $q->where('purchase_status', $filters['purchase_status']))
            ->when($filters['payment_status'], fn($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%")
                        ->orWhere('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhere('seller_store_name', 'like', "%{$s}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Purchase::selectRaw('
            count(*)           as total_purchases,
            sum(qty)           as total_qty,
            sum(subtotal)      as total_subtotal,
            sum(grand_total)   as total_amount
        ')->first();

        return view('purchases.index', compact('purchases', 'filters', 'totals'));
    }

    public function create()
    {
        $nextReference = Purchase::generateReference();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'code', 'phone']);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('purchases.create', compact('nextReference', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('purchases', 'public');
        }

        // Auto-fill name fields from related models if IDs provided
        if (! empty($validated['supplier_id'])) {
            $supplier = Supplier::find($validated['supplier_id']);
            $validated['seller_store_name'] = $supplier?->name ?? $validated['seller_store_name'];
        }

        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            $validated['product_name'] = $product?->product_name ?? $validated['product_name'];
            $validated['product_code'] = $product?->sku ?? $validated['product_code'];

            if ($product) {
                $stock = Stock::firstOrCreate(
                    ['product_id' => $product->id],
                    ['stock_qty' => 0],
                );
                $stock->increment('stock_qty', (float) $validated['qty']);
            }
        }

        $validated['subtotal'] = (float) $validated['qty'] * (float) $validated['price'];
        $validated['grand_total'] = $validated['subtotal'] + (float) ($validated['other_cost'] ?? 0);

        // Compute payment amounts based on status
        $validated = $this->resolvePaymentAmounts($validated);
        if (empty($validated['reference'])) {
            $validated['reference'] = Purchase::generateReference();
        }
        Purchase::create($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'product']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'code', 'phone']);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
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

        if (! empty($validated['supplier_id'])) {
            $supplier = Supplier::find($validated['supplier_id']);
            $validated['seller_store_name'] = $supplier?->name ?? $validated['seller_store_name'];
        }

        if (! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            $validated['product_name'] = $product?->product_name ?? $validated['product_name'];
            $validated['product_code'] = $product?->sku ?? $validated['product_code'];
        }

        if ($purchase->product_id) {
            $oldStock = Stock::where('product_id', $purchase->product_id)->first();
            if ($oldStock) {
                $oldStock->decrement('stock_qty', $purchase->qty);
            }
        }

        // add new qty to new product stock
        if (! empty($validated['product_id'])) {
            $newStock = Stock::firstOrCreate(
                ['product_id' => $validated['product_id']],
                ['stock_qty' => 0]
            );

            $newStock->increment('stock_qty', (float) $validated['qty']);
        }


        $validated['subtotal'] = (float) $validated['qty'] * (float) $validated['price'];
        $validated['grand_total'] = $validated['subtotal'] + (float) ($validated['other_cost'] ?? 0);
        $validated = $this->resolvePaymentAmounts($validated);

        $purchase->update($validated);

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->product_id) {
            $stock = Stock::where('product_id', $purchase->product_id)->first();
            if ($stock) {
                $stock->decrement('stock_qty', (float) $purchase->qty);
            }
        }

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
            ->when($request->date, fn($q) => $q->whereDate('date', $request->date))
            ->when($request->seller_store_name, fn($q) => $q->where('seller_store_name', 'like', '%' . $request->seller_store_name . '%'))
            ->when($request->purchased_by, fn($q) => $q->where('purchased_by', 'like', '%' . $request->purchased_by . '%'))
            ->when($request->purchase_status, fn($q) => $q->where('purchase_status', $request->purchase_status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($sub) use ($s) {
                    $sub->where('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%")
                        ->orWhere('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%");
                });
            })
            ->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
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
                'Due Amount',
                'Paid Amount',
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
                    $row->due_amount,
                    $row->paid_amount,
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

    // ── Helpers ────────────────────────────────────────────

    private function resolvePaymentAmounts(array $data): array
    {
        $grandTotal = $data['grand_total'] ?? 0;

        switch ($data['payment_status'] ?? 'due') {
            case 'paid':
                $data['paid_amount'] = $grandTotal;
                $data['due_amount'] = 0;
                break;
            case 'due':
                $data['due_amount'] = $grandTotal;
                $data['paid_amount'] = 0;
                break;
            case 'partial':
                // keep whatever the user entered; just ensure non-negative
                $data['paid_amount'] = max(0, (float) ($data['paid_amount'] ?? 0));
                $data['due_amount'] = max(0, $grandTotal - $data['paid_amount']);
                break;
        }

        return $data;
    }

    private function validatePurchase(Request $request, ?int $purchaseId = null): array
    {
        $rules = [
            'reference' => 'nullable|string|max:50|unique:purchases,reference,' . $purchaseId,
            'supplier_id' => 'nullable|exists:suppliers,id',
            'seller_store_name' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'product_name' => 'nullable|string|max:255',
            'product_code' => 'nullable|string|max:100',
            'purchased_by' => 'required|string|max:255',
            'qty' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'cash_memo' => 'nullable|string|max:100',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'purchase_status' => 'required|in:received,partial,pending,ordered',
            'payment_status' => 'required|in:due,paid,partial',
            'due_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:2000',
        ];

        return $request->validate($rules);
    }
}
