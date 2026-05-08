<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::withCount(['users', 'sales'])->latest()->paginate(15);

        return view('shops.index', compact('shops'));
    }

    public function create()
    {
        return view('shops.create', ['shop' => new Shop()]);
    }

    public function store(Request $request)
    {
        Shop::create($this->validateShop($request));

        return redirect()->route('shops.index')->with('success', 'Shop created successfully.');
    }

    public function show(Request $request, Shop $shop)
    {
        $shop->load(['users.roles']);

        $search = $request->input('search');
        $stocks = $shop->stocks()
            ->with('product')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('product', function ($product) use ($search) {
                    $product->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->orderBy('products.product_name')
            ->select('stocks.*')
            ->paginate(15)
            ->withQueryString();

        return view('shops.show', compact('shop', 'stocks', 'search'));
    }

    public function edit(Shop $shop)
    {
        return view('shops.edit', compact('shop'));
    }

    public function update(Request $request, Shop $shop)
    {
        $shop->update($this->validateShop($request, $shop->id));

        return redirect()->route('shops.index')->with('success', 'Shop updated successfully.');
    }

    public function destroy(Shop $shop)
    {
        if ($shop->users()->exists() || $shop->sales()->exists()) {
            return back()->with('error', 'This shop has assigned users or sales and cannot be deleted.');
        }

        $shop->delete();

        return redirect()->route('shops.index')->with('success', 'Shop deleted successfully.');
    }

    public function executives(Shop $shop)
    {
        return view('shops.executives', [
            'shop' => $shop,
            'users' => User::with(['roles', 'shop'])->orderBy('name')->get(),
        ]);
    }

    public function syncExecutives(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        User::where('shop_id', $shop->id)->update(['shop_id' => null]);
        User::whereIn('id', $validated['user_ids'] ?? [])->update(['shop_id' => $shop->id]);

        return redirect()->route('shops.show', $shop)->with('success', 'Shop executives updated successfully.');
    }

    private function validateShop(Request $request, ?int $shopId = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:shops,code,' . $shopId,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
