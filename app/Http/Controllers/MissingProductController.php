<?php

namespace App\Http\Controllers;

use App\Models\MissingProduct;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\SimplePdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class MissingProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->filteredQuery($filters);

        $missingProducts = (clone $query)->latest('date')->latest('id')->paginate(15)->withQueryString();
        $totalMissingQty = (clone $query)->sum('missing_qty');
        $totalPurchaseValue = (clone $query)->sum('purchase_value');
        [$products, $suppliers] = $this->formOptions();

        return view('missing_products.index', compact(
            'missingProducts',
            'filters',
            'products',
            'suppliers',
            'totalMissingQty',
            'totalPurchaseValue',
        ));
    }

    public function create()
    {
        $missingProduct = new MissingProduct(['date' => now()->toDateString()]);
        [$products, $suppliers] = $this->formOptions();

        return view('missing_products.create', compact('missingProduct', 'products', 'suppliers'));
    }

    public function store(Request $request)
    {
        MissingProduct::create($this->validated($request));

        return redirect()->route('missing-products.index')->with('success', 'Missing product entry saved successfully.');
    }

    public function edit(MissingProduct $missingProduct)
    {
        [$products, $suppliers] = $this->formOptions();

        return view('missing_products.edit', compact('missingProduct', 'products', 'suppliers'));
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

    public function exportPdf(Request $request)
    {
        $filters = $this->filters($request);
        $entries = $this->filteredQuery($filters)->latest('date')->latest('id')->get();

        $rows = $entries->map(fn (MissingProduct $entry) => [
            optional($entry->date)->format('Y-m-d'),
            $entry->supplier?->name ?? '-',
            $entry->product?->product_name ?? '-',
            $entry->product?->sku ?: ($entry->product?->product_code ?: '-'),
            number_format((float) $entry->missing_qty, 2),
            number_format((float) $entry->purchase_rate, 2),
            number_format((float) $entry->purchase_value, 2),
            $entry->note ?: '-',
        ]);

        $subtitleParts = [];
        if ($filters['search']) {
            $subtitleParts[] = 'Search: '.$filters['search'];
        }
        if ($filters['supplier_id']) {
            $subtitleParts[] = 'Supplier: '.(Supplier::find($filters['supplier_id'])?->name ?? $filters['supplier_id']);
        }
        if ($filters['product_id']) {
            $subtitleParts[] = 'Product: '.(Product::find($filters['product_id'])?->displayLabel() ?? $filters['product_id']);
        }
        if ($filters['date_from'] || $filters['date_to']) {
            $subtitleParts[] = 'Date: '.($filters['date_from'] ?: '...').' to '.($filters['date_to'] ?: '...');
        }

        $fileName = 'missing-products-'.now()->format('Y-m-d-H-i-s').'.pdf';

        return Response::make(SimplePdf::table('Inaya Creation - Missing Products', [
            'Date', 'Supplier', 'Product', 'Design Code', 'Missing Qty', 'Purchase Rate', 'Purchase Value', 'Note',
        ], $rows, null, [
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
            'subtitle' => $subtitleParts ? implode(' | ', $subtitleParts) : 'All missing product entries',
            'summary' => [
                ['label' => 'Entries', 'value' => (string) $entries->count(), 'tone' => 'indigo'],
                ['label' => 'Missing Qty', 'value' => number_format((float) $entries->sum('missing_qty'), 2), 'tone' => 'rose'],
                ['label' => 'Purchase Value', 'value' => number_format((float) $entries->sum('purchase_value'), 2), 'tone' => 'amber'],
            ],
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function filters(Request $request): array
    {
        return [
            'search' => $request->input('search'),
            'supplier_id' => $request->input('supplier_id'),
            'product_id' => $request->input('product_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
    }

    private function filteredQuery(array $filters): Builder
    {
        return MissingProduct::query()
            ->with(['product', 'supplier'])
            ->when($filters['supplier_id'], fn ($q) => $q->where('supplier_id', $filters['supplier_id']))
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
                            ->orWhere('product_code', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn ($supplier) => $supplier
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            });
    }

    private function formOptions(): array
    {
        return [
            Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code', 'purchase_price']),
            Supplier::orderBy('name')->get(['id', 'name', 'code', 'phone']),
        ];
    }

    private function validated(Request $request, ?MissingProduct $missingProduct = null): array
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'missing_qty' => 'required|numeric|min:0.01',
            'purchase_rate' => 'required|numeric|min:0',
            'purchase_value' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:5120',
        ]);

        $data['purchase_value'] = round((float) $data['missing_qty'] * (float) $data['purchase_rate'], 2);

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
