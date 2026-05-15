<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\Stock;
use App\Services\CashLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $filters = [
            'shop_id'        => $request->input('shop_id'),
            'payment_status' => $request->input('payment_status'),
            'status'         => $request->input('status'),
            'search'         => $request->input('search'),
            'date_from'      => $request->input('date_from', $today),
            'date_to'        => $request->input('date_to', $today),
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
            sum(due)         as total_due,
            sum(return_amount) as total_return_amount
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
        $products      = Product::with(['stocks', 'purchaseItems.returnItems', 'purchaseItems.saleItems.returnItems'])
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'sku', 'product_code', 'purchase_price', 'selling_price']);
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
                'return_amount'  => $validated['return_amount'] ?? 0,
                'cash_memo'      => $validated['cash_memo'] ?? null,
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
                $purchaseItem = $this->resolvePurchaseItem($product->id, $item['purchase_item_id'] ?? null);
               
                $costPrice  = (float) ($product->purchase_price ?? 0);
                $profit     = $price - $costPrice;
                $lineProfit = $profit * $qty;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'purchase_item_id' => $purchaseItem?->id,
                    'batch'         => $purchaseItem?->batch,
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

            app(CashLedger::class)->syncSource('sale', $sale->id, 'in', 'sale_payment', (float) $sale->paid, [
                'date' => $sale->created_at->toDateString(),
                'payment_method' => $sale->payment_method,
                'customer_id' => $sale->customer_id,
                'note' => 'Sale payment: ' . $sale->reference,
            ]);
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
        $products  = Product::with(['stocks', 'purchaseItems.returnItems', 'purchaseItems.saleItems.returnItems'])
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'sku', 'product_code', 'purchase_price', 'selling_price']);
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

            app(CashLedger::class)->deleteSource('sale', $sale->id);

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
                'return_amount'  => $validated['return_amount'] ?? 0,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'bell_no'        => $validated['bell_no'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_status' => $validated['payment_status'],
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty     = (float) $item['qty'];
                $price   = (float) $item['price_on_sale'];

                $purchaseItem = $this->resolvePurchaseItem($product->id, $item['purchase_item_id'] ?? null);
                $costPrice  = (float) ($product->purchase_price ?? 0);
                $profit     = $price - $costPrice;
                $lineProfit = $profit * $qty;

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'purchase_item_id' => $purchaseItem?->id,
                    'batch'         => $purchaseItem?->batch,
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

            app(CashLedger::class)->syncSource('sale', $sale->id, 'in', 'sale_payment', (float) $sale->paid, [
                'date' => $sale->created_at->toDateString(),
                'payment_method' => $sale->payment_method,
                'customer_id' => $sale->customer_id,
                'note' => 'Sale payment: ' . $sale->reference,
            ]);
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

            app(CashLedger::class)->deleteSource('sale', $sale->id);

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
        $totalQty = $sale->items->sum(fn($item) => (float) $item->qty);
        $totalQtyDisplay = floor($totalQty) == $totalQty
            ? number_format($totalQty, 0)
            : number_format($totalQty, 2);
        $customerTotalDue = $sale->customer ? (float) $sale->customer->due : null;

        return view('sales.invoice', compact('sale', 'totalQtyDisplay', 'customerTotalDue'));
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

    private function resolvePurchaseItem(int $productId, ?int $purchaseItemId): ?\App\Models\PurchaseItem
    {
        if ($purchaseItemId) {
            return \App\Models\PurchaseItem::where('product_id', $productId)->find($purchaseItemId);
        }

        return \App\Models\PurchaseItem::where('product_id', $productId)->latest('id')->first();
    }

    private function getBatchAvailableQty(int $purchaseItemId): float
    {
        $purchaseItem = \App\Models\PurchaseItem::find($purchaseItemId);
        if (! $purchaseItem) {
            return 0;
        }

        $returnedToSupplier = DB::table('purchase_return_items')
            ->join('purchase_returns', 'purchase_returns.id', '=', 'purchase_return_items.purchase_return_id')
            ->where('purchase_returns.return_status', 'approved')
            ->where('purchase_return_items.purchase_item_id', $purchaseItemId)
            ->sum('purchase_return_items.qty');

        $sold = DB::table('sale_items')
            ->where('purchase_item_id', $purchaseItemId)
            ->sum('qty');

        $returnedByCustomer = DB::table('sale_return_items')
            ->join('sale_returns', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->join('sale_items', 'sale_items.id', '=', 'sale_return_items.sale_item_id')
            ->where('sale_returns.return_status', 'approved')
            ->where('sale_items.purchase_item_id', $purchaseItemId)
            ->sum('sale_return_items.qty');

        return max(0, (float) $purchaseItem->qty - (float) $returnedToSupplier - (float) $sold + (float) $returnedByCustomer);
    }
    private function validateSale(Request $request, ?int $saleId = null): array
    {
        return $request->validate([
            'reference'              => 'nullable|string|max:50|unique:sales,reference,' . $saleId,
            'shop_id'                => 'nullable|exists:shops,id',
            'customer_id'            => 'nullable|exists:customers,id',
            'discount'               => 'nullable|numeric|min:0',
            'cash_memo'              => 'nullable|string|max:100',
            'bell_no'                => 'nullable|string|max:100',
            'payment_method'         => 'nullable|string|max:100',
            'payment_status'         => 'required|in:due,paid,partial',
            'paid'                   => 'nullable|numeric|min:0',
            'return_amount'          => 'nullable|numeric|min:0',
            'note'                   => 'nullable|string|max:2000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.purchase_item_id' => 'nullable|exists:purchase_items,id',
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
