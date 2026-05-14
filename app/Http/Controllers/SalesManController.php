<?php

namespace App\Http\Controllers;

use App\Models\SalesMan;
use Illuminate\Http\Request;

class SalesManController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $salesMen = SalesMan::query()
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales_men.index', compact('salesMen', 'search'));
    }

    public function create()
    {
        $salesMan = new SalesMan(['joining_date' => now()->toDateString()]);

        return view('sales_men.create', compact('salesMan'));
    }

    public function store(Request $request)
    {
        SalesMan::create($this->validated($request));

        return redirect()->route('sales-men.index')->with('success', 'Sales man created successfully.');
    }

    public function show(SalesMan $salesMan)
    {
        $logs = $salesMan->cashTransactions()->latest('date')->latest()->get();

        return view('sales_men.show', compact('salesMan', 'logs'));
    }

    public function edit(SalesMan $salesMan)
    {
        return view('sales_men.edit', compact('salesMan'));
    }

    public function update(Request $request, SalesMan $salesMan)
    {
        $data = $this->validated($request);
        unset($data['total_expense']);

        $salesMan->update($data);

        return redirect()->route('sales-men.index')->with('success', 'Sales man updated successfully.');
    }

    public function destroy(SalesMan $salesMan)
    {
        $salesMan->delete();

        return redirect()->route('sales-men.index')->with('success', 'Sales man deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:30',
            'joining_date' => 'nullable|date',
            'total_expense' => 'nullable|numeric|min:0',
        ]);
    }
}
