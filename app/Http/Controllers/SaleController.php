<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'date' => $request->input('date'),
            'purchase_status' => $request->input('purchase_status'),
            'payment_status' => $request->input('payment_status'),
            'search' => $request->input('search'),
        ];

        $sales = Sale::query()
            ->with(['customer', 'product'])
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['purchase_status'], fn ($q) => $q->where('purchase_status', $filters['purchase_status']))
            ->when($filters['payment_status'], fn ($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%")
                        ->orWhere('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Sale::selectRaw('
            count(*)          as total_sales,
            sum(qty)          as total_qty,
            sum(grand_total)  as total_amount,
            sum(paid)         as total_paid,
            sum(due)          as total_due
        ')->first();

        return view('sales.index', compact('sales', 'filters', 'totals'));
    }

    public function create()
    {
        $nextReference = Sale::generateReference();
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('sales.create', compact('nextReference', 'customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSale($request);

        DB::transaction(function () use ($request, &$validated) {

            // Handle document upload
            if ($request->hasFile('document')) {
                $validated['document'] = $request->file('document')
                    ->store('sales', 'public');
            }

            // Resolve product name/code and deduct stock
            if (! empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);

                if ($product) {
                    $validated['product_name'] = $product->product_name;
                    $validated['product_code'] = $product->sku;

                    // Deduct stock
                    $stock = Stock::firstOrCreate(
                        ['product_id' => $product->id],
                        ['stock_qty' => 0]
                    );
                    $stock->decrement('stock_qty', (float) $validated['qty']);
                }
            }

            // Compute totals
            $validated['subtotal'] = (float) $validated['qty'] * (float) $validated['price_on_sale'];
            $validated['subtotal'] -= (float) ($validated['discount'] ?? 0);
            $validated['grand_total'] = $validated['subtotal'];

            // Resolve payment amounts
            $validated = $this->resolvePaymentAmounts($validated);

            if (empty($validated['reference'])) {
                $validated['reference'] = Sale::generateReference();
            }

            $validated['status'] = 'success';
            $sale = Sale::create($validated);

            // Update customer financials
            if (! empty($validated['customer_id'])) {
                $customer = Customer::find($validated['customer_id']);
                if ($customer) {
                    $customer->increment('total_sale', $validated['grand_total']);
                    $customer->increment('total_paid', $validated['paid']);
                    $customer->recalculateDue();
                }
            }
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'product']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $this->validateSale($request, $sale->id);

        DB::transaction(function () use ($request, $sale, &$validated) {

            // Handle document upload
            if ($request->hasFile('document')) {
                if ($sale->document) {
                    Storage::disk('public')->delete($sale->document);
                }
                $validated['document'] = $request->file('document')
                    ->store('sales', 'public');
            }

            // ── Reverse old stock deduction ──────────────────────────
            if ($sale->product_id) {
                $oldStock = Stock::where('product_id', $sale->product_id)->first();
                if ($oldStock) {
                    $oldStock->increment('stock_qty', (float) $sale->qty);
                }
            }

            // ── Reverse old customer financials ──────────────────────
            if ($sale->customer_id) {
                $oldCustomer = Customer::find($sale->customer_id);
                if ($oldCustomer) {
                    $oldCustomer->decrement('total_sale', (float) $sale->grand_total);
                    $oldCustomer->decrement('total_paid', (float) $sale->paid);
                    $oldCustomer->recalculateDue();
                }
            }

            // ── Apply new product / stock ────────────────────────────
            if (! empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                if ($product) {
                    $validated['product_name'] = $product->product_name;
                    $validated['product_code'] = $product->sku;

                    $newStock = Stock::firstOrCreate(
                        ['product_id' => $product->id],
                        ['stock_qty' => 0]
                    );
                    $newStock->decrement('stock_qty', (float) $validated['qty']);
                }
            }

            // Compute totals
            $validated['subtotal'] = (float) $validated['qty'] * (float) $validated['price_on_sale'];
            $validated['subtotal'] -= (float) ($validated['discount'] ?? 0);
            $validated['grand_total'] = $validated['subtotal'];

            $validated = $this->resolvePaymentAmounts($validated);

            $sale->update($validated);

            // ── Apply new customer financials ────────────────────────
            if (! empty($validated['customer_id'])) {
                $newCustomer = Customer::find($validated['customer_id']);
                if ($newCustomer) {
                    $newCustomer->increment('total_sale', $validated['grand_total']);
                    $newCustomer->increment('total_paid', $validated['paid']);
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

            // Restore stock
            if ($sale->product_id) {
                $stock = Stock::where('product_id', $sale->product_id)->first();
                if ($stock) {
                    $stock->increment('stock_qty', (float) $sale->qty);
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

            if ($sale->document) {
                Storage::disk('public')->delete($sale->document);
            }

            $sale->delete();
        });

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'sales-'.now()->format('Y-m-d-H-i-s').'.csv';

        $rows = Sale::query()
            ->when($request->date, fn ($q) => $q->whereDate('date', $request->date))
            ->when($request->purchase_status, fn ($q) => $q->where('purchase_status', $request->purchase_status))
            ->when($request->payment_status, fn ($q) => $q->where('payment_status', $request->payment_status))
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

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Reference', 'Customer', 'Product', 'Product Code',
                'Qty', 'Price', 'Discount', 'Subtotal', 'Grand Total',
                'Paid', 'Due', 'Cash Memo', 'Date',
                'Payment Method', 'Purchase Status', 'Payment Status', 'Note',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row->reference,
                    $row->customer?->full_name,
                    $row->product_name,
                    $row->product_code,
                    $row->qty,
                    $row->price_on_sale,
                    $row->discount,
                    $row->subtotal,
                    $row->grand_total,
                    $row->paid,
                    $row->due,
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

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function resolvePaymentAmounts(array $data): array
    {
        $grandTotal = $data['grand_total'] ?? 0;

        switch ($data['payment_status'] ?? 'due') {
            case 'paid':
                $data['paid'] = $grandTotal;
                $data['due'] = 0;
                break;
            case 'due':
                $data['due'] = $grandTotal;
                $data['paid'] = 0;
                break;
            case 'partial':
                $data['paid'] = max(0, (float) ($data['paid'] ?? 0));
                $data['due'] = max(0, $grandTotal - $data['paid']);
                break;
        }

        return $data;
    }

    private function validateSale(Request $request, ?int $saleId = null): array
    {
        return $request->validate([
            'reference' => 'nullable|string|max:50|unique:sales,reference,'.$saleId,
            'customer_id' => 'nullable|exists:customers,id',
            'product_id' => 'nullable|exists:products,id',
            'qty' => 'required|numeric|min:0.01',
            'price_on_sale' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'cash_memo' => 'nullable|string|max:100',
            'payment_method' => 'nullable|string|max:100',
            'purchase_status' => 'required|in:received,partial,pending,ordered',
            'payment_status' => 'required|in:due,paid,partial',
            'status'=>'nullable',
            'paid' => 'nullable|numeric|min:0',
            'due' => 'nullable|numeric|min:0',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:4096',
            'note' => 'nullable|string|max:2000',
            'date' => 'required|date',
        ]);
    }
}
