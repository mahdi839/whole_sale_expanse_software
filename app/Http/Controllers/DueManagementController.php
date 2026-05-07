<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ManualDue;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DueManagementController extends Controller
{
    public function index(Request $request)
    {
        $customerDues = Customer::where('due', '>', 0)->orderByDesc('due')->paginate(10, ['*'], 'customers_page');
        $supplierDues = Supplier::where('due', '>', 0)->orderByDesc('due')->paginate(10, ['*'], 'suppliers_page');

        $saleDues = Sale::with('customer')
            ->where('due', '>', 0)
            ->latest()
            ->paginate(10, ['*'], 'sales_page');

        $purchaseDues = Purchase::with('supplier')
            ->where('due_amount', '>', 0)
            ->latest()
            ->paginate(10, ['*'], 'purchases_page');

        $manualDues = ManualDue::with(['customer', 'supplier'])
            ->latest('date')
            ->latest()
            ->paginate(10, ['*'], 'manual_page');

        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);

        $totals = [
            'customer_due' => Customer::sum('due'),
            'supplier_due' => Supplier::sum('due'),
            'sale_due' => Sale::sum('due'),
            'purchase_due' => Purchase::sum('due_amount'),
            'manual_customer_due' => ManualDue::where('party_type', 'customer')->sum('amount'),
            'manual_supplier_due' => ManualDue::where('party_type', 'supplier')->sum('amount'),
        ];

        return view('dues.index', compact(
            'customerDues',
            'supplierDues',
            'saleDues',
            'purchaseDues',
            'manualDues',
            'customers',
            'suppliers',
            'totals',
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $due = ManualDue::create($data);
            $this->applyManualDue($due, 1);
        });

        return redirect()->route('dues.index')->with('success', 'Manual due added successfully.');
    }

    public function edit(ManualDue $manualDue)
    {
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);

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

        return redirect()->route('dues.index')->with('success', 'Manual due updated successfully.');
    }

    public function destroy(ManualDue $manualDue)
    {
        DB::transaction(function () use ($manualDue) {
            $this->applyManualDue($manualDue, -1);
            $manualDue->delete();
        });

        return redirect()->route('dues.index')->with('success', 'Manual due deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'party_type' => ['required', Rule::in(['customer', 'supplier'])],
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

    private function applyManualDue(ManualDue $due, int $multiplier): void
    {
        $amount = (float) $due->amount * $multiplier;

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
}
