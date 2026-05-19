<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ManualDue;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $filters = [
            'purchase_status' => $request->input('purchase_status'),
            'payment_status' => $request->input('payment_status'),
            'search' => $request->input('search'),
            'date' => $request->input('date', $today),
        ];

        $purchases = Purchase::query()
            ->with(['supplier', 'items.product'])
            ->when($filters['purchase_status'], fn ($q) => $q->where('purchase_status', $filters['purchase_status']))
            ->when($filters['payment_status'], fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];

                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhere('seller_store_name', 'like', "%{$s}%")
                        ->orWhere('purchased_by', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('items.product', fn ($product) => $product->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Purchase::query()
            ->when($filters['purchase_status'], fn ($q) => $q->where('purchase_status', $filters['purchase_status']))
            ->when($filters['payment_status'], fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];

                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhere('seller_store_name', 'like', "%{$s}%")
                        ->orWhere('purchased_by', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('items.product', fn ($product) => $product->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->selectRaw('
            count(*)              as total_purchases,
            sum(grand_total)      as total_amount,
            sum(paid_amount)      as total_paid,
            sum(due_amount)       as total_due
        ')->first();

        $totals->total_qty = (float) PurchaseItem::query()
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->when($filters['purchase_status'], fn ($q) => $q->where('purchases.purchase_status', $filters['purchase_status']))
            ->when($filters['payment_status'], fn ($q) => $q->where('purchases.payment_status', $filters['payment_status']))
            ->when($filters['date'], fn ($q) => $q->whereDate('purchases.date', $filters['date']))
            ->sum('purchase_items.qty');

        $totals->total_stock = (float) Stock::sum('stock_qty');

        return view('purchases.index', compact('purchases', 'filters', 'totals'));
    }

    public function create()
    {
        $nextReference = Purchase::generateReference();
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::with('stock')
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'sku', 'product_code', 'selling_price']);

        return view('purchases.create', compact('nextReference', 'suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        DB::transaction(function () use ($request, $validated) {
            $reference = $validated['reference'] ?? Purchase::generateReference();

            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')->store('purchases', 'public');
            }

            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn ($i) => (float) $i['qty'] * (float) $i['price']);
            $grandTotal = $itemsTotal + (float) ($validated['other_cost'] ?? 0) - (float) ($validated['discount'] ?? 0);

            [$paidAmount, $dueAmount] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $grandTotal,
                (float) ($validated['paid_amount'] ?? 0)
            );

            $purchase = Purchase::create([
                'reference' => $reference,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'seller_store_name' => $validated['seller_store_name'] ?? null,
                'purchased_by' => $validated['purchased_by'],
                'discount' => $validated['discount'] ?? 0,
                'other_cost' => $validated['other_cost'] ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'cash_memo' => $validated['cash_memo'] ?? null,
                'date' => $validated['date'],
                'payment_method' => $validated['payment_method'] ?? null,
                'document' => $validated['document'] ?? null,
                'note' => $validated['note'] ?? null,
                'purchase_status' => $validated['purchase_status'],
                'payment_status' => $validated['payment_status'],
                'bill_no' => $validated['bill_no'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (float) $item['qty'];
                $price = (float) $item['price'];
                $lineTotal = $qty * $price;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $lineTotal,
                    'bale_no' => $item['bale_no'] ?? null,
                    'batch' => $item['batch'] ?? null,
                ]);
            }

            // Update supplier financials
            $this->syncSupplierFinancials($purchase->supplier_id);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product.stock']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items.product.stock');
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::with('stock')
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'sku', 'product_code', 'selling_price']);

        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $this->validatePurchase($request, $purchase->id);

        DB::transaction(function () use ($request, $purchase, $validated) {
            $oldSupplierId = $purchase->supplier_id;

            if ($request->hasFile('document')) {
                if ($purchase->document) {
                    Storage::disk('public')->delete($purchase->document);
                }

                $validated['document'] = $request->file('document')->store('purchases', 'public');
            }

            $purchase->items()->delete();

            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn ($i) => (float) $i['qty'] * (float) $i['price']);
            $grandTotal = $itemsTotal + (float) ($validated['other_cost'] ?? 0) - (float) ($validated['discount'] ?? 0);

            [$paidAmount, $dueAmount] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $grandTotal,
                (float) ($validated['paid_amount'] ?? 0)
            );

            $purchase->update([
                'supplier_id' => $validated['supplier_id'] ?? null,
                'seller_store_name' => $validated['seller_store_name'] ?? null,
                'purchased_by' => $validated['purchased_by'],
                'discount' => $validated['discount'] ?? 0,
                'other_cost' => $validated['other_cost'] ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'cash_memo' => $validated['cash_memo'] ?? null,
                'date' => $validated['date'],
                'payment_method' => $validated['payment_method'] ?? null,
                'document' => $validated['document'] ?? $purchase->document,
                'note' => $validated['note'] ?? null,
                'purchase_status' => $validated['purchase_status'],
                'payment_status' => $validated['payment_status'],
                'bill_no' => $validated['bill_no'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (float) $item['qty'];
                $price = (float) $item['price'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $qty * $price,
                    'bale_no' => $item['bale_no'] ?? null,
                    'batch' => $item['batch'] ?? null,
                ]);
            }

            // Recalculate old supplier if changed
            if ($oldSupplierId && $oldSupplierId != $purchase->supplier_id) {
                $this->syncSupplierFinancials($oldSupplierId);
            }

            // Update current supplier totals
            $this->syncSupplierFinancials($purchase->supplier_id);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $supplierId = $purchase->supplier_id;

            if ($purchase->document) {
                Storage::disk('public')->delete($purchase->document);
            }

            $purchase->items()->delete();
            $purchase->delete();

            // Update supplier financials after delete
            $this->syncSupplierFinancials($supplierId);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'purchases-'.now()->format('Y-m-d-H-i-s').'.csv';

        $purchases = Purchase::with(['supplier', 'items.product'])
            ->when($request->purchase_status, fn ($q) => $q->where('purchase_status', $request->purchase_status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
            ->latest()
            ->get();

        $callback = function () use ($purchases) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Reference',
                'Supplier',
                'Store',
                'Purchased By',
                'Products',
                'Grand Total',
                'Discount',
                'Other Cost',
                'Paid',
                'Due',
                'Cash Memo',
                'Date',
                'Payment Method',
                'Purchase Status',
                'Payment Status',
                'Note',
            ]);

            foreach ($purchases as $purchase) {
                $productsSummary = $purchase->items->map(fn ($i) => ($i->product?->product_name ?? 'Unknown Product').' x'.$i->qty.' @'.$i->price
                )->implode(' | ');

                fputcsv($file, [
                    $purchase->reference,
                    $purchase->supplier?->name,
                    $purchase->seller_store_name,
                    $purchase->purchased_by,
                    $productsSummary,
                    $purchase->grand_total,
                    $purchase->discount,
                    $purchase->other_cost,
                    $purchase->paid_amount,
                    $purchase->due_amount,
                    $purchase->cash_memo,
                    optional($purchase->date)->format('Y-m-d'),
                    $purchase->payment_method,
                    $purchase->purchase_status,
                    $purchase->payment_status,
                    $purchase->note,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function resolvePaymentAmounts(string $status, float $grandTotal, float $paidInput): array
    {
        return match ($status) {
            'paid' => [$grandTotal, 0],
            'due' => [0, $grandTotal],
            'partial' => [min($paidInput, $grandTotal), max(0, $grandTotal - $paidInput)],
            default => [0, $grandTotal],
        };
    }

    private function syncSupplierFinancials(?int $supplierId): void
    {
        if (! $supplierId) {
            return;
        }

        $supplier = Supplier::find($supplierId);

        if (! $supplier) {
            return;
        }

        $totals = Purchase::where('supplier_id', $supplierId)
            ->selectRaw('
                COALESCE(SUM(grand_total), 0) as total_purchase,
                COALESCE(SUM(paid_amount), 0) as total_paid
            ')
            ->first();

        $totalPurchase = (float) ($totals->total_purchase ?? 0);
        $totalPaid = (float) ($totals->total_paid ?? 0);
        $manualDue = (float) ManualDue::where('party_type', 'supplier')
            ->where('supplier_id', $supplierId)
            ->sum('amount');

        $supplier->update([
            'total_purchase' => $totalPurchase + $manualDue,
            'total_paid' => $totalPaid,
            'due' => max(0, ($totalPurchase + $manualDue) - $totalPaid),
        ]);
    }

    private function validatePurchase(Request $request, ?int $purchaseId = null): array
    {
        return $request->validate([
            'reference' => 'nullable|string|max:50|unique:purchases,reference,'.$purchaseId,
            'supplier_id' => 'nullable|exists:suppliers,id',
            'seller_store_name' => 'nullable|string|max:255',
            'purchased_by' => 'required|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'other_cost' => 'nullable|numeric|min:0',
            'cash_memo' => 'nullable|string|max:100',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'note' => 'nullable|string|max:2000',
            'purchase_status' => 'required|in:received,partial,pending,ordered',
            'payment_status' => 'required|in:due,paid,partial',
            'paid_amount' => 'nullable|numeric|min:0',
            'bill_no' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.bale_no' => 'nullable|string|max:100',
            'items.*.batch' => 'nullable|string|max:100',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);
    }
}
