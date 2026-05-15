<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ReceivedCloth;
use App\Models\Tailor;
use Illuminate\Http\Request;

class ReceivedClothController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $receivedCloths = ReceivedCloth::query()
            ->with(['product', 'tailor'])
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('tailor_name', 'like', "%{$search}%")
                    ->orWhereHas('tailor', fn ($tailor) => $tailor->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($product) => $product
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%"));
            }))
            ->latest('date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('received_cloths.index', compact('receivedCloths', 'search'));
    }

    public function create()
    {
        $receivedCloth = new ReceivedCloth(['date' => now()->toDateString()]);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('received_cloths.create', compact('receivedCloth', 'products'));
    }

    public function store(Request $request)
    {
        foreach ($this->validatedRows($request) as $row) {
            ReceivedCloth::create($row);
        }

        return redirect()->route('received-cloths.index')->with('success', 'Received cloth records added successfully.');
    }

    public function edit(ReceivedCloth $receivedCloth)
    {
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('received_cloths.edit', compact('receivedCloth', 'products'));
    }

    public function update(Request $request, ReceivedCloth $receivedCloth)
    {
        $receivedCloth->update($this->validatedSingle($request));

        return redirect()->route('received-cloths.index')->with('success', 'Received cloth record updated successfully.');
    }

    public function destroy(ReceivedCloth $receivedCloth)
    {
        $receivedCloth->delete();

        return redirect()->route('received-cloths.index')->with('success', 'Received cloth record deleted successfully.');
    }

    private function validatedRows(Request $request): array
    {
        $data = $request->validate([
            'tailor_name' => 'required|string|max:255',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.item_qty' => 'required|numeric|min:0.01',
        ]);

        $tailor = Tailor::firstOrCreate(['name' => trim($data['tailor_name'])]);

        return collect($data['items'])->map(fn ($item) => [
            'tailor_name' => $tailor->name,
            'tailor_id' => $tailor->id,
            'product_id' => $item['product_id'],
            'item_qty' => $item['item_qty'],
            'date' => $data['date'],
        ])->all();
    }

    private function validatedSingle(Request $request): array
    {
        $data = $request->validate([
            'tailor_name' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'item_qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        $tailor = Tailor::firstOrCreate(['name' => trim($data['tailor_name'])]);
        $data['tailor_id'] = $tailor->id;
        $data['tailor_name'] = $tailor->name;

        return $data;
    }
}
