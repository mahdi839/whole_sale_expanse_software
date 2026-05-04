<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SaleReturnController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search'        => $request->input('search'),
            'return_status' => $request->input('return_status'),
            'return_type'   => $request->input('return_type'),
        ];

        $returns = SaleReturn::query()
            ->with(['sale', 'customer', 'items.product'])
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->whereHas('sale', fn($sale) => $sale->where('shop_id', auth()->user()->shop_id)))
            ->when($filters['return_status'], fn($q) => $q->where('return_status', $filters['return_status']))
            ->when($filters['return_type'], fn($q) => $q->where('return_type', $filters['return_type']))
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

        $totals = SaleReturn::selectRaw('
            count(*)               as total_returns,
            sum(subtotal)          as total_subtotal,
            sum(return_amount)     as total_return_amount
        ')->first();
        
        return view('sale_returns.index', compact('returns', 'filters', 'totals'));
    }

    public function create(Request $request)
    {
        $nextReference = SaleReturn::generateReference();
        $customers     = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products      = Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']);

        $sale = null;
        if ($request->filled('sale_id')) {
            $sale = Sale::with(['customer', 'items.product'])->find($request->sale_id);
            if ($sale) {
                $this->authorizeSaleShop($sale);
            }
        }

        return view('sale_returns.create', compact('nextReference', 'customers', 'products', 'sale'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateReturn($request);
        if (! empty($validated['sale_id'])) {
            $this->authorizeSaleShop(Sale::findOrFail($validated['sale_id']));
        }

        DB::transaction(function () use ($request, $validated) {
            $reference  = $validated['reference'] ?? SaleReturn::generateReference();
            $itemsInput = $request->input('items', []);

            $subtotal = collect($itemsInput)->sum(fn($i) => (float)$i['qty'] * (float)$i['price_on_sale']);
            $discount = (float)($validated['discount'] ?? 0);
            $returnAmount = max(0, $subtotal - $discount);

            $saleReturn = SaleReturn::create([
                'reference'      => $reference,
                'sale_id'        => $validated['sale_id'] ?? null,
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $discount,
                'subtotal'       => $subtotal,
                'return_amount'  => $returnAmount,
                'return_type'    => $validated['return_type'],
                'return_status'  => $validated['return_status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['qty'];
                $price     = (float) $item['price_on_sale'];
                $lineTotal = $qty * $price;

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id'   => $item['sale_item_id'] ?? null,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'price_on_sale'  => $price,
                    'line_total'     => $lineTotal,
                ]);
            }

            if ($saleReturn->return_status === 'approved') {
                $this->applyReturnEffects($saleReturn->fresh('items'));
            }
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Return created successfully.');
    }

    public function show(SaleReturn $saleReturn)
    {
        $this->authorizeReturnShop($saleReturn);
        $saleReturn->load(['sale', 'customer', 'items.product', 'items.saleItem']);

        return view('sale_returns.show', compact('saleReturn'));
    }

    public function edit(SaleReturn $saleReturn)
    {
        $this->authorizeReturnShop($saleReturn);
        $saleReturn->load(['items.product', 'items.saleItem', 'sale.items.product']);

        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'code', 'phone']);
        $products  = Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('sale_returns.edit', compact('saleReturn', 'customers', 'products'));
    }

    public function update(Request $request, SaleReturn $saleReturn)
    {
        $validated = $this->validateReturn($request, $saleReturn->id);
        $this->authorizeReturnShop($saleReturn);
        if (! empty($validated['sale_id'])) {
            $this->authorizeSaleShop(Sale::findOrFail($validated['sale_id']));
        }
        $oldStatus = $saleReturn->return_status;

        DB::transaction(function () use ($request, $saleReturn, $validated, $oldStatus) {
            if ($oldStatus === 'approved') {
                $this->reverseReturnEffects($saleReturn->load('items'));
            }

            $saleReturn->items()->delete();

            $itemsInput = $request->input('items', []);
            $subtotal   = collect($itemsInput)->sum(fn($i) => (float)$i['qty'] * (float)$i['price_on_sale']);
            $discount   = (float)($validated['discount'] ?? 0);
            $returnAmount = max(0, $subtotal - $discount);

            $saleReturn->update([
                'sale_id'        => $validated['sale_id'] ?? null,
                'customer_id'    => $validated['customer_id'] ?? null,
                'discount'       => $discount,
                'subtotal'       => $subtotal,
                'return_amount'  => $returnAmount,
                'return_type'    => $validated['return_type'],
                'return_status'  => $validated['return_status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'cash_memo'      => $validated['cash_memo'] ?? null,
                'note'           => $validated['note'] ?? null,
            ]);

            foreach ($itemsInput as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qty       = (float) $item['qty'];
                $price     = (float) $item['price_on_sale'];
                $lineTotal = $qty * $price;

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id'   => $item['sale_item_id'] ?? null,
                    'product_id'     => $product->id,
                    'qty'            => $qty,
                    'price_on_sale'  => $price,
                    'line_total'     => $lineTotal,
                ]);
            }

            if ($saleReturn->return_status === 'approved') {
                $this->applyReturnEffects($saleReturn->fresh('items'));
            } else {
                $this->syncSaleStatus($saleReturn);
            }
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Return updated successfully.');
    }

    public function destroy(SaleReturn $saleReturn)
    {
        $this->authorizeReturnShop($saleReturn);
        DB::transaction(function () use ($saleReturn) {
            $saleReturn->load('items');

            if ($saleReturn->return_status === 'approved') {
                $this->reverseReturnEffects($saleReturn);
            }

            $saleReturn->items()->delete();
            $saleReturn->delete();

            if ($saleReturn->sale_id) {
                $sale = Sale::find($saleReturn->sale_id);
                if ($sale) {
                    $hasApprovedReturns = SaleReturn::where('sale_id', $sale->id)
                        ->where('return_status', 'approved')
                        ->exists();

                    $sale->update([
                        'status' => $hasApprovedReturns ? 'returned' : 'success',
                    ]);
                }
            }
        });

        return redirect()->route('sale-returns.index')
            ->with('success', 'Return deleted successfully.');
    }

    public function approve(SaleReturn $saleReturn)
    {
        $this->authorizeReturnShop($saleReturn);
        if ($saleReturn->return_status === 'approved') {
            return back()->with('error', 'Return is already approved.');
        }

        DB::transaction(function () use ($saleReturn) {
            $saleReturn->load('items');
            $saleReturn->update(['return_status' => 'approved']);
            $this->applyReturnEffects($saleReturn);
        });

        return back()->with('success', 'Return approved successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'sale-returns-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $rows = SaleReturn::with(['sale', 'customer', 'items.product'])
            ->when(! auth()->user()->canManageAllShops(), fn($q) => $q->whereHas('sale', fn($sale) => $sale->where('shop_id', auth()->user()->shop_id)))
            ->when($request->return_status, fn($q) => $q->where('return_status', $request->return_status))
            ->when($request->return_type, fn($q) => $q->where('return_type', $request->return_type))
            ->latest()
            ->get();

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Reference',
                'Original Sale',
                'Customer',
                'Items',
                'Subtotal',
                'Discount',
                'Return Amount',
                'Return Type',
                'Return Status',
                'Payment Method',
                'Cash Memo',
                'Created At',
                'Note',
            ]);

            foreach ($rows as $row) {
                $itemsSummary = $row->items->map(fn($i) =>
                    ($i->product?->product_name ?? 'Unknown Product')
                    . ' x' . $i->qty
                    . ' @' . $i->price_on_sale
                )->implode(' | ');

                fputcsv($file, [
                    $row->reference,
                    $row->sale?->reference,
                    $row->customer?->full_name,
                    $itemsSummary,
                    $row->subtotal,
                    $row->discount,
                    $row->return_amount,
                    $row->return_type,
                    $row->return_status,
                    $row->payment_method,
                    $row->cash_memo,
                    $row->created_at?->format('Y-m-d'),
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

    private function applyReturnEffects(SaleReturn $ret): void
    {
        $ret->loadMissing(['items', 'sale']);

        foreach ($ret->items as $item) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $item->product_id, 'shop_id' => $ret->sale?->shop_id],
                ['stock_qty'  => 0]
            );

            $stock->increment('stock_qty', (float) $item->qty);
        }

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

        if ($ret->sale_id) {
            $sale = Sale::find($ret->sale_id);

            if ($sale) {
                $sale->update([
                    'status' => 'returned',
                ]);
            }
        }
    }

    private function reverseReturnEffects(SaleReturn $ret): void
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

                if (in_array($ret->return_type, ['refund', 'exchange'])) {
                    $customer->increment('total_paid', (float) $ret->return_amount);
                }

                $customer->recalculateDue();
            }
        }

        $this->syncSaleStatus($ret);
    }

    private function syncSaleStatus(SaleReturn $ret): void
    {
        if (! $ret->sale_id) {
            return;
        }

        $sale = Sale::find($ret->sale_id);

        if (! $sale) {
            return;
        }

        $hasApprovedReturns = SaleReturn::where('sale_id', $sale->id)
            ->where('return_status', 'approved')
            ->where('id', '!=', $ret->id)
            ->exists();

        $sale->update([
            'status' => $hasApprovedReturns ? 'returned' : 'success',
        ]);
    }

    private function validateReturn(Request $request, ?int $returnId = null): array
    {
        return $request->validate([
            'reference'             => 'nullable|string|max:50|unique:sale_returns,reference,' . $returnId,
            'sale_id'               => 'nullable|exists:sales,id',
            'customer_id'           => 'nullable|exists:customers,id',
            'discount'              => 'nullable|numeric|min:0',
            'return_type'           => 'required|in:refund,exchange,credit',
            'return_status'         => 'required|in:pending,approved,rejected',
            'payment_method'        => 'nullable|string|max:100',
            'cash_memo'             => 'nullable|string|max:100',
            'note'                  => 'nullable|string|max:2000',

            'items'                 => 'required|array|min:1',
            'items.*.sale_item_id'  => 'nullable|exists:sale_items,id',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.qty'           => 'required|numeric|min:0.01',
            'items.*.price_on_sale' => 'required|numeric|min:0',
        ]);
    }

    private function authorizeReturnShop(SaleReturn $saleReturn): void
    {
        if (auth()->user()->canManageAllShops()) {
            return;
        }

        $saleReturn->loadMissing('sale');
        abort_unless($saleReturn->sale?->shop_id === auth()->user()->shop_id, 403);
    }

    private function authorizeSaleShop(Sale $sale): void
    {
        if (auth()->user()->canManageAllShops()) {
            return;
        }

        abort_unless($sale->shop_id === auth()->user()->shop_id, 403);
    }
}
