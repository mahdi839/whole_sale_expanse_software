<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashTransactionController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $filters = [
            'search' => $request->input('search'),
            'direction' => $request->input('direction'),
            'type' => $request->input('type'),
            'date_from' => $request->input('date_from', $today),
            'date_to' => $request->input('date_to', $today),
        ];

        $query = CashTransaction::query()
            ->with(['customer', 'supplier'])
            ->when($filters['direction'], fn($q) => $q->where('direction', $filters['direction']))
            ->when($filters['type'], fn($q) => $q->where('type', $filters['type']))
            ->when($filters['date_from'], fn($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('note', 'like', "%{$s}%")
                        ->orWhere('payment_method', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn($c) => $c->where('full_name', 'like', "%{$s}%"))
                        ->orWhereHas('supplier', fn($sp) => $sp->where('name', 'like', "%{$s}%"));
                });
            });

        $transactions = (clone $query)->latest('date')->latest('id')->paginate(15)->withQueryString();

        $totals = (clone $query)->selectRaw('
            COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE 0 END), 0) as cash_in,
            COALESCE(SUM(CASE WHEN direction = "out" THEN amount ELSE 0 END), 0) as cash_out
        ')->first();

        $balance = CashTransaction::selectRaw('
            COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE -amount END), 0) as balance
        ')->value('balance');

        return view('cash_transactions.index', compact('transactions', 'filters', 'totals', 'balance'));
    }

    public function create()
    {
        $transaction = new CashTransaction(['date' => now()->toDateString(), 'direction' => 'in', 'type' => 'manual_add']);
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);

        return view('cash_transactions.create', compact('transaction', 'customers', 'suppliers'));
    }

    public function store(Request $request)
    {
        CashTransaction::create($this->validated($request));

        return redirect()->route('cash-transactions.index')->with('success', 'Cash transaction saved successfully.');
    }

    public function edit(CashTransaction $cashTransaction)
    {
        abort_if($cashTransaction->source_type, 403, 'Automatic cash entries are edited from their source document.');

        $transaction = $cashTransaction;
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);

        return view('cash_transactions.edit', compact('transaction', 'customers', 'suppliers'));
    }

    public function update(Request $request, CashTransaction $cashTransaction)
    {
        abort_if($cashTransaction->source_type, 403, 'Automatic cash entries are edited from their source document.');

        $cashTransaction->update($this->validated($request));

        return redirect()->route('cash-transactions.index')->with('success', 'Cash transaction updated successfully.');
    }

    public function destroy(CashTransaction $cashTransaction)
    {
        abort_if($cashTransaction->source_type, 403, 'Automatic cash entries are deleted from their source document.');

        $cashTransaction->delete();

        return redirect()->route('cash-transactions.index')->with('success', 'Cash transaction deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'direction' => ['required', Rule::in(['in', 'out'])],
            'type' => ['required', Rule::in(['manual_add', 'collection', 'manual_out'])],
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'note' => 'nullable|string|max:2000',
        ]);

        if ($data['type'] === 'manual_add' || $data['type'] === 'collection') {
            $data['direction'] = 'in';
        }

        if ($data['type'] === 'manual_out') {
            $data['direction'] = 'out';
        }

        return $data;
    }
}
