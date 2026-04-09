<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
     public function index(Request $request)
    {
        $search = $request->input('search');
 
        $suppliers = Supplier::query()
            ->when($search, fn($q) => $q
                ->where('name',  'like', "%{$search}%")
                ->orWhere('code',  'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();
 
        return view('suppliers.index', compact('suppliers', 'search'));
    }
 
    public function create()
    {
        $nextCode = Supplier::generateCode();
        return view('suppliers.create', compact('nextCode'));
    }
 
    public function store(Request $request)
    {
        $validated = $this->validateSupplier($request);
        $validated['due'] = max(0, $validated['total_purchase'] - $validated['total_paid']);
 
        Supplier::create($validated);
 
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }
 
    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }
 
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }
 
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $this->validateSupplier($request);
        $validated['due'] = max(0, $validated['total_purchase'] - $validated['total_paid']);
 
        $supplier->update($validated);
 
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }
 
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
 
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
 
    // ── Shared validation ──────────────────────────────────
    private function validateSupplier(Request $request): array
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'nullable|string|max:500',
            'total_purchase' => 'nullable|numeric|min:0',
            'total_paid'     => 'nullable|numeric|min:0',
        ]);
 
        $data['total_purchase'] = $data['total_purchase'] ?? 0;
        $data['total_paid']     = $data['total_paid']     ?? 0;
 
        return $data;
    }
}
