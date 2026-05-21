<?php

namespace App\Http\Controllers;

use App\Models\ClothSewing;
use App\Models\Product;
use App\Models\ReceivedCloth;
use App\Models\Tailor;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class ClothSewingController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $tailors = Tailor::query()
            ->with([
                'clothSewings.product',
                'receivedCloths.product',
            ])
            ->withSum('clothSewings as total_sewing_qty', 'item_qty')
            ->withSum('receivedCloths as total_received_qty', 'item_qty')
            ->withMax('clothSewings as latest_sewing_date', 'date')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhereHas('clothSewings.product', fn ($product) => $product
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%"));
            }))
            ->whereHas('clothSewings')
            ->orderByDesc('latest_sewing_date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('cloth_sewings.index', compact('tailors', 'search'));
    }

    public function create()
    {
        $clothSewing = new ClothSewing(['date' => now()->toDateString()]);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);
        $tailors = Tailor::orderBy('name')->get(['id', 'name', 'phone']);

        return view('cloth_sewings.create', compact('clothSewing', 'products', 'tailors'));
    }

    public function store(Request $request)
    {
        foreach ($this->validatedRows($request) as $row) {
            ClothSewing::create($row);
        }

        return redirect()->route('cloth-sewings.index')->with('success', 'Cloth sewing records added successfully.');
    }

    public function edit(ClothSewing $clothSewing)
    {
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);
        $tailors = Tailor::orderBy('name')->get(['id', 'name', 'phone']);

        return view('cloth_sewings.edit', compact('clothSewing', 'products', 'tailors'));
    }

    public function update(Request $request, ClothSewing $clothSewing)
    {
        $clothSewing->update($this->validatedSingle($request));

        return redirect()->route('cloth-sewings.index')->with('success', 'Cloth sewing record updated successfully.');
    }

    public function destroy(ClothSewing $clothSewing)
    {
        $clothSewing->delete();

        return redirect()->route('cloth-sewings.index')->with('success', 'Cloth sewing record deleted successfully.');
    }

    public function receiveData(Tailor $tailor)
    {
        return response()->json([
            'tailor' => [
                'id' => $tailor->id,
                'name' => $tailor->name,
            ],
            'items' => $this->tailorProductSummary($tailor)->values(),
        ]);
    }

    public function saveReceived(Request $request, Tailor $tailor)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.received_qty' => 'required|numeric|min:0',
        ]);

        $summary = $this->tailorProductSummary($tailor)->keyBy('product_id');

        DB::transaction(function () use ($data, $summary, $tailor) {
            foreach ($data['items'] as $item) {
                $productId = (int) $item['product_id'];
                $targetQty = round((float) $item['received_qty'], 2);
                $row = $summary->get($productId);

                if (! $row) {
                    throw ValidationException::withMessages([
                        'items' => 'This product is not assigned to the selected tailor.',
                    ]);
                }

                if ($targetQty > round((float) $row['sewing_qty'], 2)) {
                    throw ValidationException::withMessages([
                        'items' => 'Received quantity cannot be greater than sewing quantity for '.$row['product_name'].'.',
                    ]);
                }

                $delta = round($targetQty - (float) $row['received_qty'], 2);

                if (abs($delta) < 0.01) {
                    continue;
                }

                ReceivedCloth::create([
                    'tailor_name' => $tailor->name,
                    'tailor_id' => $tailor->id,
                    'product_id' => $productId,
                    'item_qty' => $delta,
                    'date' => now()->toDateString(),
                ]);
            }
        });

        $tailor->load(['clothSewings.product', 'receivedCloths.product']);

        return response()->json([
            'message' => 'Received cloth updated successfully.',
            'row' => $this->tailorIndexRow($tailor),
            'items' => $this->tailorProductSummary($tailor)->values(),
        ]);
    }

    public function logs(Tailor $tailor)
    {
        return response()->json([
            'tailor' => [
                'id' => $tailor->id,
                'name' => $tailor->name,
            ],
            'pdf_url' => route('cloth-sewings.tailors.logs.export', [$tailor, 'format' => 'pdf']),
            'logs' => $this->tailorLogs($tailor)->map(fn ($log) => [
                'date' => optional($log['date'])->format('d M Y'),
                'type' => $log['type'],
                'product' => $log['product'],
                'design_code' => $log['design_code'],
                'qty' => number_format($log['qty'], 2),
                'note' => $log['note'],
            ])->values(),
        ]);
    }

    public function exportLogs(Tailor $tailor)
    {
        $logs = $this->tailorLogs($tailor);
        $summary = $this->tailorProductSummary($tailor);
        $headers = ['Date', 'Type', 'Product', 'Design Code', 'Qty', 'Note'];
        $totalSewing = (float) $summary->sum('sewing_qty');
        $totalReceived = (float) $summary->sum('received_qty');
        $rows = $logs->map(fn ($log) => [
            optional($log['date'])->format('Y-m-d'),
            $log['type'],
            $log['product'],
            $log['design_code'],
            $log['qty'],
            $log['note'],
        ]);

        $fileName = 'tailor-'.$tailor->id.'-cloth-sewing-logs-'.now()->format('Y-m-d-H-i-s').'.pdf';

        return Response::make(SimplePdf::table('Inaya Creation - Cloth Sewing Logs - '.$tailor->name, $headers, $rows, null, [
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
            'summary' => [
                ['label' => 'Total Sewing Qty', 'value' => number_format($totalSewing, 2)],
                ['label' => 'Total Received Qty', 'value' => number_format($totalReceived, 2)],
                ['label' => 'Balance Qty', 'value' => number_format($totalSewing - $totalReceived, 2)],
            ],
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function validatedRows(Request $request): array
    {
        $data = $request->validate([
            'tailor_id' => 'required|exists:tailors,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.item_qty' => 'required|numeric|min:0.01',
        ]);

        return collect($data['items'])->map(fn ($item) => [
            'tailor_id' => $data['tailor_id'],
            'product_id' => $item['product_id'],
            'item_qty' => $item['item_qty'],
            'date' => $data['date'],
        ])->all();
    }

    private function validatedSingle(Request $request): array
    {
        $data = $request->validate([
            'tailor_id' => 'required|exists:tailors,id',
            'product_id' => 'required|exists:products,id',
            'item_qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
        ]);

        return $data;
    }

    private function tailorProductSummary(Tailor $tailor)
    {
        $sewing = ClothSewing::query()
            ->with('product')
            ->select('product_id')
            ->selectRaw('SUM(item_qty) as sewing_qty')
            ->where('tailor_id', $tailor->id)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $received = ReceivedCloth::query()
            ->select('product_id')
            ->selectRaw('SUM(item_qty) as received_qty')
            ->where('tailor_id', $tailor->id)
            ->groupBy('product_id')
            ->pluck('received_qty', 'product_id');

        return $sewing->map(function ($item) use ($received) {
            $receivedQty = (float) ($received[$item->product_id] ?? 0);
            $sewingQty = (float) $item->sewing_qty;

            return [
                'product_id' => (int) $item->product_id,
                'product_name' => $item->product?->product_name ?? '-',
                'design_code' => $item->product?->sku ?? $item->product?->product_code ?? '-',
                'sewing_qty' => $sewingQty,
                'received_qty' => $receivedQty,
                'balance_qty' => $sewingQty - $receivedQty,
            ];
        });
    }

    private function tailorIndexRow(Tailor $tailor): array
    {
        $summary = $this->tailorProductSummary($tailor);

        return [
            'tailor_id' => $tailor->id,
            'tailor_name' => $tailor->name,
            'latest_date' => optional($tailor->clothSewings->max('date'))->format('d M Y'),
            'products' => $summary->map(fn ($item) => [
                'name' => $item['product_name'],
                'design_code' => $item['design_code'],
                'sewing_qty' => number_format($item['sewing_qty'], 2),
                'received_qty' => number_format($item['received_qty'], 2),
                'balance_qty' => number_format($item['balance_qty'], 2),
            ])->values(),
            'total_sewing_qty' => number_format($summary->sum('sewing_qty'), 2),
            'total_received_qty' => number_format($summary->sum('received_qty'), 2),
            'balance_qty' => number_format($summary->sum('balance_qty'), 2),
        ];
    }

    private function tailorLogs(Tailor $tailor)
    {
        return collect()
            ->merge($tailor->clothSewings()->with('product')->latest('date')->latest()->get()->map(fn ($item) => [
                'date' => $item->date,
                'type' => 'Sewing',
                'product' => $item->product?->product_name ?? '-',
                'design_code' => $item->product?->sku ?? $item->product?->product_code ?? '-',
                'qty' => (float) $item->item_qty,
                'note' => 'Assigned to tailor',
            ]))
            ->merge($tailor->receivedCloths()->with('product')->latest('date')->latest()->get()->map(fn ($item) => [
                'date' => $item->date,
                'type' => 'Received',
                'product' => $item->product?->product_name ?? '-',
                'design_code' => $item->product?->sku ?? $item->product?->product_code ?? '-',
                'qty' => (float) $item->item_qty,
                'note' => $item->item_qty < 0 ? 'Received adjustment' : 'Received from tailor',
            ]))
            ->sortByDesc('date')
            ->values();
    }
}
