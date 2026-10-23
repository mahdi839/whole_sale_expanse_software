<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\StockDistribution;
use App\Models\StockDistributionItem;
use App\Models\StockMovement;
use App\Services\StockLedger;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $centralStocks = Stock::with('product')->central()->latest()->get();
        $shopStocks = Stock::with(['product', 'shop'])->forExistingShop()->latest()->get();
        $distributions = StockDistribution::with(['shop', 'items.product', 'receivedBy'])->whereHas('shop')->latest()->get();
        $centralStockValue = $centralStocks->sum(fn ($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0));
        $shopStockValue = $shopStocks->sum(fn ($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0));

        return view('stocks.index', compact('centralStocks', 'shopStocks', 'distributions', 'centralStockValue', 'shopStockValue'));
    }

    public function history(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'type' => (string) $request->input('type', ''),
            'shop_id' => (string) $request->input('shop_id', ''),
            'range' => (string) $request->input('range', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        if (! array_key_exists($filters['range'], ['today' => 1, '7d' => 1, 'month' => 1, 'all' => 1, 'custom' => 1])) {
            $filters['range'] = 'all';
        }

        if ($filters['range'] !== 'custom') {
            [$filters['date_from'], $filters['date_to']] = $this->historyRange($filters['range']);
        }

        if (! array_key_exists($filters['type'], StockMovement::filterOptions())) {
            $filters['type'] = '';
        }

        $query = StockMovement::query()->with(['product', 'shop', 'user']);

        if (! auth()->user()->canManageAllShops()) {
            $query->where('shop_id', auth()->user()->shop_id ?: -1);
        } elseif ($filters['shop_id'] === 'central') {
            $query->whereNull('shop_id');
        } elseif ($filters['shop_id'] !== '') {
            $query->where('shop_id', $filters['shop_id']);
        }

        if ($filters['type'] !== '') {
            $query->whereIn('movement_type', StockMovement::typesForFilter($filters['type']));
        }

        if ($filters['date_from']) {
            $query->where('occurred_at', '>=', $filters['date_from'].' 00:00:00');
        }

        if ($filters['date_to']) {
            $query->where('occurred_at', '<=', $filters['date_to'].' 23:59:59');
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function ($inner) use ($search) {
                $inner->where('reference', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($product) use ($search) {
                        $product->where('product_name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('product_code', 'like', "%{$search}%");
                    });
            });
        }

        $summary = [
            'count' => (clone $query)->count(),
            'in' => (float) (clone $query)->where('direction', 'in')->sum('qty'),
            'out' => (float) (clone $query)->where('direction', 'out')->sum('qty'),
        ];
        $summary['net'] = $summary['in'] - $summary['out'];

        $movements = $query->orderByDesc('occurred_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $shops = auth()->user()->canManageAllShops()
            ? Shop::orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        return view('stocks.history', compact('movements', 'filters', 'summary', 'shops'));
    }

    public function exportPdf()
    {
        $centralStocks = Stock::with('product')->central()->latest()->get();
        $shopStocks = Stock::with(['product', 'shop'])->forExistingShop()->latest()->get();
        $distributions = StockDistribution::with(['shop', 'items.product', 'receivedBy'])
            ->whereHas('shop')
            ->latest()
            ->get();

        $centralStockValue = $centralStocks->sum(
            fn ($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0)
        );
        $shopStockValue = $shopStocks->sum(
            fn ($stock) => (float) $stock->stock_qty * (float) ($stock->product?->purchase_price ?? 0)
        );

        $sections = [
            [
                'title' => 'Central Inventory',
                'headers' => ['#', 'Product Name', 'Design Code', 'Stock Qty', 'Created'],
                'rows' => $centralStocks->values()->map(fn ($stock, $index) => [
                    $index + 1,
                    $stock->product?->product_name ?? 'Product #'.$stock->product_id,
                    $stock->product?->sku ?? '-',
                    number_format((float) $stock->stock_qty, 2),
                    $stock->created_at?->format('d M Y') ?? '-',
                ]),
                'widths' => [35, 300, 160, 110, 165],
            ],
            [
                'title' => 'Shop Inventory',
                'headers' => ['#', 'Shop', 'Product', 'Design Code', 'Qty'],
                'rows' => $shopStocks->values()->map(fn ($stock, $index) => [
                    $index + 1,
                    $stock->shop?->name ?? '-',
                    $stock->product?->product_name ?? 'Product #'.$stock->product_id,
                    $stock->product?->sku ?? '-',
                    number_format((float) $stock->stock_qty, 2),
                ]),
                'widths' => [35, 180, 270, 170, 115],
            ],
            [
                'title' => 'Shop Distribution History',
                'headers' => ['Date & Time', 'Shop', 'Distributor', 'Carry Man', 'Receiver', 'Products', 'Qty', 'Status', 'Note'],
                'rows' => $distributions->map(fn ($distribution) => [
                    trim(($distribution->distribution_date?->format('d M Y') ?? '-').' '.($distribution->created_at?->format('h:i A') ?? '')),
                    $distribution->shop?->name ?? '-',
                    $distribution->distributor ?: '-',
                    $distribution->carry_man ?: '-',
                    $distribution->receiver ?: 'Pending receive',
                    $distribution->items->map(
                        fn ($item) => ($item->product?->product_name ?? 'Product #'.$item->product_id)
                            .' x '.number_format((float) $item->qty, 2)
                    )->implode(' | '),
                    number_format((float) $distribution->items->sum('qty'), 2),
                    ucfirst($distribution->status),
                    $distribution->action_note ?: '-',
                ]),
                'widths' => [75, 75, 80, 70, 80, 180, 55, 60, 95],
            ],
        ];

        $fileName = 'stock-inventory-'.now()->format('Y-m-d-H-i-s').'.pdf';

        return Response::make(SimplePdf::tables('Inaya Creation - Stock Inventory', $sections, [
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
            'summary' => [
                ['label' => 'Central Items', 'value' => number_format($centralStocks->count())],
                ['label' => 'Central Qty', 'value' => number_format((float) $centralStocks->sum('stock_qty'), 2)],
                ['label' => 'Shop Qty', 'value' => number_format((float) $shopStocks->sum('stock_qty'), 2)],
                ['label' => 'Central Value', 'value' => 'BDT '.number_format($centralStockValue, 2)],
                ['label' => 'Shop Value', 'value' => 'BDT '.number_format($shopStockValue, 2)],
            ],
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
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
            'stock_qty' => 'required|numeric|min:0',
        ]);

        $productId = (int) $request->product_id;
        $newQty = (float) $request->stock_qty;
        $existing = Stock::where('product_id', $productId)->whereNull('shop_id')->first();

        Stock::updateOrCreate(
            ['product_id' => $productId, 'shop_id' => null],
            ['stock_qty' => $newQty]
        );

        app(StockLedger::class)->recordQuantityChange($productId, null, (float) ($existing->stock_qty ?? 0), $newQty, [
            'note' => 'Central stock updated',
            'source_type' => 'stock',
            'source_id' => $existing?->id,
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock created successfully');
    }

    public function distribute()
    {
        return view('stocks.distribute', [
            'products' => Product::with('stock')->orderBy('product_name')->get(['id', 'product_name', 'sku']),
            'shops' => auth()->user()->canManageAllShops() ? Shop::where('is_active', true)->orderBy('name')->get() : collect([auth()->user()->shop]),
            'distributions' => StockDistribution::with(['shop', 'items.product', 'receivedBy'])
                ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
                ->latest()->get(),
        ]);
    }

    public function adjustments()
    {
        return view('stocks.adjustments', [
            'products' => Product::orderBy('product_name')->get(['id', 'product_name', 'sku']),
            'shops' => Shop::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'stocks' => Stock::with(['product', 'shop'])->forExistingLocation()->latest()->limit(25)->get(),
        ]);
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'location_type' => 'required|in:central,shop',
            'shop_id' => 'nullable|required_if:location_type,shop|exists:shops,id',
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:plus,minus',
            'qty' => 'required|numeric|min:0.01',
        ], [], [
            'shop_id' => 'shop',
            'product_id' => 'product',
            'adjustment_type' => 'adjustment',
            'qty' => 'quantity',
        ]);

        DB::transaction(function () use ($validated) {
            $shopId = $validated['location_type'] === 'shop' ? $validated['shop_id'] : null;
            $stock = Stock::where('product_id', $validated['product_id'])
                ->where('shop_id', $shopId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = Stock::create([
                    'product_id' => $validated['product_id'],
                    'shop_id' => $shopId,
                    'stock_qty' => 0,
                ]);
            }

            $qty = (float) $validated['qty'];
            $ledger = app(StockLedger::class);
            $meta = [
                'note' => 'Manual stock adjustment',
                'source_type' => 'stock',
                'source_id' => $stock->id,
            ];

            if ($validated['adjustment_type'] === 'minus') {
                if ((float) $stock->stock_qty < $qty) {
                    throw ValidationException::withMessages([
                        'qty' => 'Not enough stock to minus this quantity.',
                    ]);
                }

                $stock->decrement('stock_qty', $qty);
                $ledger->record((int) $validated['product_id'], $shopId, 'adjustment', 'out', $qty, $meta);

                return;
            }

            $stock->increment('stock_qty', $qty);
            $ledger->record((int) $validated['product_id'], $shopId, 'adjustment', 'in', $qty, $meta);
        });

        return redirect()->route('stocks.adjustments')->with('success', 'Stock adjusted successfully.');
    }

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'from_shop_id' => 'required|exists:shops,id|different:to_shop_id',
            'destination_type' => 'nullable|in:shop,central',
            'to_shop_id' => 'nullable|required_unless:destination_type,central|exists:shops,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0.01',
        ], [], [
            'from_shop_id' => 'from shop',
            'destination_type' => 'destination',
            'to_shop_id' => 'destination shop',
            'product_id' => 'product',
            'qty' => 'quantity',
        ]);

        DB::transaction(function () use ($validated) {
            $qty = (float) $validated['qty'];
            $destinationType = $validated['destination_type'] ?? 'shop';
            $fromStock = Stock::where('product_id', $validated['product_id'])
                ->where('shop_id', $validated['from_shop_id'])
                ->lockForUpdate()
                ->first();

            if (! $fromStock || (float) $fromStock->stock_qty < $qty) {
                throw ValidationException::withMessages([
                    'qty' => 'Not enough stock in the source shop.',
                ]);
            }

            $toStock = Stock::firstOrCreate(
                [
                    'product_id' => $validated['product_id'],
                    'shop_id' => $destinationType === 'central' ? null : $validated['to_shop_id'],
                ],
                ['stock_qty' => 0]
            );

            $fromStock->decrement('stock_qty', $qty);
            $toStock->increment('stock_qty', $qty);

            $fromShop = Shop::whereKey($validated['from_shop_id'])->value('name') ?: 'shop';
            $toLabel = $destinationType === 'central'
                ? 'Central'
                : (Shop::whereKey($validated['to_shop_id'])->value('name') ?: 'shop');
            $note = 'From '.$fromShop.' to '.$toLabel;
            $ledger = app(StockLedger::class);
            $ledger->record((int) $validated['product_id'], (int) $validated['from_shop_id'], 'transfer', 'out', $qty, [
                'note' => $note,
                'source_type' => 'stock',
                'source_id' => $fromStock->id,
            ]);
            $ledger->record(
                (int) $validated['product_id'],
                $destinationType === 'central' ? null : (int) $validated['to_shop_id'],
                'transfer',
                'in',
                $qty,
                [
                    'note' => $note,
                    'source_type' => 'stock',
                    'source_id' => $toStock->id,
                ]
            );
        });

        return redirect()->route('stocks.adjustments')->with('success', 'Stock transferred successfully.');
    }

    public function storeDistribution(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'distributor' => 'required|string|max:255',
            'carry_man' => 'required|string|max:255',
            'distribution_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
        ], [
            'items.required' => 'Please add at least one product to distribute.',
            'items.min' => 'Please add at least one product to distribute.',
            'items.*.product_id.required' => 'Please select a product for every row.',
            'items.*.product_id.exists' => 'One of the selected products is invalid. Please select it again.',
            'items.*.qty.required' => 'Please enter quantity for every selected product.',
            'items.*.qty.numeric' => 'Product quantity must be a valid number.',
            'items.*.qty.min' => 'Product quantity must be at least 0.01.',
        ], [
            'shop_id' => 'shop',
            'carry_man' => 'carry man',
            'distribution_date' => 'date',
            'items.*.product_id' => 'product',
            'items.*.qty' => 'quantity',
        ]);

        if (auth()->user()->canManageAllShops()) {
            abort_unless(! empty($validated['shop_id']), 422, 'Please select a shop.');
        } else {
            abort_unless(auth()->user()->shop_id, 403, 'No shop assigned to your user.');
            $validated['shop_id'] = auth()->user()->shop_id;
        }

        DB::transaction(function () use ($validated) {
            $distribution = StockDistribution::create([
                'shop_id' => $validated['shop_id'],
                'distributor' => $validated['distributor'],
                'carry_man' => $validated['carry_man'],
                'distribution_date' => $validated['distribution_date'],
                'status' => 'pending',
            ]);

            $shopName = Shop::whereKey($validated['shop_id'])->value('name') ?: 'shop';
            $reference = 'DST-'.str_pad((string) $distribution->id, 6, '0', STR_PAD_LEFT);
            $ledger = app(StockLedger::class);

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

                $distributionItem = StockDistributionItem::create([
                    'stock_distribution_id' => $distribution->id,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                ]);

                $ledger->record((int) $item['product_id'], null, 'distribution', 'out', $qty, [
                    'reference' => $reference,
                    'note' => 'Sent to '.$shopName,
                    'source_type' => 'stock_distribution_item',
                    'source_id' => $distributionItem->id,
                    'link_type' => 'stock_distribution',
                    'link_id' => $distribution->id,
                ]);
            }
        });

        return back()->with('success', 'Stock distribution is pending approval.');
    }

    public function pendingDistributions()
    {
        $distributions = StockDistribution::with(['shop', 'items.product'])
            ->where('status', 'pending')
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->latest()
            ->get();

        return view('stocks.pending-distributions', compact('distributions'));
    }

    public function receiveDistribution(Request $request, StockDistribution $distribution)
    {
        abort_unless(auth()->user()->canManageAllShops() || $distribution->shop_id === auth()->user()->shop_id, 403);
        if ($distribution->status !== 'pending') {
            return redirect()->route('stocks.distributions.pending')->with('success', 'This stock distribution is already processed.');
        }

        $validated = $request->validate([
            'action_note' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($distribution, $validated) {
            $distribution->load(['items', 'shop']);
            $reference = 'DST-'.str_pad((string) $distribution->id, 6, '0', STR_PAD_LEFT);
            $shopName = $distribution->shop?->name ?: 'shop';
            $ledger = app(StockLedger::class);

            foreach ($distribution->items as $item) {
                $shopStock = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'shop_id' => $distribution->shop_id],
                    ['stock_qty' => 0]
                );
                $shopStock->increment('stock_qty', (float) $item->qty);
                $ledger->record((int) $item->product_id, (int) $distribution->shop_id, 'distribution_receive', 'in', (float) $item->qty, [
                    'reference' => $reference,
                    'note' => 'Received into '.$shopName,
                    'source_type' => 'stock_distribution_item',
                    'source_id' => $item->id,
                    'link_type' => 'stock_distribution',
                    'link_id' => $distribution->id,
                ]);
            }

            $distribution->update([
                'status' => 'received',
                'receiver' => auth()->user()?->name,
                'action_note' => $validated['action_note'] ?? null,
                'received_at' => now(),
                'received_by' => auth()->id(),
            ]);
        });

        return redirect()->route('stocks.distributions.pending')->with('success', 'Stock received and added to shop inventory.');
    }

    public function cancelDistribution(Request $request, StockDistribution $distribution)
    {
        abort_unless(auth()->user()->canManageAllShops() || $distribution->shop_id === auth()->user()->shop_id, 403);
        if ($distribution->status !== 'pending') {
            return redirect()->route('stocks.distributions.pending')->with('success', 'This stock distribution is already processed.');
        }

        $validated = $request->validate([
            'action_note' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($distribution, $validated) {
            $distribution->load('items');
            $reference = 'DST-'.str_pad((string) $distribution->id, 6, '0', STR_PAD_LEFT);
            $ledger = app(StockLedger::class);

            foreach ($distribution->items as $item) {
                $central = Stock::firstOrCreate(
                    ['product_id' => $item->product_id, 'shop_id' => null],
                    ['stock_qty' => 0]
                );
                $central->increment('stock_qty', (float) $item->qty);
                $ledger->record((int) $item->product_id, null, 'distribution_cancel', 'in', (float) $item->qty, [
                    'reference' => $reference,
                    'note' => 'Returned to central inventory',
                    'source_type' => 'stock_distribution_item',
                    'source_id' => $item->id,
                    'link_type' => 'stock_distribution',
                    'link_id' => $distribution->id,
                ]);
            }

            $distribution->update([
                'status' => 'cancelled',
                'action_note' => $validated['action_note'] ?? null,
                'received_at' => now(),
                'received_by' => auth()->id(),
            ]);
        });

        return redirect()->route('stocks.distributions.pending')->with('success', 'Stock distribution cancelled and returned to central inventory.');
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
            'stock_qty' => 'required|numeric|min:0',
        ]);

        $oldProductId = (int) $stock->product_id;
        $oldQty = (float) $stock->stock_qty;
        $newProductId = (int) $request->product_id;
        $newQty = (float) $request->stock_qty;

        $stock->update([
            'product_id' => $newProductId,
            'stock_qty' => $newQty,
        ]);

        $ledger = app(StockLedger::class);
        if ($oldProductId === $newProductId) {
            $ledger->recordQuantityChange($newProductId, null, $oldQty, $newQty, [
                'note' => 'Central stock updated',
                'source_type' => 'stock',
                'source_id' => $stock->id,
            ]);
        } else {
            $ledger->record($oldProductId, null, 'adjustment', 'out', $oldQty, [
                'note' => 'Central stock moved to another product',
                'source_type' => 'stock',
                'source_id' => $stock->id,
            ]);
            $ledger->record($newProductId, null, 'add_stock', 'in', $newQty, [
                'note' => 'Central stock set from inventory',
                'source_type' => 'stock',
                'source_id' => $stock->id,
            ]);
        }

        return redirect()->route('stocks.index')
            ->with('success', 'Stock updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stock = Stock::central()->findOrFail($id);
        $qty = (float) $stock->stock_qty;
        $productId = (int) $stock->product_id;
        $stockId = (int) $stock->id;
        $stock->delete();

        app(StockLedger::class)->record($productId, null, 'adjustment', 'out', $qty, [
            'note' => 'Central stock removed',
            'source_type' => 'stock',
            'source_id' => $stockId,
        ]);

        return redirect()->route('stocks.index')
            ->with('success', 'Stock deleted successfully');
    }

    private function historyRange(string $range): array
    {
        $today = now();

        return match ($range) {
            'today' => [$today->toDateString(), $today->toDateString()],
            '7d' => [$today->copy()->subDays(6)->toDateString(), $today->toDateString()],
            'month' => [$today->copy()->startOfMonth()->toDateString(), $today->toDateString()],
            default => [null, null],
        };
    }
}
