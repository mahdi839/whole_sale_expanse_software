<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    private const IMAGE_MAX_KILOBYTES = 2048;

    public function index(Request $request)
    {
        $query = Product::with(['stock', 'stocks.shop']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $suggestedProducts = Product::orderBy('product_name')->limit(100)->get(['product_name', 'sku', 'product_code']);
        $shops = Shop::orderBy('name')->get(['id', 'name', 'code']);

        return view('products.index', compact('products', 'search', 'shops', 'suggestedProducts'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $products = Product::with(['stock', 'stocks.shop'])
            ->when($search, fn ($query) => $query->where(function ($match) use ($search) {
                $match->where('product_name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            }))
            ->orderBy('product_name')
            ->get();

        $shops = Shop::query()
            ->when(! auth()->user()->canManageAllShops(), fn ($query) => $query->whereKey(auth()->user()->shop_id ?: -1))
            ->orderBy('name')
            ->get(['id', 'name']);
        $headers = array_merge(['Product Name', 'Design Code', 'Sale Price'], $shops->pluck('name')->all(), ['Stock', 'Central Stock']);
        $rows = $products->map(function (Product $product) use ($shops) {
            $central = (float) ($product->stock?->stock_qty ?? 0);
            $shopValues = $shops->map(fn (Shop $shop) => (float) ($product->stocks->firstWhere('shop_id', $shop->id)?->stock_qty ?? 0));

            return array_merge([
                $product->product_name,
                $product->sku ?: ($product->product_code ?: '-'),
                number_format((float) $product->selling_price, 2),
            ], $shopValues->all(), [
                $central + (float) $shopValues->sum(),
                $central,
            ]);
        });

        $fileName = 'products-'.now()->format('Y-m-d-H-i-s').'.pdf';

        return Response::make(SimplePdf::table('Inaya Creation - Products', $headers, $rows, null, [
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());
        if (! auth()->user()->hasRole('Super Admin') && ! auth()->user()->is_admin) {
            $validated['purchase_price'] = 0;
        }

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
        $validated = $request->validate($this->rules($product), $this->messages());
        if (! auth()->user()->hasRole('Super Admin') && ! auth()->user()->is_admin) {
            unset($validated['purchase_price']);
        }

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
            $code = $this->twoDigitRandom().$middleCode.$this->twoDigitRandom();
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

    private function rules(?Product $product = null): array
    {
        $skuRule = 'required|string|max:100|unique:products,sku';

        if ($product) {
            $skuRule .= ','.$product->id;
        }

        return [
            'product_name' => 'required|string|max:255',
            'sku' => $skuRule,
            'product_code' => 'nullable|string|max:96',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:'.self::IMAGE_MAX_KILOBYTES,
            'stock_qty' => 'required|numeric|min:0',
        ];
    }

    private function messages(): array
    {
        return [
            'image.image' => 'Please upload a valid product image.',
            'image.mimes' => 'Product image must be a JPG, PNG, or WEBP file.',
            'image.max' => 'Product image must be 2 MB or smaller.',
        ];
    }
}
