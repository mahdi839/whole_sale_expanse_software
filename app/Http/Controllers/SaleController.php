<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'shop_id'        => $request->input('shop_id'),
            'payment_status' => $request->input('payment_status'),
            'status'         => $request->input('status'),
            'search'         => $request->input('search'),
            'date_from'      => $request->input('date_from'),
            'date_to'        => $request->input('date_to'),
        ];

        $sales = Sale::query()
            ->with(['customer', 'items.product', 'shop'])
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->where('shop_id', auth()->user()->shop_id))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn($q) => $q->where('shop_id', $filters['shop_id']))
            ->when($filters['payment_status'], fn($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhere('bill_no', 'like', "%{$s}%")
                        ->orWhere('bell_no', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$s}%"))
                        ->orWhereHas('items.product', fn($p) => $p->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->when($filters['date_from'], fn($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totalsQuery = Sale::query()
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->where('shop_id', auth()->user()->shop_id))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn($q) => $q->where('shop_id', $filters['shop_id']))
            ->when($filters['payment_status'], fn($q) => $q->where('payment_status', $filters['payment_status']))
            ->when($filters['status'], fn($q) => $q->where('status', $filters['status']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('cash_memo', 'like', "%{$s}%")
                        ->orWhere('bill_no', 'like', "%{$s}%")
                        ->orWhere('bell_no', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$s}%"))
                        ->orWhereHas('items.product', fn($p) => $p->where('product_name', 'like', "%{$s}%"));
                });
            })
            ->when($filters['date_from'], fn($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('created_at', '<=', $filters['date_to']));

        $totals = $totalsQuery->selectRaw('
            count(*)         as total_sales,
            sum(grand_total) as total_amount,
            sum(paid)        as total_paid,
            sum(due)         as total_due
        ')->first();

        $shops = auth()->user()->canManageAllShops()
            ? Shop::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('sales.index', compact('sales', 'filters', 'totals', 'shops'));
    }

    public function create()
    {
        abort_unless(auth()->user()->canManageAllShops() || auth()->user()->shop_id, 403, 'No shop assigned to your user.');

        $nextReference = Sale::generateReference();
        $customers     = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products      = Product::with('stocks')->orderBy('product_name')->get(['id', 'product_name', 'sku']);
        $shops = auth()->user()->canManageAllShops()
            ? Shop::where('is_active', true)->orderBy('name')->get()
            : collect([auth()->user()->shop]);

        return view('sales.create', compact('nextReference', 'customers', 'products', 'shops'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSale($request);

        DB::transaction(function () use ($request, $validated) {

            $reference  = $validated['reference'] ?? Sale::generateReference();
            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn($i) => (float) $i['qty'] * (float) $i['price_on_sale']);
            $grandTotal = $itemsTotal - (float) ($validated['discount'] ?? 0);

            [$paid, $due] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $grandTotal,
                (float) ($validated['paid'] ?? 0)
            );

            $shopId = $this->resolveShopId($validated);

            $sale = Sale::create([
                'reference'      => $reference,
                'shop_id'        => $shopId,
                'user_id'        => auth()->id(),
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $validated['discount'] ?? 0,
                'grand_total'    => $grandTotal,
                'paid'           => $paid,
                'due'            => $due,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'bill_no'        => $validated['bill_no'] ?? null,
                'bell_no'        => $validated['bell_no'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'status'         => 'success',
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['qty'];
                $price     = (float) $item['price_on_sale'];
                $lineTotal = $qty * $price;
                $costPrice  = $this->getProductCostPrice($product->id);
                $profit     = $price - $costPrice;
                $lineProfit = $profit * $qty;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'qty'           => $qty,
                    'price_on_sale' => $price,
                    'cost_price'    => $costPrice,
                    'profit'        => $profit,
                    'line_total'    => $lineTotal,
                    'line_profit'   => $lineProfit,
                ]);

                $stock = Stock::firstOrCreate(
                    ['product_id' => $product->id, 'shop_id' => $shopId],
                    ['stock_qty' => 0]
                );
                if ((float) $stock->stock_qty < $qty) {
                    throw ValidationException::withMessages([
                        'items' => 'Not enough stock in this shop for ' . $product->product_name . '.',
                    ]);
                }
                $stock->decrement('stock_qty', $qty);
            }

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
        $this->authorizeSaleShop($sale);
        $sale->load(['customer', 'items.product.stock', 'shop', 'user']);

        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $this->authorizeSaleShop($sale);
        $sale->load('items.product.stocks');
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products  = Product::with('stocks')->orderBy('product_name')->get(['id', 'product_name', 'sku']);
        $shops = auth()->user()->canManageAllShops()
            ? Shop::where('is_active', true)->orderBy('name')->get()
            : collect([auth()->user()->shop]);

        return view('sales.edit', compact('sale', 'customers', 'products', 'shops'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $this->validateSale($request, $sale->id);
        $this->authorizeSaleShop($sale);

        DB::transaction(function () use ($request, $sale, $validated) {

            // Reverse old stock
            foreach ($sale->items as $oldItem) {
                $stock = Stock::where('product_id', $oldItem->product_id)
                    ->where('shop_id', $sale->shop_id)
                    ->first();
                if ($stock) {
                    $stock->increment('stock_qty', (float) $oldItem->qty);
                }
            }

            // Reverse old customer financials
            if ($sale->customer_id) {
                $oldCustomer = Customer::find($sale->customer_id);
                if ($oldCustomer) {
                    $oldCustomer->decrement('total_sale', (float) $sale->grand_total);
                    $oldCustomer->decrement('total_paid', (float) $sale->paid);
                    $oldCustomer->recalculateDue();
                }
            }

            $sale->items()->delete();

            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn($i) => (float) $i['qty'] * (float) $i['price_on_sale']);
            $grandTotal = $itemsTotal - (float) ($validated['discount'] ?? 0);

            [$paid, $due] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $grandTotal,
                (float) ($validated['paid'] ?? 0)
            );

            $shopId = $this->resolveShopId($validated, $sale);

            $sale->update([
                'shop_id'        => $shopId,
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $validated['discount'] ?? 0,
                'grand_total'    => $grandTotal,
                'paid'           => $paid,
                'due'            => $due,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'bill_no'        => $validated['bill_no'] ?? null,
                'bell_no'        => $validated['bell_no'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (float) $item['qty'];
                $price   = (float) $item['price_on_sale'];

                $costPrice  = $this->getProductCostPrice($product->id);
                $profit     = $price - $costPrice;
                $lineProfit = $profit * $qty;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'qty'           => $qty,
                    'price_on_sale' => $price,
                    'cost_price'    => $costPrice,
                    'profit'        => $profit,
                    'line_total'    => $qty * $price,
                    'line_profit'   => $lineProfit,
                ]);
                $stock = Stock::firstOrCreate(
                    ['product_id' => $product->id, 'shop_id' => $shopId],
                    ['stock_qty' => 0]
                );
                if ((float) $stock->stock_qty < $qty) {
                    throw ValidationException::withMessages([
                        'items' => 'Not enough stock in this shop for ' . $product->product_name . '.',
                    ]);
                }
                $stock->decrement('stock_qty', $qty);
            }

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
        $this->authorizeSaleShop($sale);

        DB::transaction(function () use ($sale) {

            foreach ($sale->items as $item) {
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('shop_id', $sale->shop_id)
                    ->first();
                if ($stock) {
                    $stock->increment('stock_qty', (float) $item->qty);
                }
            }

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

    /**
     * Show a printable / PDF-ready invoice view.
     */
    public function invoice(Sale $sale)
    {
        $this->authorizeSaleShop($sale);
        $sale->load(['customer', 'items.product', 'shop']);

        return view('sales.invoice', compact('sale'));
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'sales-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $sales = Sale::with(['customer', 'items.product'])
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->where('shop_id', auth()->user()->shop_id))
            ->when(auth()->user()->canManageAllShops() && $request->shop_id, fn($q) => $q->where('shop_id', $request->shop_id))
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
                'Bill No',
                'Bell No',
                'Payment Method',
                'Payment Status',
                'Note',
                'Date',
            ]);

            foreach ($sales as $sale) {
                $productsSummary = $sale->items->map(
                    fn($i) => $i->product->product_name . ' x' . $i->qty . ' @' . $i->price_on_sale
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
                    $sale->bill_no,
                    $sale->bell_no,
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

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resolvePaymentAmounts(string $status, float $grandTotal, float $paidInput): array
    {
        return match ($status) {
            'paid'    => [$grandTotal, 0],
            'due'     => [0, $grandTotal],
            'partial' => [min($paidInput, $grandTotal), max(0, $grandTotal - $paidInput)],
            default   => [0, $grandTotal],
        };
    }

    private function getProductCostPrice(int $productId): float
    {
        $latestPurchasePrice = DB::table('purchase_items')
            ->where('product_id', $productId)
            ->latest('id')
            ->value('price');

        return (float) ($latestPurchasePrice ?? 0);
    }
    private function validateSale(Request $request, ?int $saleId = null): array
    {
        return $request->validate([
            'reference'              => 'nullable|string|max:50|unique:sales,reference,' . $saleId,
            'shop_id'                => 'nullable|exists:shops,id',
            'customer_id'            => 'nullable|exists:customers,id',
            'discount'               => 'nullable|numeric|min:0',
            'cash_memo'              => 'nullable|string|max:100',
            'bill_no'                => 'nullable|string|max:100',
            'bell_no'                => 'nullable|string|max:100',
            'payment_method'         => 'nullable|string|max:100',
            'payment_status'         => 'required|in:due,paid,partial',
            'paid'                   => 'nullable|numeric|min:0',
            'note'                   => 'nullable|string|max:2000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.qty'            => 'required|numeric|min:0.01',
            'items.*.price_on_sale'  => 'required|numeric|min:0',
        ]);
    }

    private function resolveShopId(array $validated, ?Sale $sale = null): int
    {
        if (auth()->user()->canManageAllShops()) {
            $shopId = $validated['shop_id'] ?? $sale?->shop_id;
            abort_unless($shopId, 422, 'Please select a shop for this sale.');

            return (int) $shopId;
        }

        abort_unless(auth()->user()->shop_id, 403, 'No shop assigned to your user.');

        return (int) auth()->user()->shop_id;
    }

    private function authorizeSaleShop(Sale $sale): void
    {
        if (auth()->user()->canManageAllShops()) {
            return;
        }

        abort_unless($sale->shop_id === auth()->user()->shop_id, 403);
    }
}
