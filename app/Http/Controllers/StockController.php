<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Stock::with('product')->latest()->get();
        return $stocks;
        return view('stocks.index', compact('stocks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('stocks.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'stock_qty'  => 'required|integer|min:0',
        ]);

        Stock::create([
            'product_id' => $request->product_id,
            'stock_qty'  => $request->stock_qty,
        ]);

        return redirect()->route('stocks.index')
                         ->with('success', 'Stock created successfully');
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
        $stock = Stock::findOrFail($id);
        return view('stocks.edit', compact('stock'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $stock = Stock::findOrFail($id);

        $request->validate([
            'product_id' => 'required|integer',
            'stock_qty'  => 'required|integer|min:0',
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
        $stock = Stock::findOrFail($id);
        $stock->delete();

        return redirect()->route('stocks.index')
                         ->with('success', 'Stock deleted successfully');
    }
}