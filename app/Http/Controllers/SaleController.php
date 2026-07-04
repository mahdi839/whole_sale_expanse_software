<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CashTransaction;
use App\Models\Expense;
use App\Models\ManualDue;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Shop;
use App\Models\Stock;
use App\Services\CashLedger;
use App\Support\SimplePdf;
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
                        ->orWhere('bank', 'like', "%{$s}%")
                        ->orWhere('bank_details', 'like', "%{$s}%")
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
                        ->orWhere('bank', 'like', "%{$s}%")
                        ->orWhere('bank_details', 'like', "%{$s}%")
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
            sum(add_money)   as total_add_money,
            sum(paid)        as total_paid,
            sum(due)         as total_due,
            sum(return_amount) as total_return_amount
        ')->first();

        $totals->total_sell_qty = (float) SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->where('sales.shop_id', auth()->user()->shop_id))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn($q) => $q->where('sales.shop_id', $filters['shop_id']))
            ->when($filters['payment_status'], fn($q) => $q->where('sales.payment_status', $filters['payment_status']))
            ->when($filters['status'], fn($q) => $q->where('sales.status', $filters['status']))
            ->when($filters['date_from'], fn($q) => $q->whereDate('sales.created_at', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('sales.created_at', '<=', $filters['date_to']))
            ->sum('sale_items.qty');

        $totals->total_stock = (float) Stock::when(
            ! auth()->user()->canManageAllShops(),
            fn($q) => $q->where('shop_id', auth()->user()->shop_id)
        )->sum('stock_qty');

        $totals->total_expense = (float) Expense::query()
            ->when($filters['date_from'], fn($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->sum('amount');

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
        $returnableSales = $this->returnableSales();
        $shops = auth()->user()->canManageAllShops()
            ? Shop::where('is_active', true)->orderBy('name')->get()
            : collect([auth()->user()->shop]);

        return view('sales.create', compact('nextReference', 'customers', 'products', 'shops', 'returnableSales'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSale($request);

        DB::transaction(function () use ($request, $validated) {

            $reference  = $validated['reference'] ?? Sale::generateReference();
            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn($i) => (float) $i['qty'] * (float) $i['price_on_sale']);
            $addMoney = (float) ($validated['add_money'] ?? 0);
            $grandTotal = $itemsTotal - (float) ($validated['discount'] ?? 0) + $addMoney;
            $returnAmount = $this->validatedReturnItems($request->input('returns', []))->sum('line_total');
            $netPayable = max(0, $grandTotal - $returnAmount);

            [$paid, $due] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $netPayable,
                (float) ($validated['paid'] ?? 0)
            );

            $shopId = $this->resolveShopId($validated);

            $sale = Sale::create([
                'reference'      => $reference,
                'shop_id'        => $shopId,
                'user_id'        => auth()->id(),
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $validated['discount'] ?? 0,
                'add_money'      => $addMoney,
                'grand_total'    => $grandTotal,
                'paid'           => $paid,
                'due'            => $due,
                'return_amount'  => $returnAmount,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'bell_no'        => $validated['bell_no'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'bank'           => ($validated['payment_method'] ?? null) === 'Bank' ? ($validated['bank'] ?? null) : null,
                'bank_details'   => ($validated['payment_method'] ?? null) === 'Bank' ? ($validated['bank_details'] ?? null) : null,
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

            $this->createAppliedSaleReturns($sale, $request->input('returns', []));
            $this->syncCashPayment($sale->fresh());
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
        $sale->load('items.product.stocks', 'appliedReturns.items.product', 'appliedReturns.sale');
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products  = Product::with(['stocks', 'purchaseItems.returnItems', 'purchaseItems.saleItems.returnItems'])
            ->orderBy('product_name')
            ->get(['id', 'product_name', 'sku', 'product_code', 'purchase_price', 'selling_price']);
        $returnableSales = $this->returnableSales($sale);
        $shops = auth()->user()->canManageAllShops()
            ? Shop::where('is_active', true)->orderBy('name')->get()
            : collect([auth()->user()->shop]);

        return view('sales.edit', compact('sale', 'customers', 'products', 'shops', 'returnableSales'));
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

            $this->deleteAppliedSaleReturns($sale);

            $sale->items()->delete();

            $itemsInput = $request->input('items', []);
            $itemsTotal = collect($itemsInput)->sum(fn($i) => (float) $i['qty'] * (float) $i['price_on_sale']);
            $addMoney = (float) ($validated['add_money'] ?? 0);
            $grandTotal = $itemsTotal - (float) ($validated['discount'] ?? 0) + $addMoney;
            $returnAmount = $this->validatedReturnItems($request->input('returns', []))->sum('line_total');
            $netPayable = max(0, $grandTotal - $returnAmount);

            [$paid, $due] = $this->resolvePaymentAmounts(
                $validated['payment_status'],
                $netPayable,
                (float) ($validated['paid'] ?? 0)
            );

            $shopId = $this->resolveShopId($validated, $sale);

            $sale->update([
                'shop_id'        => $shopId,
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $validated['discount'] ?? 0,
                'add_money'      => $addMoney,
                'grand_total'    => $grandTotal,
                'paid'           => $paid,
                'due'            => $due,
                'return_amount'  => $returnAmount,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'bell_no'        => $validated['bell_no'] ?? null,
                'payment_method' => $validated['payment_method'] ?? null,
                'bank'           => ($validated['payment_method'] ?? null) === 'Bank' ? ($validated['bank'] ?? null) : null,
                'bank_details'   => ($validated['payment_method'] ?? null) === 'Bank' ? ($validated['bank_details'] ?? null) : null,
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

            $this->createAppliedSaleReturns($sale, $request->input('returns', []));
            $this->syncCashPayment($sale->fresh());
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

            $this->deleteAppliedSaleReturns($sale);
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
        $sale->load(['customer', 'items.product', 'shop', 'appliedReturns.items.product', 'appliedReturns.sale']);
        $totalQty = $sale->items->sum(fn($item) => (float) $item->qty);
        $totalQtyDisplay = floor($totalQty) == $totalQty
            ? number_format($totalQty, 0)
            : number_format($totalQty, 2);
        $customerTotalDue = $sale->customer ? (float) $sale->customer->due : null;
        $customerPreviousDue = $sale->customer ? $this->customerDueBeforeSale($sale) : null;

        return view('sales.invoice', compact('sale', 'totalQtyDisplay', 'customerTotalDue', 'customerPreviousDue'));
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'sales-' . now()->format('Y-m-d-H-i-s') . (request('format') === 'pdf' ? '.pdf' : '.csv');

        $sales = Sale::with(['customer', 'items.product', 'shop'])
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->where('shop_id', auth()->user()->shop_id))
            ->when(auth()->user()->canManageAllShops() && $request->shop_id, fn($q) => $q->where('shop_id', $request->shop_id))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()->get();

        $headers = [
            'Reference',
            'Customer',
            'Products',
            'Qty',
            'Grand Total',
            'Discount',
            'Add Money',
            'Paid',
            'Due',
            'Cash Memo',
            'Bell No',
            'Payment Method',
            'Payment Status',
            'Note',
            'Date',
        ];

        $rows = $sales->map(function ($sale) {
            $productsSummary = $sale->items->map(
                fn($i) => $i->product->product_name . ' x' . $i->qty . ' @' . $i->price_on_sale
            )->implode(' | ');

            return [
                $sale->reference,
                $sale->customer?->full_name,
                $productsSummary,
                $sale->items->sum(fn($item) => (float) $item->qty),
                $sale->grand_total,
                $sale->discount,
                $sale->add_money,
                $sale->paid,
                $sale->due,
                $sale->cash_memo,
                $sale->bell_no,
                $sale->payment_method,
                $sale->payment_status,
                $sale->note,
                $sale->created_at->format('Y-m-d'),
            ];
        });

        if (request('format') === 'pdf') {
            $pdfRows = $sales->map(function ($sale) {
                $products = $sale->items->map(function ($item) {
                    $unitPrice = (float) $item->price_on_sale;
                    $qty = (float) $item->qty;

                    return ($item->product?->product_name ?? 'Product #'.$item->product_id)
                        . ' | Qty: ' . rtrim(rtrim(number_format($qty, 2), '0'), '.')
                        . ' | Unit: ' . number_format($unitPrice, 2)
                        . ' | Line: ' . number_format($qty * $unitPrice, 2);
                })->implode("\n");

                return [
                    $sale->reference,
                    $sale->shop?->name ?? '-',
                    $sale->customer?->full_name,
                    $products,
                    number_format((float) $sale->return_amount, 2),
                    'Status: ' . ucfirst((string) $sale->payment_status)
                        . "\nPaid: " . number_format((float) $sale->paid, 2)
                        . "\nDue: " . number_format((float) $sale->due, 2)
                        . "\nGrand: " . number_format((float) $sale->grand_total, 2),
                ];
            })->values();

            $pdfHeaders = ['Reference', 'Shop', 'Customer', 'Products', 'Return', 'Payment Details'];
            $pdfWidths = [72, 78, 102, 330, 54, 124];

            return Response::make(SimplePdf::table('Inaya Creation - All Sales', $pdfHeaders, $pdfRows, $pdfWidths), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        }

        $callback = function () use ($rows, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($rows as $row) {
                fputcsv($file, $row);
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

    private function syncCashPayment(Sale $sale): void
    {
        if (strtolower((string) $sale->payment_method) !== 'cash' || (float) $sale->paid <= 0) {
            app(CashLedger::class)->deleteSource('sale', $sale->id);
            return;
        }

        app(CashLedger::class)->syncSource('sale', $sale->id, 'in', 'sale', (float) $sale->paid, [
            'date' => $sale->created_at?->toDateString() ?? now()->toDateString(),
            'payment_method' => $sale->payment_method,
            'customer_id' => $sale->customer_id,
            'note' => 'Sale payment: '.$sale->reference,
        ]);
    }

    private function customerDueBeforeSale(Sale $sale): float
    {
        if (! $sale->customer_id) {
            return 0;
        }

        $beforeSale = fn ($query) => $query
            ->where('created_at', '<', $sale->created_at)
            ->orWhere(fn ($sub) => $sub
                ->where('created_at', $sale->created_at)
                ->where('id', '<', $sale->id));

        $saleTotals = Sale::query()
            ->where('customer_id', $sale->customer_id)
            ->where($beforeSale)
            ->selectRaw('COALESCE(SUM(grand_total), 0) as total_sale, COALESCE(SUM(paid), 0) as total_paid')
            ->first();

        $returnTotal = (float) SaleReturn::query()
            ->where('customer_id', $sale->customer_id)
            ->where('return_status', 'approved')
            ->where('return_type', '!=', 'exchange')
            ->where('created_at', '<', $sale->created_at)
            ->sum('return_amount');

        $cashPaid = (float) CashTransaction::query()
            ->where('customer_id', $sale->customer_id)
            ->whereNull('source_type')
            ->where('created_at', '<', $sale->created_at)
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE -amount END), 0) as paid')
            ->value('paid');

        $manualDue = (float) ManualDue::query()
            ->where('party_type', 'customer')
            ->where('customer_id', $sale->customer_id)
            ->whereDate('date', '<', $sale->created_at->toDateString())
            ->sum('amount');

        return max(0, (float) ($saleTotals->total_sale ?? 0) + $manualDue - $returnTotal - (float) ($saleTotals->total_paid ?? 0) - $cashPaid);
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

    private function returnableSales(?Sale $currentSale = null)
    {
        return Sale::query()
            ->with(['customer', 'items.product', 'items.returnItems.saleReturn'])
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->where('shop_id', auth()->user()->shop_id))
            ->when($currentSale, fn($q) => $q->whereKeyNot($currentSale->id))
            ->latest()
            ->limit(250)
            ->get()
            ->map(function (Sale $sale) use ($currentSale) {
                $items = $sale->items->map(function (SaleItem $item) use ($currentSale) {
                    $returnedQty = $item->returnItems
                        ->filter(fn($returnItem) => $returnItem->saleReturn?->return_status === 'approved'
                            && $returnItem->saleReturn?->applied_sale_id !== $currentSale?->id)
                        ->sum(fn($returnItem) => (float) $returnItem->qty);

                    $availableQty = max(0, (float) $item->qty - (float) $returnedQty);

                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->product_name ?? 'Unknown',
                        'sku' => $item->product?->sku ?? '',
                        'qty' => (float) $item->qty,
                        'returned_qty' => (float) $returnedQty,
                        'available_qty' => $availableQty,
                        'price_on_sale' => (float) $item->price_on_sale,
                    ];
                })->filter(fn($item) => $item['available_qty'] > 0)->values();

                return [
                    'id' => $sale->id,
                    'reference' => $sale->reference,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $sale->customer?->full_name ?? 'Walk-in Customer',
                    'created_at' => $sale->created_at?->format('Y-m-d'),
                    'items' => $items,
                ];
            })
            ->filter(fn($sale) => $sale['items']->isNotEmpty())
            ->values();
    }

    private function validatedReturnItems(array $returns)
    {
        return collect($returns)
            ->filter(fn($item) => ! empty($item['sale_id']) && ! empty($item['sale_item_id']) && ! empty($item['product_id']))
            ->map(function ($item) {
                $sale = Sale::with('items.returnItems.saleReturn')->findOrFail($item['sale_id']);
                $this->authorizeSaleShop($sale);

                $saleItem = $sale->items->firstWhere('id', (int) $item['sale_item_id']);
                if (! $saleItem || (int) $saleItem->product_id !== (int) $item['product_id']) {
                    throw ValidationException::withMessages([
                        'returns' => 'Selected return item does not match the original sale.',
                    ]);
                }

                $qty = (float) ($item['qty'] ?? 0);
                if ($qty <= 0) {
                    throw ValidationException::withMessages([
                        'returns' => 'Return quantity must be greater than zero.',
                    ]);
                }

                $returnedQty = $saleItem->returnItems
                    ->filter(fn($returnItem) => $returnItem->saleReturn?->return_status === 'approved')
                    ->sum(fn($returnItem) => (float) $returnItem->qty);
                $availableQty = max(0, (float) $saleItem->qty - (float) $returnedQty);

                if ($qty > $availableQty) {
                    throw ValidationException::withMessages([
                        'returns' => 'Return quantity is higher than available quantity for ' . ($saleItem->product?->product_name ?? 'selected product') . '.',
                    ]);
                }

                $price = (float) $saleItem->price_on_sale;

                return [
                    'sale' => $sale,
                    'sale_item' => $saleItem,
                    'product_id' => (int) $saleItem->product_id,
                    'qty' => $qty,
                    'price_on_sale' => $price,
                    'line_total' => $qty * $price,
                ];
            })
            ->values();
    }

    private function createAppliedSaleReturns(Sale $sale, array $returns): void
    {
        $items = $this->validatedReturnItems($returns);

        $items->groupBy(fn($item) => $item['sale']->id)->each(function ($group) use ($sale) {
            $originalSale = $group->first()['sale'];
            $subtotal = $group->sum('line_total');

            if ($sale->customer_id && $originalSale->customer_id && (int) $sale->customer_id !== (int) $originalSale->customer_id) {
                throw ValidationException::withMessages([
                    'returns' => 'Returned products must belong to the selected customer.',
                ]);
            }

            $saleReturn = SaleReturn::create([
                'reference' => SaleReturn::generateReference(),
                'sale_id' => $originalSale->id,
                'applied_sale_id' => $sale->id,
                'customer_id' => $originalSale->customer_id ?? $sale->customer_id,
                'discount' => 0,
                'subtotal' => $subtotal,
                'return_amount' => $subtotal,
                'return_type' => 'credit',
                'return_status' => 'approved',
                'payment_method' => $sale->payment_method,
                'cash_memo' => $sale->cash_memo,
                'note' =>  $sale->note,
            ]);

            foreach ($group as $item) {
                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id' => $item['sale_item']->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'price_on_sale' => $item['price_on_sale'],
                    'line_total' => $item['line_total'],
                ]);
            }

            $this->applySaleReturnEffects($saleReturn->fresh('items'));
        });
    }

    private function deleteAppliedSaleReturns(Sale $sale): void
    {
        $sale->loadMissing('appliedReturns.items');

        foreach ($sale->appliedReturns as $saleReturn) {
            $this->reverseSaleReturnEffects($saleReturn);
            $saleReturn->items()->delete();
            $saleReturn->delete();
        }
    }

    private function applySaleReturnEffects(SaleReturn $ret): void
    {
        $ret->loadMissing(['items', 'sale']);

        foreach ($ret->items as $item) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $item->product_id, 'shop_id' => $ret->sale?->shop_id],
                ['stock_qty' => 0]
            );

            $stock->increment('stock_qty', (float) $item->qty);
        }

        if ($ret->customer_id) {
            $customer = Customer::find($ret->customer_id);
            if ($customer) {
                $customer->decrement('total_sale', (float) $ret->return_amount);
                $customer->recalculateDue();
            }
        }

        $ret->sale?->update(['status' => 'returned']);
    }

    private function reverseSaleReturnEffects(SaleReturn $ret): void
    {
        $ret->loadMissing(['items', 'sale']);

        foreach ($ret->items as $item) {
            $stock = Stock::where('product_id', $item->product_id)
                ->where('shop_id', $ret->sale?->shop_id)
                ->first();

            if ($stock) {
                $stock->decrement('stock_qty', (float) $item->qty);
            }
        }

        if ($ret->customer_id) {
            $customer = Customer::find($ret->customer_id);
            if ($customer) {
                $customer->increment('total_sale', (float) $ret->return_amount);
                $customer->recalculateDue();
            }
        }

        if ($ret->sale_id) {
            $hasApprovedReturns = SaleReturn::where('sale_id', $ret->sale_id)
                ->where('return_status', 'approved')
                ->where('id', '!=', $ret->id)
                ->exists();

            $ret->sale?->update(['status' => $hasApprovedReturns ? 'returned' : 'success']);
        }
    }

    private function validateSale(Request $request, ?int $saleId = null): array
    {
        return $request->validate([
            'reference'              => 'nullable|string|max:50|unique:sales,reference,' . $saleId,
            'shop_id'                => 'nullable|exists:shops,id',
            'customer_id'            => 'nullable|exists:customers,id',
            'discount'               => 'nullable|numeric|min:0',
            'add_money'              => 'nullable|numeric|min:0',
            'cash_memo'              => 'nullable|string|max:100',
            'bell_no'                => 'nullable|string|max:100',
           'payment_method' => [
                'nullable',
                'required_if:payment_status,paid,partial',
                'string',
                'max:100',
             ],
            'bank'                   => 'nullable|string|max:100',
            'bank_details'           => 'nullable|string|max:255',
            'payment_status'         => 'required|in:due,paid,partial',
            'paid'                   => 'nullable|numeric|min:0',
            'note'                   => 'nullable|string|max:2000',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.purchase_item_id' => 'nullable|exists:purchase_items,id',
            'items.*.qty'            => 'required|numeric|min:0.01',
            'items.*.price_on_sale'  => 'required|numeric|min:0',
            'returns'                  => 'nullable|array',
            'returns.*.sale_id'        => 'required_with:returns|exists:sales,id',
            'returns.*.sale_item_id'   => 'required_with:returns|exists:sale_items,id',
            'returns.*.product_id'     => 'required_with:returns|exists:products,id',
            'returns.*.qty'            => 'required_with:returns|numeric|min:0.01',
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
