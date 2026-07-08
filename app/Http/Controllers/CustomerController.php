<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Support\SimplePdf;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class CustomerController extends Controller
{
    // -------------------------------------------------------
    // INDEX — list with search & pagination
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $search = $request->input('search');
 
        $customers = $this->customerIndexQuery($search)
            ->latest()
            ->paginate(15)
            ->withQueryString();
 
        $summary = [
            'total_sale' => (float) Customer::sum('total_sale'),
            'total_paid' => (float) Customer::sum('total_paid'),
            'total_due' => (float) Customer::sum('due'),
            'count' => Customer::count(),
            'total_sell_qty' => (float) DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->sum('sale_items.qty'),
        ];

        return view('customers.index', compact('customers', 'search', 'summary'));
    }

    public function exportIndexPdf(Request $request)
    {
        $search = $request->input('search');
        $customers = $this->customerIndexQuery($search)
            ->orderBy('full_name')
            ->get();

        $headers = ['Code', 'Full Name', 'Phone', 'Address', 'Total Quantity', 'Total Sale', 'Total Paid', 'Total Due'];
        $rows = $customers->map(fn (Customer $customer) => [
            $customer->code,
            $customer->full_name,
            $customer->phone ?? '-',
            $customer->address ?? '-',
            number_format((float) ($customer->total_sell_qty ?? 0), 2),
            number_format((float) $customer->total_sale, 2),
            number_format((float) $customer->total_paid, 2),
            number_format((float) $customer->due, 2),
        ]);

        return $this->streamPdf(
            'customers-'.now()->format('Y-m-d-H-i-s').'.pdf',
            'Inaya Creation - Customers',
            $headers,
            $rows
        );
    }

    public function suggestions(Request $request)
    {
        $search = trim((string) $request->input('q'));

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Customer::query();

        foreach (preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) as $term) {
            $query->where(function ($match) use ($term) {
                $match->where('full_name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('alternative_phone', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%");
            });
        }

        return response()->json(
            $query->orderBy('full_name')
                ->limit(8)
                ->get(['id', 'code', 'full_name', 'phone'])
                ->map(fn (Customer $customer) => [
                    'name' => $customer->full_name,
                    'code' => $customer->code,
                    'phone' => $customer->phone,
                    'url' => route('customers.show', $customer),
                ])
        );
    }
 
    // -------------------------------------------------------
    // CREATE
    // -------------------------------------------------------
    public function create()
    {
        $nextCode = Customer::generateCode();
        return view('customers.create', compact('nextCode'));
    }
 
    // -------------------------------------------------------
    // STORE
    // -------------------------------------------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:1000',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'total_sale' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
        ]);
 
        $validated['total_sale'] = $validated['total_sale'] ?? 0;
        $validated['total_paid'] = $validated['total_paid'] ?? 0;
        $validated['due']        = max(0, $validated['total_sale'] - $validated['total_paid']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('customers', 'public');
        }
 
        $customer = Customer::create($validated);
   // If request came from AJAX / fetch / modal
        if ($request->expectsJson()) {
            return response()->json([
                'id'         => $customer->id,
                'code'       => $customer->code,
                'full_name'  => $customer->full_name,
                'phone'      => $customer->phone,
                'alternative_phone' => $customer->alternative_phone,
                'address'    => $customer->address,
                'image'      => $customer->image,
                'total_sale' => $customer->total_sale,
                'total_paid' => $customer->total_paid,
                'due'        => $customer->due,
                'message'    => 'Customer created successfully.',
            ], 201);
        }
        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }
 
    // -------------------------------------------------------
    // SHOW
    // -------------------------------------------------------
    public function show(Customer $customer)
    {
        [$logs, $totalQty] = $this->customerLogs($customer);
        return view('customers.show', compact('customer', 'logs', 'totalQty'));
    }

    public function exportTransactions(Customer $customer)
    {
        [$logs, $totalQty] = $this->customerLogs($customer);
        $headers = ['Date & Time', 'Type', 'Reference', 'Amount', 'Qty', 'Paid', 'Due','Note'];

        if (request('format') === 'pdf') {
            $rows = $logs->map(fn ($log) => [
                optional($log['display_at'] ?? $log['date'])->format('Y-m-d h:i A'),
                $log['type'],
                $log['reference'],
                $log['type'] === 'Payment' ? '-' : $log['amount'],
                $log['qty'],
                $log['paid'],
                $log['due'],
                $log['note'],
            ]);

            $summary = [
                ['label' => 'Total Paid', 'value' => $customer->total_paid],
                ['label' => 'Total Due', 'value' => $customer->due],
                ['label' => 'Total Qty', 'value' => $totalQty],
                ['label' => 'Total Sale', 'value' =>  $customer->total_sale],
            ];

            return $this->streamCustomerTransactionsPdf(
                'customer-'.$customer->code.'-transactions-'.now()->format('Y-m-d-H-i-s').'.pdf',
                'Inaya Creation - Customer Transactions - '.$customer->full_name,
                $headers,
                $rows,
                $summary
            );
        }

        $fileName = 'customer-'.$customer->code.'-transactions-'.now()->format('Y-m-d-H-i-s').'.csv';

        return $this->streamLogsCsv($fileName, $logs, $headers);
    }
 
    // -------------------------------------------------------
    // EDIT
    // -------------------------------------------------------
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }
 
    // -------------------------------------------------------
    // UPDATE
    // -------------------------------------------------------
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'full_name'  => 'required|string|max:255',
            'phone'      => 'nullable|string|max:20',
            'alternative_phone' => 'nullable|string|max:20',
            'address'    => 'nullable|string|max:1000',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'total_sale' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($customer->image) {
                Storage::disk('public')->delete($customer->image);
            }
            $validated['image'] = $request->file('image')->store('customers', 'public');
        }
 
        $totalSale = $request->has('total_sale')
            ? ($validated['total_sale'] ?? 0)
            : $customer->total_sale;
        $totalPaid = $request->has('total_paid')
            ? ($validated['total_paid'] ?? 0)
            : $customer->total_paid;

        if ($request->hasAny(['total_sale', 'total_paid'])) {
            $validated['total_sale'] = $totalSale;
            $validated['total_paid'] = $totalPaid;
            $validated['due']        = max(0, $totalSale - $totalPaid);
        } else {
            unset($validated['total_sale'], $validated['total_paid']);
        }
 
        $customer->update($validated);
 
        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }
 
    // -------------------------------------------------------
    // DESTROY
    // -------------------------------------------------------
    public function destroy(Customer $customer)
    {
        if ($customer->image) {
            Storage::disk('public')->delete($customer->image);
        }

        $customer->delete();
 
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    private function customerLogs(Customer $customer): array
    {
        $sales = $customer->sales()->with('items.product')->oldest('created_at')->get();
        $totalQty = $sales->sum(fn ($sale) => $sale->items->sum(fn ($item) => (float) $item->qty));

        $logs = collect()
            ->merge($sales->map(function ($sale) {
                $products = $sale->items
                    ->map(fn ($item) => trim(($item->product?->product_name ?? 'Product #'.$item->product_id).' x'.$item->qty))
                    ->filter()
                    ->implode(', ');

                return [
                    'date' => $sale->created_at,
                    'sort_at' => $sale->created_at,
                    'type' => 'Sale',
                    'reference' => $sale->reference,
                    'amount' => $sale->grand_total,
                    'qty' => $sale->items->sum(fn ($item) =>  $item->qty),
                    'paid' => $sale->paid,
                    'due' => $sale->due,
                    'products' => $products,
                    'note' => $sale->note,
                    'url' => route('sales.show', $sale),
                ];
            }))
            ->merge($customer->saleReturns()->with('items.product')->get()->map(function ($return) {
                $products = $return->items
                    ->map(fn ($item) => trim(($item->product?->product_name ?? 'Product #'.$item->product_id).' x'.$item->qty))
                    ->filter()
                    ->implode(', ');

                $affectsCustomerBalance = $return->return_type !== 'exchange';

                return [
                    'date' => $return->created_at,
                    'sort_at' => $return->created_at,
                    'type' => 'Sale Return'.($return->return_type ? ' - '.ucfirst($return->return_type) : ''),
                    'reference' => $return->reference,
                    'amount' => $affectsCustomerBalance ? -1 * $return->return_amount : 0,
                    'qty' => $return->items->sum(fn ($item) =>  $item->qty),
                    'paid' => 0,
                    'due' => 0,
                    'products' => $products,
                    'note' => $return->note,
                    'url' => route('sale-returns.show', $return),
                ];
            }))
            ->merge($customer->cashTransactions()->whereNull('source_type')->get()->map(fn ($cash) => [
                'date' => $cash->date,
                'sort_at' => $this->logSortAt($cash->date, $cash->created_at),
                'type' => 'Payment',
                'reference' => $cash->reference,
                'amount' => $cash->direction === 'in' ? $cash->amount : -1 * $cash->amount,
                'qty' => null,
                'paid' => $cash->amount,
                'due' => "-",
                'products' => '',
                'note' => $cash->note,
                'url' => route('cash-transactions.index', ['search' => $cash->reference]),
            ]))
            ->merge($customer->manualDues()->get()->map(fn ($due) => [
                'date' => $due->date,
                'sort_at' => $this->logSortAt($due->date, $due->created_at),
                'type' => 'Manual Due',
                'reference' => $due->reference ?? 'Manual',
                'amount' => $due->amount,
                'qty' => null,
                'paid' => 0,
                'due' => $due->amount,
                'products' => '',
                'note' => $due->note,
                'url' => route('dues.manual'),
            ]))
            ->sortBy('sort_at')
            ->map(function ($log) {
                $log['display_at'] = $log['sort_at'] ?? $log['date'];
                unset($log['sort_at']);

                return $log;
            })
            ->values();

        return [$logs, $totalQty];
    }

    private function customerIndexQuery(?string $search)
    {
        return Customer::query()
            ->addSelect([
                'total_sell_qty' => DB::table('sale_items')
                    ->selectRaw('COALESCE(SUM(sale_items.qty), 0)')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->whereColumn('sales.customer_id', 'customers.id'),
            ])
            ->when($search, function ($q) use ($search) {
                foreach (preg_split('/\s+/', trim($search), -1, PREG_SPLIT_NO_EMPTY) as $term) {
                    $q->where(function ($match) use ($term) {
                        $match->where('full_name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%")
                            ->orWhere('phone', 'like', "%{$term}%")
                            ->orWhere('alternative_phone', 'like', "%{$term}%")
                            ->orWhere('address', 'like', "%{$term}%");
                    });
                }
            });
    }

    private function logSortAt($date, $createdAt): ?Carbon
    {
        if (! $date) {
            return $createdAt;
        }

        $sortAt = $date instanceof Carbon
            ? $date->copy()
            : ($date instanceof DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date));

        if ($createdAt instanceof DateTimeInterface) {
            return $sortAt->setTime(
                (int) $createdAt->format('H'),
                (int) $createdAt->format('i'),
                (int) $createdAt->format('s')
            );
        }

        return $sortAt->startOfDay();
    }

    private function streamLogsCsv(string $fileName, $logs, array $header)
    {
        return Response::stream(function () use ($logs, $header) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $header);

            foreach ($logs as $log) {
                fputcsv($file, [
                    optional($log['display_at'] ?? $log['date'])->format('Y-m-d h:i A'),
                    $log['type'],
                    $log['reference'],
                    $log['amount'],
                    $log['qty'],
                    $log['paid'],
                    $log['due'],
                    $log['products'],
                    $log['note'],
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function streamPdf(string $fileName, string $title, array $headers, $rows, array $summary = [])
    {
        return Response::make(SimplePdf::table($title, $headers, $rows, null, [
            'summary' => $summary,
            'logo_path' => public_path('inaya_creation_logo.jpeg'),
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    private function streamCustomerTransactionsPdf(string $fileName, string $title, array $headers, $rows, array $summary = [])
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'tempDir' => $tempDir,
            'default_font' => 'freeserif',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);
        $mpdf->useSubstitutions = true;

        $summaryHtml = collect($summary)->map(fn ($item) => '<div class="summary-card"><span>'.
            e((string) ($item['label'] ?? '')).'</span><strong>'.
            e(is_numeric($item['value'] ?? null) ? number_format((float) $item['value'], 2) : (string) ($item['value'] ?? '')).'</strong></div>'
        )->implode('');

        $headerHtml = collect($headers)->map(fn ($header) => '<th>'.e($header).'</th>')->implode('');
        $bodyHtml = collect($rows)->map(function ($row) {
            return '<tr>'.collect($row)->map(fn ($value) => '<td>'.e((string) ($value ?? '-')).'</td>')->implode('').'</tr>';
        })->implode('');

        if ($bodyHtml === '') {
            $bodyHtml = '<tr><td colspan="'.count($headers).'" class="empty">No transactions found.</td></tr>';
        }

        $html = '<html lang="bn"><head><meta charset="UTF-8"><style>
            body { font-family: freeserif, sans-serif; color: #1f2937; font-size: 10pt; }
            h1 { font-size: 16pt; margin: 0 0 4mm; color: #1e3a8a; }
            .date { color: #64748b; font-size: 8pt; margin-bottom: 5mm; }
            .summary { display: table; width: 100%; border-spacing: 3mm; margin-bottom: 5mm; }
            .summary-card { display: table-cell; border: 0.2mm solid #d8dee9; background: #f8fafc; padding: 3mm; }
            .summary-card span { display: block; color: #64748b; font-size: 8pt; text-transform: uppercase; }
            .summary-card strong { display: block; margin-top: 1.5mm; font-size: 11pt; color: #111827; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #1d4ed8; color: #ffffff; font-weight: bold; padding: 2.2mm; border: 0.2mm solid #1d4ed8; }
            td { padding: 2mm; border: 0.2mm solid #d8dee9; vertical-align: top; }
            tr:nth-child(even) td { background: #f8fafc; }
            td:nth-child(4), td:nth-child(5), td:nth-child(6), td:nth-child(7) { text-align: right; }
            .empty { text-align: center; color: #64748b; }
        </style></head><body>
            <h1>'.e($title).'</h1>
            <div class="date">Generated: '.e(now()->format('d M Y H:i')).'</div>
            <div class="summary">'.$summaryHtml.'</div>
            <table><thead><tr>'.$headerHtml.'</tr></thead><tbody>'.$bodyHtml.'</tbody></table>
        </body></html>';

        $mpdf->WriteHTML($html);

        return Response::make($mpdf->Output('', Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
