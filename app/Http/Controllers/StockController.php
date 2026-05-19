<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $centralStocks = Stock::with('product')->central()->latest()->get();
        $shopStocks = Stock::with(['product', 'shop'])->whereNotNull('shop_id')->latest()->get();
        $centralStockValue = $centralStocks->sum(fn ($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0));
        $shopStockValue = $shopStocks->sum(fn ($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0));

        return view('stocks.index', compact('centralStocks', 'shopStocks', 'centralStockValue', 'shopStockValue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stocks.create', [
            'products' => Product::orderBy('product_name')->get(['id', 'product_name', 'sku']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock_qty'  => 'required|numeric|min:0',
        ]);

        Stock::updateOrCreate(
            ['product_id' => $request->product_id, 'shop_id' => null],
            ['stock_qty' => $request->stock_qty]
        );

        return redirect()->route('stocks.index')
                         ->with('success', 'Stock created successfully');
    }

    public function distribute()
    {
        return view('stocks.distribute', [
            'products' => Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']),
            'shops' => Shop::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $qty = (float) $item['qty'];
                $central = Stock::where('product_id', $item['product_id'])
                    ->whereNull('shop_id')
                    ->lockForUpdate()
                    ->first();

                if (! $central || (float) $central->stock_qty < $qty) {
                    throw ValidationException::withMessages([
                        'items' => 'Not enough central stock for one or more selected products.',
                    ]);
                }

                $central->decrement('stock_qty', $qty);

                $shopStock = Stock::firstOrCreate(
                    ['product_id' => $item['product_id'], 'shop_id' => $validated['shop_id']],
                    ['stock_qty' => 0]
                );
                $shopStock->increment('stock_qty', $qty);
            }
        });

        return redirect()->route('stocks.index')->with('success', 'Stock distributed to shop successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stock = Stock::findOrFail($id);
        return view('stocks.show', compact('stock'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stock = Stock::central()->findOrFail($id);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku']);

        return view('stocks.edit', compact('stock', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stock = Stock::central()->findOrFail($id);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock_qty'  => 'required|numeric|min:0',
        ]);

        $stock->update([
            'product_id' => $request->product_id,
            'stock_qty'  => $request->stock_qty,
        ]);

        return redirect()->route('stocks.index')
                         ->with('success', 'Stock updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stock = Stock::central()->findOrFail($id);
        $stock->delete();

        return redirect()->route('stocks.index')
                         ->with('success', 'Stock deleted successfully');
    }
}
