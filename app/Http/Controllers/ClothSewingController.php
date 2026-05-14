<?php

namespace App\Http\Controllers;

use App\Models\ClothSewing;
use App\Models\Product;
use Illuminate\Http\Request;

class ClothSewingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $clothSewings = ClothSewing::query()
            ->with('product')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('tailor_name', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($product) => $product
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%"));
            }))
            ->latest('date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cloth_sewings.index', compact('clothSewings', 'search'));
    }

    public function create()
    {
        $clothSewing = new ClothSewing(['date' => now()->toDateString()]);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('cloth_sewings.create', compact('clothSewing', 'products'));
    }

    public function store(Request $request)
    {
        ClothSewing::create($this->validated($request));

        return redirect()->route('cloth-sewings.index')->with('success', 'Cloth sewing record added successfully.');
    }

    public function edit(ClothSewing $clothSewing)
    {
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('cloth_sewings.edit', compact('clothSewing', 'products'));
    }

    public function update(Request $request, ClothSewing $clothSewing)
    {
        $clothSewing->update($this->validated($request));

        return redirect()->route('cloth-sewings.index')->with('success', 'Cloth sewing record updated successfully.');
    }

    public function destroy(ClothSewing $clothSewing)
    {
        $clothSewing->delete();

        return redirect()->route('cloth-sewings.index')->with('success', 'Cloth sewing record deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'tailor_name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'item_qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);
    }
}
