<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'payment_status' => $request->input('payment_status'),
            'status'         => $request->input('status'),
            'search'         => $request->input('search'),
        ];

        $sales = Sale::query()
            ->with(['customer', 'items.product'])
            ->when($filters['payment_status'], fn($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$s}%"))
                        ->orWhereHas('items.product', fn($p) => $p->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Sale::selectRaw('
        count(*)         as total_sales,
        sum(grand_total) as total_amount,
        sum(paid)        as total_paid,
        sum(due)         as total_due
    ')->first();

        return view('sales.index', compact('sales', 'filters', 'totals'));
    }

    public function create()
    {
        $nextReference = Sale::generateReference();
        $customers     = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products      = Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('sales.create', compact('nextReference', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSale($request);

        DB::transaction(function () use ($request, $validated) {

            // Build reference
            $reference = $validated['reference'] ?? Sale::generateReference();

            // Compute grand total from items
            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn($i) => (float)$i['qty'] * (float)$i['price_on_sale']);
            $grandTotal = $itemsTotal - (float)($validated['discount'] ?? 0);

            // Resolve payment amounts
            [$paid, $due] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $grandTotal,
                (float)($validated['paid'] ?? 0)
            );

            // Create sale header
            $sale = Sale::create([
                'reference'      => $reference,
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $validated['discount'] ?? 0,
                'grand_total'    => $grandTotal,
                'paid'           => $paid,
                'due'            => $due,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'status'         => 'success',
                'note'           => $validated['note'] ?? null,
            ]);

            // Create items & deduct stock
            foreach ($itemsInput as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['qty'];
                $price     = (float) $item['price_on_sale'];
                $lineTotal = $qty * $price;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'qty'           => $qty,
                    'price_on_sale' => $price,
                    'line_total'    => $lineTotal,
                ]);

                // Deduct stock
                $stock = Stock::firstOrCreate(
                    ['product_id' => $product->id],
                    ['stock_qty'  => 0]
                );
                $stock->decrement('stock_qty', $qty);
            }

            // Update customer financials
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $customer->increment('total_sale', $grandTotal);
                    $customer->increment('total_paid', $paid);
                    $customer->recalculateDue();
                }
            }
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product.stock']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $sale->load('items.product');
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products  = Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $this->validateSale($request, $sale->id);

        DB::transaction(function () use ($request, $sale, $validated) {

            // ── Reverse old stock & customer ──────────────────────────
            foreach ($sale->items as $oldItem) {
                $stock = Stock::where('product_id', $oldItem->product_id)->first();
                if ($stock) {
                    $stock->increment('stock_qty', (float) $oldItem->qty);
                }
            }

            if ($sale->customer_id) {
                $oldCustomer = Customer::find($sale->customer_id);
                if ($oldCustomer) {
                    $oldCustomer->decrement('total_sale', (float) $sale->grand_total);
                    $oldCustomer->decrement('total_paid', (float) $sale->paid);
                    $oldCustomer->recalculateDue();
                }
            }

            // ── Delete old items & rebuild ────────────────────────────
            $sale->items()->delete();

            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn($i) => (float)$i['qty'] * (float)$i['price_on_sale']);
            $grandTotal = $itemsTotal - (float)($validated['discount'] ?? 0);

            [$paid, $due] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $grandTotal,
                (float)($validated['paid'] ?? 0)
            );

            $sale->update([
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $validated['discount'] ?? 0,
                'grand_total'    => $grandTotal,
                'paid'           => $paid,
                'due'            => $due,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['qty'];
                $price     = (float) $item['price_on_sale'];

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'qty'           => $qty,
                    'price_on_sale' => $price,
                    'line_total'    => $qty * $price,
                ]);

                $stock = Stock::firstOrCreate(
                    ['product_id' => $product->id],
                    ['stock_qty'  => 0]
                );
                $stock->decrement('stock_qty', $qty);
            }

            // ── Apply new customer financials ─────────────────────────
            if (! empty($validated['customer_id'])) {
                $newCustomer = Customer::find($validated['customer_id']);
                if ($newCustomer) {
                    $newCustomer->increment('total_sale', $grandTotal);
                    $newCustomer->increment('total_paid', $paid);
                    $newCustomer->recalculateDue();
                }
            }
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {

            // Restore stock for every item
            foreach ($sale->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)->first();
                if ($stock) {
                    $stock->increment('stock_qty', (float) $item->qty);
                }
            }

            // Reverse customer financials
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    $customer->decrement('total_sale', (float) $sale->grand_total);
                    $customer->decrement('total_paid', (float) $sale->paid);
                    $customer->recalculateDue();
                }
            }

            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'sales-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $sales = Sale::with(['customer', 'items.product'])
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->latest()->get();

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Reference',
                'Customer',
                'Products',
                'Grand Total',
                'Discount',
                'Paid',
                'Due',
                'Cash Memo',
                'Payment Method',
                'Payment Status',
                'Note',
                'Date',
            ]);

            foreach ($sales as $sale) {
                $productsSummary = $sale->items->map(
                    fn($i) =>
                    $i->product->product_name . ' x' . $i->qty . ' @' . $i->price_on_sale
                )->implode(' | ');

                fputcsv($file, [
                    $sale->reference,
                    $sale->customer?->full_name,
                    $productsSummary,
                    $sale->grand_total,
                    $sale->discount,
                    $sale->paid,
                    $sale->due,
                    $sale->cash_memo,
                    $sale->payment_method,
                    $sale->payment_status,
                    $sale->note,
                    $sale->created_at->format('Y-m-d'),
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function resolvePaymentAmounts(string $status, float $grandTotal, float $paidInput): array
    {
        return match ($status) {
            'paid'    => [$grandTotal, 0],
            'due'     => [0, $grandTotal],
            'partial' => [min($paidInput, $grandTotal), max(0, $grandTotal - $paidInput)],
            default   => [0, $grandTotal],
        };
    }

    private function validateSale(Request $request, ?int $saleId = null): array
    {
        $rules = [
            'reference'        => 'nullable|string|max:50|unique:sales,reference,' . $saleId,
            'customer_id'      => 'nullable|exists:customers,id',
            'discount'         => 'nullable|numeric|min:0',
            'cash_memo'        => 'nullable|string|max:100',
            'payment_method'   => 'nullable|string|max:100',
            'payment_status'   => 'required|in:due,paid,partial',
            'paid'             => 'nullable|numeric|min:0',
            'note'             => 'nullable|string|max:2000',
            // items array
            'items'            => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.qty'           => 'required|numeric|min:0.01',
            'items.*.price_on_sale' => 'required|numeric|min:0',
        ];

        return $request->validate($rules);
    }
}
