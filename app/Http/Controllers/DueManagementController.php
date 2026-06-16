<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ManualDue;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class DueManagementController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('dues.customer');
    }

    public function customer()
    {
        $filters = $this->filters(request());

        $rows = Customer::query()
            ->addSelect([
                'latest_due_sale_at' => Sale::select('created_at')
                    ->whereColumn('customer_id', 'customers.id')
                    ->where('due', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('created_at', $filters['date']))
                    ->latest()
                    ->limit(1),
                'total_due_sale_qty' => DB::table('sale_items')
                    ->selectRaw('COALESCE(SUM(sale_items.qty), 0)')
                    ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                    ->whereColumn('sales.customer_id', 'customers.id')
                    ->where('sales.due', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('sales.created_at', $filters['date'])),
            ])
            ->where('due', '>', 0)
            ->whereExists(function ($query) use ($filters) {
                $query->selectRaw(1)
                    ->from('sales')
                    ->whereColumn('sales.customer_id', 'customers.id')
                    ->where('sales.due', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('sales.created_at', $filters['date']));
            })
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('due')
            ->paginate(10)
            ->withQueryString();

        return view('dues.customer', compact('rows', 'filters'));
    }

    public function supplier()
    {
        $filters = $this->filters(request());

        $rows = Supplier::query()
            ->addSelect([
                'latest_due_purchase_date' => Purchase::select('date')
                    ->whereColumn('supplier_id', 'suppliers.id')
                    ->where('due_amount', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
                    ->latest('date')
                    ->limit(1),
                'total_due_purchase_qty' => DB::table('purchase_items')
                    ->selectRaw('COALESCE(SUM(purchase_items.qty), 0)')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->where('purchases.due_amount', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('purchases.date', $filters['date'])),
            ])
            ->where('due', '>', 0)
            ->whereExists(function ($query) use ($filters) {
                $query->selectRaw(1)
                    ->from('purchases')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->where('purchases.due_amount', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('purchases.date', $filters['date']));
            })
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('due')
            ->paginate(10)
            ->withQueryString();

        return view('dues.supplier', compact('rows', 'filters'));
    }

    public function sale()
    {
        $filters = $this->filters(request());

        $rows = Sale::with('customer')
            ->where('due', '>', 0)
            ->when($filters['date'], fn ($q) => $q->whereDate('created_at', $filters['date']))
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($sub) use ($search) {
                    $sub->where('reference', 'like', "%{$search}%")
                        ->orWhere('cash_memo', 'like', "%{$search}%")
                        ->orWhere('bell_no', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dues.sale', compact('rows', 'filters'));
    }

    public function purchase()
    {
        $filters = $this->filters(request());

        $rows = Purchase::with('supplier')
            ->where('due_amount', '>', 0)
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($sub) use ($search) {
                    $sub->where('reference', 'like', "%{$search}%")
                        ->orWhere('cash_memo', 'like', "%{$search}%")
                        ->orWhere('bill_no', 'like', "%{$search}%")
                        ->orWhere('seller_store_name', 'like', "%{$search}%")
                        ->orWhere('purchased_by', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplier) use ($search) {
                            $supplier->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dues.purchase', compact('rows', 'filters'));
    }

    public function manual()
    {
        $filters = $this->filters(request());

        $manualDues = ManualDue::with(['customer', 'supplier'])
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];

                $query->where(function ($sub) use ($search) {
                    $sub->where('reference', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customer) use ($search) {
                            $customer->where('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('supplier', function ($supplier) use ($search) {
                            $supplier->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('address', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone', 'due']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone', 'due']);

        return view('dues.manual', compact(
            'manualDues',
            'customers',
            'suppliers',
            'filters',
        ));
    }

    public function exportCustomer()
    {
        $filters = $this->filters(request());
        $rows = Customer::query()
            ->where('due', '>', 0)
            ->whereExists(function ($query) use ($filters) {
                $query->selectRaw(1)
                    ->from('sales')
                    ->whereColumn('sales.customer_id', 'customers.id')
                    ->where('sales.due', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('sales.created_at', $filters['date']));
            })
            ->when($filters['search'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $search = $filters['search'];
                $sub->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->orderByDesc('due')
            ->get();

        return $this->export('customer-wise-dues', 'Customer Wise Dues', ['Code', 'Customer', 'Phone', 'Total Sale', 'Total Paid', 'Due'], $rows->map(fn ($row) => [
            $row->code,
            $row->full_name,
            $row->phone,
            $row->total_sale,
            $row->total_paid,
            $row->due,
        ]));
    }

    public function exportSupplier()
    {
        $filters = $this->filters(request());
        $rows = Supplier::query()
            ->addSelect([
                'total_due_purchase_qty' => DB::table('purchase_items')
                    ->selectRaw('COALESCE(SUM(purchase_items.qty), 0)')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->where('purchases.due_amount', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('purchases.date', $filters['date'])),
            ])
            ->where('due', '>', 0)
            ->whereExists(function ($query) use ($filters) {
                $query->selectRaw(1)
                    ->from('purchases')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->where('purchases.due_amount', '>', 0)
                    ->when($filters['date'], fn ($q) => $q->whereDate('purchases.date', $filters['date']));
            })
            ->when($filters['search'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $search = $filters['search'];
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            }))
            ->orderByDesc('due')
            ->get();

        return $this->export('supplier-wise-dues', 'Supplier Wise Dues', ['Code', 'Supplier', 'Phone', 'Total Qty', 'Total Purchase', 'Total Paid', 'Due'], $rows->map(fn ($row) => [
            $row->code,
            $row->name,
            $row->phone,
            $row->total_due_purchase_qty ?? 0,
            $row->total_purchase,
            $row->total_paid,
            $row->due,
        ]));
    }

    public function exportSale()
    {
        $filters = $this->filters(request());
        $rows = Sale::with('customer')
            ->where('due', '>', 0)
            ->when($filters['date'], fn ($q) => $q->whereDate('created_at', $filters['date']))
            ->when($filters['search'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $search = $filters['search'];
                $sub->where('reference', 'like', "%{$search}%")
                    ->orWhere('cash_memo', 'like', "%{$search}%")
                    ->orWhere('bell_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%"));
            }))
            ->latest()
            ->get();

        return $this->export('sale-wise-dues', 'Sale Wise Dues', ['Date', 'Reference', 'Customer', 'Grand Total', 'Paid', 'Due', 'Status'], $rows->map(fn ($row) => [
            optional($row->created_at)->format('Y-m-d'),
            $row->reference,
            $row->customer?->full_name,
            $row->grand_total,
            $row->paid,
            $row->due,
            $row->payment_status,
        ]));
    }

    public function exportPurchase()
    {
        $filters = $this->filters(request());
        $rows = Purchase::with('supplier')
            ->where('due_amount', '>', 0)
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $search = $filters['search'];
                $sub->where('reference', 'like', "%{$search}%")
                    ->orWhere('cash_memo', 'like', "%{$search}%")
                    ->orWhere('bill_no', 'like', "%{$search}%")
                    ->orWhere('seller_store_name', 'like', "%{$search}%")
                    ->orWhere('purchased_by', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            }))
            ->latest()
            ->get();

        return $this->export('purchase-wise-dues', 'Purchase Wise Dues', ['Date', 'Reference', 'Supplier', 'Grand Total', 'Paid', 'Due', 'Status'], $rows->map(fn ($row) => [
            optional($row->date)->format('Y-m-d'),
            $row->reference,
            $row->supplier?->name,
            $row->grand_total,
            $row->paid_amount,
            $row->due_amount,
            $row->payment_status,
        ]));
    }

    public function exportManual()
    {
        $filters = $this->filters(request());
        $rows = ManualDue::with(['customer', 'supplier'])
            ->when($filters['date'], fn ($q) => $q->whereDate('date', $filters['date']))
            ->when($filters['search'], fn ($q) => $q->where(function ($sub) use ($filters) {
                $search = $filters['search'];
                $sub->where('reference', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            }))
            ->latest('date')
            ->latest()
            ->get();

        return $this->export('manual-dues', 'Manual Dues', ['Date', 'Reference', 'Party Type', 'Adjustment', 'Party', 'Amount', 'Note'], $rows->map(fn ($row) => [
            optional($row->date)->format('Y-m-d'),
            $row->reference,
            $row->party_type,
            $row->adjustment_type,
            $row->customer?->full_name ?? $row->supplier?->name,
            $row->amount,
            $row->note,
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $due = ManualDue::create($data);
            $this->applyManualDue($due, 1);
        });

        return redirect()->route('dues.manual')->with('success', 'Manual due added successfully.');
    }

    public function edit(ManualDue $manualDue)
    {
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone', 'due']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone', 'due']);

        return view('dues.edit', compact('manualDue', 'customers', 'suppliers'));
    }

    public function update(Request $request, ManualDue $manualDue)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($manualDue, $data) {
            $this->applyManualDue($manualDue, -1);
            $manualDue->update($data);
            $this->applyManualDue($manualDue->fresh(), 1);
        });

        return redirect()->route('dues.manual')->with('success', 'Manual due updated successfully.');
    }

    public function destroy(ManualDue $manualDue)
    {
        DB::transaction(function () use ($manualDue) {
            $this->applyManualDue($manualDue, -1);
            $manualDue->delete();
        });

        return redirect()->route('dues.manual')->with('success', 'Manual due deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'party_type' => ['required', Rule::in(['customer', 'supplier'])],
            'adjustment_type' => ['required', Rule::in(['add', 'subtract'])],
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string|max:2000',
        ]);

        if ($data['party_type'] === 'customer') {
            $request->validate(['customer_id' => 'required|exists:customers,id']);
            $data['supplier_id'] = null;
        } else {
            $request->validate(['supplier_id' => 'required|exists:suppliers,id']);
            $data['customer_id'] = null;
        }

        return $data;
    }

    private function filters(Request $request): array
    {
        return [
            'search' => $request->input('search'),
            'date' => $request->input('date'),
        ];
    }

    private function applyManualDue(ManualDue $due, int $multiplier): void
    {
        $amount = (float) $due->amount * $multiplier;
        $amount = $due->adjustment_type === 'subtract' ? -1 * $amount : $amount;

        if ($due->party_type === 'customer' && $due->customer_id) {
            $customer = Customer::find($due->customer_id);
            if ($customer) {
                $customer->increment('total_sale', $amount);
                $customer->recalculateDue();
            }
        }

        if ($due->party_type === 'supplier' && $due->supplier_id) {
            $supplier = Supplier::find($due->supplier_id);
            if ($supplier) {
                $supplier->increment('total_purchase', $amount);
                $supplier->update(['due' => max(0, (float) $supplier->total_purchase - (float) $supplier->total_paid)]);
            }
        }
    }

    private function export(string $name, string $title, array $header, $rows)
    {
        if (request('format') === 'pdf') {
            return Response::make(SimplePdf::table($title, $header, $rows), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$name.'-'.now()->format('Y-m-d-H-i-s').'.pdf"',
            ]);
        }

        return $this->csv($name, $header, $rows);
    }

    private function csv(string $name, array $header, $rows)
    {
        $fileName = $name.'-'.now()->format('Y-m-d-H-i-s').'.csv';

        return Response::stream(function () use ($header, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $header);

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
