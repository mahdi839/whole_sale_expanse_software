<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    // -------------------------------------------------------
    // INDEX — list with search & pagination
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $search = $request->input('search');
 
        $customers = Customer::query()
            ->addSelect([
                'total_sell_qty' => DB::table('sale_items')
                    ->selectRaw('COALESCE(SUM(sale_items.qty), 0)')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->whereColumn('sales.customer_id', 'customers.id'),
            ])
            ->when($search, function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('code',     'like', "%{$search}%")
                  ->orWhere('phone',    'like', "%{$search}%")
                  ->orWhere('alternative_phone', 'like', "%{$search}%")
                  ->orWhere('address',  'like', "%{$search}%");
            })
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
        $headers = ['Date', 'Type', 'Reference', 'Amount', 'Qty', 'Paid', 'Due', 'Products / Note'];

        if (request('format') === 'pdf') {
            $rows = $logs->map(fn ($log) => [
                optional($log['date'])->format('Y-m-d'),
                $log['type'],
                $log['reference'],
                $log['amount'],
                $log['qty'],
                $log['paid'],
                $log['due'],
                $log['note'],
            ]);

            $summary = [
                ['label' => 'Total Paid', 'value' => number_format((float) $customer->total_paid, 2)],
                ['label' => 'Total Due', 'value' => number_format((float) $customer->due, 2)],
                ['label' => 'Total Qty', 'value' => number_format((float) $totalQty, 2)],
                ['label' => 'Total Sale', 'value' => number_format((float) $customer->total_sale, 2)],
            ];

            return $this->streamPdf(
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
        $sales = $customer->sales()->with('items.product')->latest()->get();
        $totalQty = $sales->sum(fn ($sale) => $sale->items->sum(fn ($item) => (float) $item->qty));

        $logs = collect()
            ->merge($sales->map(fn ($sale) => [
                'date' => $sale->created_at,
                'type' => 'Sale',
                'reference' => $sale->reference,
                'amount' => (float) $sale->grand_total,
                'qty' => $sale->items->sum(fn ($item) => (float) $item->qty),
                'paid' => (float) $sale->paid,
                'due' => (float) $sale->due,
                'note' => $sale->note,
                'url' => route('sales.show', $sale),
            ]))
            ->merge($customer->saleReturns()->with('items.product')->latest()->get()->map(fn ($return) => [
                'date' => $return->created_at,
                'type' => 'Sale Return',
                'reference' => $return->reference,
                'amount' => -1 * (float) $return->return_amount,
                'qty' => $return->items->sum(fn ($item) => (float) $item->qty),
                'paid' => $return->return_type === 'credit' ? 0 : -1 * (float) $return->return_amount,
                'due' => 0,
                'note' => $return->note,
                'url' => route('sale-returns.show', $return),
            ]))
            ->merge($customer->cashTransactions()->whereNull('source_type')->latest('date')->latest()->get()->map(fn ($cash) => [
                'date' => $cash->date,
                'type' => 'Payment',
                'reference' => $cash->reference,
                'amount' => $cash->direction === 'in' ? (float) $cash->amount : -1 * (float) $cash->amount,
                'qty' => null,
                'paid' => (float) $cash->amount,
                'due' => 0,
                'note' => $cash->note,
                'url' => route('cash-transactions.index', ['search' => $cash->reference]),
            ]))
            ->merge($customer->manualDues()->latest('date')->latest()->get()->map(fn ($due) => [
                'date' => $due->date,
                'type' => 'Manual Due',
                'reference' => $due->reference ?? 'Manual',
                'amount' => (float) $due->amount,
                'qty' => null,
                'paid' => 0,
                'due' => (float) $due->amount,
                'note' => $due->note,
                'url' => route('dues.manual'),
            ]))
            ->sortByDesc('date')
            ->values();

        return [$logs, $totalQty];
    }

    private function streamLogsCsv(string $fileName, $logs, array $header)
    {
        return Response::stream(function () use ($logs, $header) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $header);

            foreach ($logs as $log) {
                fputcsv($file, [
                    optional($log['date'])->format('Y-m-d'),
                    $log['type'],
                    $log['reference'],
                    $log['amount'],
                    $log['qty'],
                    $log['paid'],
                    $log['due'],
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
}
