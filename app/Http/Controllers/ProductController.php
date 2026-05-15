<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('stock');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'search'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'product_code' => 'nullable|string|max:96',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'stock_qty' => 'required|numeric|min:0',
        ]);

        $validated['product_code'] = $this->generatedProductCode($validated['product_code'] ?? null);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        // Create the stock record
        $product->stock()->create([
            'stock_qty' => $validated['stock_qty'],
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($product->load('stock'), 201);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function barcode(Product $product)
    {
        return view('products.barcode', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,'.$product->id,
            'product_code' => 'nullable|string|max:96',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'stock_qty' => 'required|numeric|min:0',
        ]);

        if (($validated['product_code'] ?? null) !== $product->product_code) {
            $validated['product_code'] = $this->generatedProductCode($validated['product_code'] ?? null, $product->id);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($validated['image']);
        }

        $product->update($validated);

        // Update or create the stock record
        $product->stock()->updateOrCreate(
            ['product_id' => $product->id],
            ['stock_qty' => $validated['stock_qty']]
        );

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->purchaseItems()->exists()) {
        return redirect()
            ->route('products.index')
            ->with('error', 'This product cannot be deleted because it is used in purchases.');
        }
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    private function generatedProductCode(?string $middleCode, ?int $ignoreProductId = null): ?string
    {
        $middleCode = trim((string) $middleCode);

        if ($middleCode === '') {
            return null;
        }

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $code = $this->twoDigitRandom() . $middleCode . $this->twoDigitRandom();
            $exists = Product::where('product_code', $code)
                ->when($ignoreProductId, fn ($query) => $query->whereKeyNot($ignoreProductId))
                ->exists();

            if (! $exists) {
                return $code;
            }
        }

        throw ValidationException::withMessages([
            'product_code' => 'Could not generate a unique product code. Please try again.',
        ]);
    }

    private function twoDigitRandom(): string
    {
        return str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
    }
}
