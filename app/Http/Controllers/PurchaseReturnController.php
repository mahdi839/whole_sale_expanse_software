<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Stock;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search'        => $request->input('search'),
            'return_status' => $request->input('return_status'),
            'return_type'   => $request->input('return_type'),
            'date'          => $request->input('date'),
        ];

        $returns = PurchaseReturn::query()
            ->with(['purchase', 'supplier', 'items.product'])
            ->when($filters['return_status'], fn($q) => $q->where('return_status', $filters['return_status']))
            ->when($filters['return_type'], fn($q) => $q->where('return_type', $filters['return_type']))
            ->when($filters['date'], fn($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];

                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhereHas('supplier', fn($supplier) => $supplier->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('items.product', fn($product) => $product->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = PurchaseReturn::selectRaw('
            count(*) as total_returns,
            sum(subtotal) as total_subtotal,
            sum(return_amount) as total_return_amount
        ')->first();

        return view('purchase_returns.index', compact('returns', 'filters', 'totals'));
    }
    public function create(Request $request)
    {
        $nextReference = PurchaseReturn::generateReference();
        $suppliers     = Supplier::orderBy('name')->get(['id', 'name', 'code', 'phone']);
        $products      = Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']);

        $purchase = null;
        if ($request->filled('purchase_id')) {
            $purchase = Purchase::with(['supplier', 'items.product'])->find($request->purchase_id);
        }

        return view('purchase_returns.create', compact('nextReference', 'suppliers', 'products', 'purchase'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateReturn($request);

        DB::transaction(function () use ($request, $validated) {
            $reference = $validated['reference'] ?? PurchaseReturn::generateReference();

            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')->store('purchase_returns', 'public');
            }

            $itemsInput = $request->input('items', []);
            $subtotal   = collect($itemsInput)->sum(fn($i) => (float) $i['qty'] * (float) $i['price']);
            $discount   = (float) ($validated['discount'] ?? 0);
            $returnAmount = max(0, $subtotal - $discount);

            $purchaseReturn = PurchaseReturn::create([
                'reference'      => $reference,
                'purchase_id'    => $validated['purchase_id'] ?? null,
                'supplier_id'    => $validated['supplier_id'] ?? null,
                'discount'       => $discount,
                'subtotal'       => $subtotal,
                'return_amount'  => $returnAmount,
                'return_type'    => $validated['return_type'],
                'return_status'  => $validated['return_status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'date'           => $validated['date'],
                'document'       => $validated['document'] ?? null,
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['qty'];
                $price     = (float) $item['price'];
                $lineTotal = $qty * $price;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id'   => $item['purchase_item_id'] ?? null,
                    'product_id'         => $product->id,
                    'qty'                => $qty,
                    'price'              => $price,
                    'line_total'         => $lineTotal,
                ]);
            }

            if ($purchaseReturn->return_status === 'approved') {
                $this->applyReturnEffects($purchaseReturn->fresh('items'));
                $purchaseReturn->purchase->update([
                    'purchase_status'=> 'returned'
                ]);
            }
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Purchase return created successfully.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase', 'supplier', 'items.product', 'items.purchaseItem']);

        return view('purchase_returns.show', compact('purchaseReturn'));
    }

    public function edit(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['items.product', 'items.purchaseItem', 'purchase.items.product', 'supplier']);

        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'code', 'phone']);
        $products  = Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('purchase_returns.edit', compact('purchaseReturn', 'suppliers', 'products'));
    }

    public function update(Request $request, PurchaseReturn $purchaseReturn)
    {
        $validated = $this->validateReturn($request, $purchaseReturn->id);
        $oldStatus = $purchaseReturn->return_status;

        DB::transaction(function () use ($request, $purchaseReturn, $validated, $oldStatus) {
            if ($request->hasFile('document')) {
                if ($purchaseReturn->document) {
                    Storage::disk('public')->delete($purchaseReturn->document);
                }

                $validated['document'] = $request->file('document')->store('purchase_returns', 'public');
            }

            if ($oldStatus === 'approved') {
                $this->reverseReturnEffects($purchaseReturn->load('items'));
                 $purchaseReturn->purchase->update([
                    'purchase_status'=> 'returned'
                ]);
            }

            $purchaseReturn->items()->delete();

            $itemsInput = $request->input('items', []);
            $subtotal   = collect($itemsInput)->sum(fn($i) => (float) $i['qty'] * (float) $i['price']);
            $discount   = (float) ($validated['discount'] ?? 0);
            $returnAmount = max(0, $subtotal - $discount);

            $purchaseReturn->update([
                'purchase_id'    => $validated['purchase_id'] ?? null,
                'supplier_id'    => $validated['supplier_id'] ?? null,
                'discount'       => $discount,
                'subtotal'       => $subtotal,
                'return_amount'  => $returnAmount,
                'return_type'    => $validated['return_type'],
                'return_status'  => $validated['return_status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'date'           => $validated['date'],
                'document'       => $validated['document'] ?? $purchaseReturn->document,
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (float) $item['qty'];
                $price   = (float) $item['price'];

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id'   => $item['purchase_item_id'] ?? null,
                    'product_id'         => $product->id,
                    'qty'                => $qty,
                    'price'              => $price,
                    'line_total'         => $qty * $price,
                ]);
            }

            if ($purchaseReturn->return_status === 'approved') {
                $this->applyReturnEffects($purchaseReturn->fresh('items'));
            } else {
                $this->syncPurchaseStatus($purchaseReturn);
                $this->syncSupplierFinancials($purchaseReturn->supplier_id);
            }
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Purchase return updated successfully.');
    }

    public function destroy(PurchaseReturn $purchaseReturn)
    {
        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->load('items');

            if ($purchaseReturn->return_status === 'approved') {
                $this->reverseReturnEffects($purchaseReturn);
            }

            if ($purchaseReturn->document) {
                Storage::disk('public')->delete($purchaseReturn->document);
            }

            $supplierId = $purchaseReturn->supplier_id;
            $purchaseReturn->items()->delete();
            $purchaseReturn->delete();

            $this->syncSupplierFinancials($supplierId);
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Purchase return deleted successfully.');
    }

    public function approve(PurchaseReturn $purchaseReturn)
    {
        if ($purchaseReturn->return_status === 'approved') {
            return back()->with('error', 'Purchase return is already approved.');
        }

        DB::transaction(function () use ($purchaseReturn) {
            $purchaseReturn->load('items');
            $purchaseReturn->update(['return_status' => 'approved']);
            $this->applyReturnEffects($purchaseReturn);
        });

        return back()->with('success', 'Purchase return approved successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'purchase-returns-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $rows = PurchaseReturn::with(['purchase', 'supplier', 'items.product'])
            ->when($request->return_status, fn($q) => $q->where('return_status', $request->return_status))
            ->when($request->return_type, fn($q) => $q->where('return_type', $request->return_type))
            ->latest()
            ->get();

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Reference',
                'Original Purchase',
                'Supplier',
                'Items',
                'Subtotal',
                'Discount',
                'Return Amount',
                'Return Type',
                'Return Status',
                'Payment Method',
                'Cash Memo',
                'Date',
                'Note',
            ]);

            foreach ($rows as $row) {
                $itemsSummary = $row->items->map(
                    fn($i) => ($i->product?->product_name ?? 'Unknown Product') . ' x' . $i->qty . ' @' . $i->price
                )->implode(' | ');

                fputcsv($file, [
                    $row->reference,
                    $row->purchase?->reference,
                    $row->supplier?->name,
                    $itemsSummary,
                    $row->subtotal,
                    $row->discount,
                    $row->return_amount,
                    $row->return_type,
                    $row->return_status,
                    $row->payment_method,
                    $row->cash_memo,
                    optional($row->date)->format('Y-m-d'),
                    $row->note,
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function applyReturnEffects(PurchaseReturn $ret): void
    {
        $ret->loadMissing('items');

        foreach ($ret->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)->first();
            if ($stock) {
                $stock->decrement('stock_qty', (float) $item->qty);
            }
        }

        $this->syncSupplierFinancials($ret->supplier_id);
        $this->syncPurchaseStatus($ret);
    }

    private function reverseReturnEffects(PurchaseReturn $ret): void
    {
        $ret->loadMissing('items');

        foreach ($ret->items as $item) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $item->product_id],
                ['stock_qty' => 0]
            );

            $stock->increment('stock_qty', (float) $item->qty);
        }

        $this->syncSupplierFinancials($ret->supplier_id);
        $this->syncPurchaseStatus($ret);
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

        $purchaseTotals = Purchase::where('supplier_id', $supplierId)
            ->selectRaw('
                COALESCE(SUM(grand_total), 0) as total_purchase,
                COALESCE(SUM(paid_amount), 0) as total_paid
            ')
            ->first();

        $approvedReturns = PurchaseReturn::where('supplier_id', $supplierId)
            ->where('return_status', 'approved')
            ->selectRaw('
                COALESCE(SUM(return_amount), 0) as total_return_amount,
                COALESCE(SUM(CASE WHEN return_type IN ("refund","exchange") THEN return_amount ELSE 0 END), 0) as total_refunded
            ')
            ->first();

        $grossPurchase = (float) ($purchaseTotals->total_purchase ?? 0);
        $grossPaid     = (float) ($purchaseTotals->total_paid ?? 0);

        $totalReturnAmount = (float) ($approvedReturns->total_return_amount ?? 0);
        $totalRefunded     = (float) ($approvedReturns->total_refunded ?? 0);

        $netPurchase = max(0, $grossPurchase - $totalReturnAmount);
        $netPaid     = max(0, $grossPaid - $totalRefunded);

        $supplier->update([
            'total_purchase' => $netPurchase,
            'total_paid'     => $netPaid,
            'due'            => max(0, $netPurchase - $netPaid),
        ]);
    }

    private function syncPurchaseStatus(PurchaseReturn $ret): void
    {
        if (! $ret->purchase_id) {
            return;
        }

        $purchase = Purchase::find($ret->purchase_id);
        if (! $purchase) {
            return;
        }

        $hasApprovedReturns = PurchaseReturn::where('purchase_id', $purchase->id)
            ->where('return_status', 'approved')
            ->where('id', '!=', $ret->id)
            ->exists();

        $purchase->update([
            'purchase_status' => $hasApprovedReturns ? 'partial' : $purchase->purchase_status,
        ]);
    }

    private function validateReturn(Request $request, ?int $returnId = null): array
    {
        return $request->validate([
            'reference'                => 'nullable|string|max:50|unique:purchase_returns,reference,' . $returnId,
            'purchase_id'              => 'nullable|exists:purchases,id',
            'supplier_id'              => 'nullable|exists:suppliers,id',
            'discount'                 => 'nullable|numeric|min:0',
            'return_type'              => 'required|in:refund,exchange,credit',
            'return_status'            => 'required|in:pending,approved,rejected',
            'payment_method'           => 'nullable|string|max:100',
            'cash_memo'                => 'nullable|string|max:100',
            'date'                     => 'required|date',
            'document'                 => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'note'                     => 'nullable|string|max:2000',

            'items'                    => 'required|array|min:1',
            'items.*.purchase_item_id' => 'nullable|exists:purchase_items,id',
            'items.*.product_id'       => 'required|exists:products,id',
            'items.*.qty'              => 'required|numeric|min:0.01',
            'items.*.price'            => 'required|numeric|min:0',
        ]);
    }
}
