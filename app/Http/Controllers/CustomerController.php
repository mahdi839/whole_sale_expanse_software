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
                  ->orWhere('phone',    'like', "%{$search}%");
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
        return view('customers.show', compact('customer'));
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
            'total_sale' => 'nullable|numeric|min:0',
            'total_paid' => 'nullable|numeric|min:0',
        ]);
 
        $validated['total_sale'] = $validated['total_sale'] ?? 0;
        $validated['total_paid'] = $validated['total_paid'] ?? 0;
        $validated['due']        = max(0, $validated['total_sale'] - $validated['total_paid']);
 
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
