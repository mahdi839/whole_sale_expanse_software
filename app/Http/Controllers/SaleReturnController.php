<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search'        => $request->input('search'),
            'date'          => $request->input('date'),
            'return_status' => $request->input('return_status'),
            'return_type'   => $request->input('return_type'),
        ];

        $returns = SaleReturn::query()
            ->with(['sale', 'customer', 'product'])
            ->when($filters['date'],          fn($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['return_status'], fn($q) => $q->where('return_status', $filters['return_status']))
            ->when($filters['return_type'],   fn($q) => $q->where('return_type',   $filters['return_type']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference',    'like', "%{$s}%")
                        ->orWhere('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%")
                        ->orWhere('cash_memo',    'like', "%{$s}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = SaleReturn::selectRaw('
            count(*)            as total_returns,
            sum(qty)            as total_qty,
            sum(return_amount)  as total_return_amount
        ')->first();

        return view('sale_returns.index', compact('returns', 'filters', 'totals'));
    }

    public function create(Request $request)
    {
        $nextReference = SaleReturn::generateReference();
        $customers     = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products      = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        // If coming from a sale's show page, pre-fill from that sale
        $sale = null;
        if ($request->filled('sale_id')) {
            $sale = Sale::with(['customer', 'product'])->find($request->sale_id);
        }

        return view('sale_returns.create', compact('nextReference', 'customers', 'products', 'sale'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateReturn($request);

        DB::transaction(function () use ($request, &$validated) {

            // Document upload
            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')
                    ->store('sale_returns', 'public');
            }

            // Resolve product details
            if (! empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                if ($product) {
                    $validated['product_name'] = $product->product_name;
                    $validated['product_code'] = $product->sku;
                }
            }

            // Compute amounts
            $validated['subtotal']      = (float) $validated['qty'] * (float) $validated['price_on_sale'];
            $validated['subtotal']     -= (float) ($validated['discount'] ?? 0);
            $validated['return_amount'] = $validated['subtotal'];

            if (empty($validated['reference'])) {
                $validated['reference'] = SaleReturn::generateReference();
            }

            $saleReturn = SaleReturn::create($validated);

            // Only apply stock/financial effects when approved
            if ($validated['return_status'] === 'approved') {
                $this->applyReturnEffects($saleReturn);
            }
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Return created successfully.');
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale', 'customer', 'product']);

        return view('sale_returns.show', compact('saleReturn'));
    }

    public function edit(SaleReturn $saleReturn)
    {
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products  = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('sale_returns.edit', compact('saleReturn', 'customers', 'products'));
    }

    public function update(Request $request, SaleReturn $saleReturn)
    {
        $oldStatus = $saleReturn->return_status;
        $validated = $this->validateReturn($request, $saleReturn->id);

        DB::transaction(function () use ($request, $saleReturn, $validated, $oldStatus) {

            // Document upload
            if ($request->hasFile('document')) {
                if ($saleReturn->document) {
                    Storage::disk('public')->delete($saleReturn->document);
                }
                $validated['document'] = $request->file('document')
                    ->store('sale_returns', 'public');
            }

            // Resolve product details
            if (! empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                if ($product) {
                    $validated['product_name'] = $product->product_name;
                    $validated['product_code'] = $product->sku;
                }
            }

            // Recompute amounts
            $validated['subtotal']      = (float) $validated['qty'] * (float) $validated['price_on_sale'];
            $validated['subtotal']     -= (float) ($validated['discount'] ?? 0);
            $validated['return_amount'] = $validated['subtotal'];

            $newStatus = $validated['return_status'];

            // If was approved → now being changed: reverse old effects first
            if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                $this->reverseReturnEffects($saleReturn);
            }

            $saleReturn->update($validated);

            // If newly approved (wasn't before): apply effects
            if ($oldStatus !== 'approved' && $newStatus === 'approved') {
                $this->applyReturnEffects($saleReturn->fresh());
            }

            // If still approved but data changed: reverse then reapply
            if ($oldStatus === 'approved' && $newStatus === 'approved') {
                $this->reverseReturnEffects($saleReturn);
                $this->applyReturnEffects($saleReturn->fresh());
            }
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Return updated successfully.');
    }

    public function destroy(SaleReturn $saleReturn)
    {
        DB::transaction(function () use ($saleReturn) {

            // Reverse effects if it was approved
            if ($saleReturn->return_status === 'approved') {
                $this->reverseReturnEffects($saleReturn);
            }

            if ($saleReturn->document) {
                Storage::disk('public')->delete($saleReturn->document);
            }

            $saleReturn->delete();
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Return deleted successfully.');
    }

    /**
     * Quick-approve a pending return directly from the index/show page.
     */
    public function approve(SaleReturn $saleReturn)
    {
        if ($saleReturn->return_status === 'approved') {
            return back()->with('error', 'Return is already approved.');
        }

        DB::transaction(function () use ($saleReturn) {
            $saleReturn->update(['return_status' => 'approved']);
            $this->applyReturnEffects($saleReturn);
        });

        return back()->with('success', 'Return approved successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'sale-returns-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $rows = SaleReturn::query()
            ->when($request->date,          fn($q) => $q->whereDate('date', $request->date))
            ->when($request->return_status, fn($q) => $q->where('return_status', $request->return_status))
            ->when($request->return_type,   fn($q) => $q->where('return_type',   $request->return_type))
            ->when($request->search, function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference',    'like', "%{$s}%")
                        ->orWhere('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%");
                });
            })
            ->latest()->get();

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Reference',
                'Original Sale',
                'Customer',
                'Product',
                'Product Code',
                'Qty',
                'Price',
                'Discount',
                'Subtotal',
                'Return Amount',
                'Return Type',
                'Return Status',
                'Payment Method',
                'Cash Memo',
                'Date',
                'Reason',
                'Note',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->reference,
                    $row->sale?->reference,
                    $row->customer?->full_name,
                    $row->product_name,
                    $row->product_code,
                    $row->qty,
                    $row->price_on_sale,
                    $row->discount,
                    $row->subtotal,
                    $row->return_amount,
                    $row->return_type,
                    $row->return_status,
                    $row->payment_method,
                    $row->cash_memo,
                    optional($row->date)->format('Y-m-d'),
                    $row->reason,
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

    // ── Private helpers ─────────────────────────────────────────────────────

    /**
     * Apply stock restore + customer financial reversal when a return is approved.
     * - Stock goes back up (returned goods re-enter inventory)
     * - Customer total_sale goes down, total_paid goes down by refund amount, due recalculated
     */
    private function applyReturnEffects(SaleReturn $ret): void
    {
        // 1. Restore stock
        if ($ret->product_id) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $ret->product_id],
                ['stock_qty'  => 0]
            );
            $stock->increment('stock_qty', (float) $ret->qty);
        }

        // 2. Adjust customer financials
        if ($ret->customer_id) {
            $customer = Customer::find($ret->customer_id);
            if ($customer) {
                $customer->decrement('total_sale', (float) $ret->return_amount);

                if (in_array($ret->return_type, ['refund', 'exchange'])) {
                    $customer->decrement('total_paid', (float) $ret->return_amount);
                }

                $customer->recalculateDue();
            }
        }

        // 3. Mark related sale as returned
        if ($ret->sale_id) {
            $sale = Sale::find($ret->sale_id);
            if ($sale) {
                $sale->update([
                    'status' => 'returned',
                ]);
            }
        }
    }

    /**
     * Reverse the effects of an already-approved return (used before update/delete).
     */
    private function reverseReturnEffects(SaleReturn $ret): void
    {
        if ($ret->product_id) {
            $stock = Stock::where('product_id', $ret->product_id)->first();
            if ($stock) {
                $stock->decrement('stock_qty', (float) $ret->qty);
            }
        }

        if ($ret->customer_id) {
            $customer = Customer::find($ret->customer_id);
            if ($customer) {
                $customer->increment('total_sale', (float) $ret->return_amount);

                if (in_array($ret->return_type, ['refund', 'exchange'])) {
                    $customer->increment('total_paid', (float) $ret->return_amount);
                }

                $customer->recalculateDue();
            }
        }

        if ($ret->sale_id) {
            $sale = Sale::find($ret->sale_id);
            if ($sale) {
                $hasApprovedReturns = SaleReturn::where('sale_id', $sale->id)
                    ->where('return_status', 'approved')
                    ->where('id', '!=', $ret->id)
                    ->exists();

                $sale->update([
                    'status' => $hasApprovedReturns ? 'returned' : 'success',
                ]);
            }
        }
    }
    private function validateReturn(Request $request, ?int $returnId = null): array
    {
        return $request->validate([
            'reference'      => 'nullable|string|max:50|unique:sale_returns,reference,' . $returnId,
            'sale_id'        => 'nullable|exists:sales,id',
            'customer_id'    => 'nullable|exists:customers,id',
            'product_id'     => 'nullable|exists:products,id',
            'qty'            => 'required|numeric|min:0.01',
            'price_on_sale'  => 'required|numeric|min:0',
            'discount'       => 'nullable|numeric|min:0',
            'return_type'    => 'required|in:refund,exchange,credit',
            'return_status'  => 'required|in:pending,approved,rejected',
            'payment_method' => 'nullable|string|max:100',
            'cash_memo'      => 'nullable|string|max:100',
            'document'       => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'reason'         => 'nullable|string|max:1000',
            'note'           => 'nullable|string|max:2000',
            'date'           => 'required|date',
        ]);
    }
}
