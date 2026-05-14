<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // -------------------------------------------------------
    // INDEX — list with search & pagination
    // -------------------------------------------------------
    public function index(Request $request)
    {
        $search = $request->input('search');
 
        $customers = Customer::query()
            ->when($search, function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('code',     'like', "%{$search}%")
                  ->orWhere('phone',    'like', "%{$search}%")
                  ->orWhere('address',  'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
 
        return view('customers.index', compact('customers', 'search'));
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
            'address'    => 'nullable|string|max:1000',
            'total_sale' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
        ]);
 
        $validated['total_sale'] = $validated['total_sale'] ?? 0;
        $validated['total_paid'] = $validated['total_paid'] ?? 0;
        $validated['due']        = max(0, $validated['total_sale'] - $validated['total_paid']);
 
        $customer = Customer::create($validated);
   // If request came from AJAX / fetch / modal
        if ($request->expectsJson()) {
            return response()->json([
                'id'         => $customer->id,
                'code'       => $customer->code,
                'full_name'  => $customer->full_name,
                'phone'      => $customer->phone,
                'address'    => $customer->address,
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
                'note' => $sale->items->map(fn ($item) => $item->product?->product_name.' x'.$item->qty)->implode(', '),
                'url' => route('sales.show', $sale),
            ]))
            ->merge($customer->saleReturns()->latest()->get()->map(fn ($return) => [
                'date' => $return->created_at,
                'type' => 'Sale Return',
                'reference' => $return->reference,
                'amount' => -1 * (float) $return->return_amount,
                'qty' => null,
                'paid' => $return->return_type === 'credit' ? 0 : -1 * (float) $return->return_amount,
                'due' => 0,
                'note' => ucfirst($return->return_type).' / '.ucfirst($return->return_status),
                'url' => route('sale-returns.show', $return),
            ]))
            ->merge($customer->cashTransactions()->latest('date')->latest()->get()->map(fn ($cash) => [
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

        return view('customers.show', compact('customer', 'logs', 'totalQty'));
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
            'address'    => 'nullable|string|max:1000',
            'total_sale' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
        ]);
 
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
        $customer->delete();
 
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
