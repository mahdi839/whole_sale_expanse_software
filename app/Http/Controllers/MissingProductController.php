<?php

namespace App\Http\Controllers;

use App\Models\MissingProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MissingProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'product_id' => $request->input('product_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = MissingProduct::query()
            ->with('product')
            ->when($filters['product_id'], fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->when($filters['search'], function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where(function ($sub) use ($search) {
                    $sub->where('note', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($product) => $product
                            ->where('product_name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%"));
                });
            });

        $missingProducts = (clone $query)->latest('date')->latest('id')->paginate(15)->withQueryString();
        $totalMissingQty = (clone $query)->sum('missing_qty');
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('missing_products.index', compact('missingProducts', 'filters', 'products', 'totalMissingQty'));
    }

    public function create()
    {
        $missingProduct = new MissingProduct(['date' => now()->toDateString()]);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('missing_products.create', compact('missingProduct', 'products'));
    }

    public function store(Request $request)
    {
        MissingProduct::create($this->validated($request));

        return redirect()->route('missing-products.index')->with('success', 'Missing product entry saved successfully.');
    }

    public function edit(MissingProduct $missingProduct)
    {
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('missing_products.edit', compact('missingProduct', 'products'));
    }

    public function update(Request $request, MissingProduct $missingProduct)
    {
        $missingProduct->update($this->validated($request, $missingProduct));

        return redirect()->route('missing-products.index')->with('success', 'Missing product entry updated successfully.');
    }

    public function destroy(MissingProduct $missingProduct)
    {
        if ($missingProduct->document) {
            Storage::disk('public')->delete($missingProduct->document);
        }

        $missingProduct->delete();

        return redirect()->route('missing-products.index')->with('success', 'Missing product entry deleted successfully.');
    }

    private function validated(Request $request, ?MissingProduct $missingProduct = null): array
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'missing_qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:5120',
        ]);

        if ($request->hasFile('document')) {
            if ($missingProduct?->document) {
                Storage::disk('public')->delete($missingProduct->document);
            }
            $data['document'] = $request->file('document')->store('missing-products', 'public');
        } else {
            unset($data['document']);
        }

        return $data;
    }
}
