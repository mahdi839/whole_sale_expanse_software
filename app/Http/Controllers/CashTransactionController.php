<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\CarryMan;
use App\Models\ComputerMan;
use App\Models\Customer;
use App\Models\GareyMan;
use App\Models\SalesMan;
use App\Models\Supplier;
use App\Models\Tailor;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CashTransactionController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $filters = [
            'shop_id' => $request->input('shop_id'),
            'search' => $request->input('search'),
            'direction' => $request->input('direction'),
            'type' => $request->input('type'),
            'date_from' => $request->input('date_from', $today),
            'date_to' => $request->input('date_to', $today),
        ];

        $query = CashTransaction::query()
            ->with(['shop', 'customer', 'supplier', 'salesMan', 'tailor', 'carryMan', 'computerMan', 'gareyMan'])
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
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
                        ->orWhereHas('supplier', fn($sp) => $sp->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('salesMan', fn($sm) => $sm->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('tailor', fn($tailor) => $tailor->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('carryMan', fn($worker) => $worker->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('computerMan', fn($worker) => $worker->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('gareyMan', fn($worker) => $worker->where('name', 'like', "%{$s}%"));
                });
            });

        $transactions = (clone $query)->latest('date')->latest('id')->paginate(15)->withQueryString();

        $totals = (clone $query)->selectRaw('
            COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE 0 END), 0) as cash_in,
            COALESCE(SUM(CASE WHEN direction = "out" THEN amount ELSE 0 END), 0) as cash_out
        ')->first();

        $overallBalance = CashTransaction::query()
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
            ->selectRaw('
            COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE -amount END), 0) as balance
        ')->value('balance');

        $dateBalance = CashTransaction::query()
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
            ->when($filters['date_from'], fn($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->selectRaw('
                COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE -amount END), 0) as balance
            ')
            ->value('balance');
        $shops = auth()->user()->canManageAllShops() ? Shop::where('is_active', true)->orderBy('name')->get() : collect();
        return view('cash_transactions.index', compact('transactions', 'filters', 'totals', 'overallBalance', 'dateBalance', 'shops'));
    }

    public function create()
    {
        $transaction = new CashTransaction(['date' => now()->toDateString(), 'direction' => 'in', 'type' => 'manual_add']);
        $customers = Customer::when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))->orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone', 'currency', 'due']);
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);
        $tailors = Tailor::orderBy('name')->get(['id', 'name']);
        $carryMen = CarryMan::orderBy('name')->get(['id', 'name', 'phone']);
        $computerMen = ComputerMan::orderBy('name')->get(['id', 'name', 'phone']);
        $gareyMen = GareyMan::orderBy('name')->get(['id', 'name', 'phone']);
        $shops = auth()->user()->canManageAllShops() ? Shop::where('is_active', true)->orderBy('name')->get() : collect([auth()->user()->shop]);

        return view('cash_transactions.create', compact('transaction', 'customers', 'suppliers', 'salesMen', 'tailors', 'carryMen', 'computerMen', 'gareyMen', 'shops'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $transaction = CashTransaction::create($this->validated($request));
            $this->applyPartyPayment($transaction, 1);
        });

        return redirect()->route('cash-transactions.index')->with('success', 'Cash transaction saved successfully.');
    }

    public function edit(CashTransaction $cashTransaction)
    {
        abort_if($cashTransaction->source_type, 403, 'Automatic cash entries are edited from their source document.');

        $transaction = $cashTransaction;
        $this->authorizeShop($cashTransaction);
        $customers = Customer::when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))->orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone', 'currency', 'due']);
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);
        $tailors = Tailor::orderBy('name')->get(['id', 'name']);
        $carryMen = CarryMan::orderBy('name')->get(['id', 'name', 'phone']);
        $computerMen = ComputerMan::orderBy('name')->get(['id', 'name', 'phone']);
        $gareyMen = GareyMan::orderBy('name')->get(['id', 'name', 'phone']);
        $shops = auth()->user()->canManageAllShops() ? Shop::where('is_active', true)->orderBy('name')->get() : collect([auth()->user()->shop]);

        return view('cash_transactions.edit', compact('transaction', 'customers', 'suppliers', 'salesMen', 'tailors', 'carryMen', 'computerMen', 'gareyMen', 'shops'));
    }

    public function update(Request $request, CashTransaction $cashTransaction)
    {
        $this->authorizeShop($cashTransaction);
        abort_if($cashTransaction->source_type, 403, 'Automatic cash entries are edited from their source document.');

        DB::transaction(function () use ($request, $cashTransaction) {
            $this->applyPartyPayment($cashTransaction, -1);
            $cashTransaction->update($this->validated($request));
            $this->applyPartyPayment($cashTransaction->fresh(), 1);
        });

        return redirect()->route('cash-transactions.index')->with('success', 'Cash transaction updated successfully.');
    }

    public function destroy(CashTransaction $cashTransaction)
    {
        $this->authorizeShop($cashTransaction);
        abort_if($cashTransaction->source_type, 403, 'Automatic cash entries are deleted from their source document.');

        DB::transaction(function () use ($cashTransaction) {
            $this->applyPartyPayment($cashTransaction, -1);
            $cashTransaction->delete();
        });

        return redirect()->route('cash-transactions.index')->with('success', 'Cash transaction deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'direction' => ['required', Rule::in(['in', 'out'])],
            'type' => ['required', Rule::in(['manual_add', 'collection', 'manual_out'])],
            'amount' => 'required|numeric|min:0.01',
            'supplier_amount' => [
                'nullable',
                Rule::requiredIf(fn () => $request->input('cash_entry_type') === 'supplier'
                    && $request->input('type') === 'manual_out'),
                'numeric',
                'min:0.01',
            ],
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'customer_id' => 'nullable|required_if:cash_entry_type,customer|exists:customers,id',
            'supplier_id' => 'nullable|required_if:cash_entry_type,supplier|exists:suppliers,id',
            'sales_man_id' => 'nullable|exists:sales_men,id',
            'tailor_id' => 'nullable|required_if:cash_entry_type,tailor|exists:tailors,id',
            'carry_man_id' => 'nullable|required_if:cash_entry_type,carry_man|exists:carry_men,id',
            'computer_man_id' => 'nullable|required_if:cash_entry_type,computer|exists:computer_men,id',
            'garey_man_id' => 'nullable|required_if:cash_entry_type,garey_man|exists:garey_men,id',
            'cash_entry_type' => ['nullable', Rule::in(['customer', 'supplier', 'tailor', 'computer', 'carry_man', 'garey_man'])],
            'note' => 'nullable|string|max:2000',
        ]);

        if (auth()->user()->canManageAllShops()) {
            abort_unless(! empty($data['shop_id']), 422, 'Please select a shop.');
        } else {
            abort_unless(auth()->user()->shop_id, 403, 'No shop assigned to your user.');
            $data['shop_id'] = auth()->user()->shop_id;
        }

        if ($data['type'] === 'manual_add' || $data['type'] === 'collection') {
            $data['direction'] = 'in';
        }

        if ($data['type'] === 'manual_out') {
            $data['direction'] = 'out';
        }

        $entryType = $data['cash_entry_type'] ?? null;
        unset($data['cash_entry_type']);

        if ($entryType === 'supplier' && ! empty($data['supplier_id'])) {
            $data['supplier_currency'] = Supplier::find($data['supplier_id'])?->currency;
        } else {
            $data['supplier_amount'] = null;
            $data['supplier_currency'] = null;
        }

        foreach (['customer_id', 'supplier_id', 'tailor_id', 'carry_man_id', 'computer_man_id', 'garey_man_id'] as $field) {
            $typeForField = match ($field) {
                'customer_id' => 'customer',
                'supplier_id' => 'supplier',
                'tailor_id' => 'tailor',
                'carry_man_id' => 'carry_man',
                'computer_man_id' => 'computer',
                'garey_man_id' => 'garey_man',
            };

            if ($entryType !== $typeForField) {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function applyPartyPayment(CashTransaction $transaction, int $multiplier): void
    {
        $amount = (float) $transaction->amount * $multiplier;
        $customerAmount = $transaction->direction === 'in' ? $amount : -1 * $amount;
        $supplierBaseAmount = (float) ($transaction->supplier_amount ?? $transaction->amount) * $multiplier;
        $supplierAmount = $transaction->direction === 'out' ? $supplierBaseAmount : -1 * $supplierBaseAmount;

        if ($transaction->customer_id) {
            $customer = Customer::find($transaction->customer_id);
            if ($customer) {
                $customer->increment('total_paid', $customerAmount);
                $customer->refresh();
                $customer->recalculateDue();
            }
        }

        if ($transaction->supplier_id) {
            $supplier = Supplier::find($transaction->supplier_id);
            if ($supplier) {
                $supplier->increment('total_paid', $supplierAmount);
                $supplier->refresh();
                $supplier->update(['due' => max(0, (float) $supplier->total_purchase - (float) $supplier->total_paid)]);
            }
        }

        if ($transaction->sales_man_id) {
            $salesMan = SalesMan::find($transaction->sales_man_id);
            if ($salesMan) {
                $expenseAmount = $transaction->direction === 'out' ? $amount : -1 * $amount;
                $salesMan->update([
                    'total_expense' => max(0, (float) $salesMan->total_expense + $expenseAmount),
                ]);
            }
        }

        foreach ([
            'tailor_id' => Tailor::class,
            'carry_man_id' => CarryMan::class,
            'computer_man_id' => ComputerMan::class,
            'garey_man_id' => GareyMan::class,
        ] as $field => $modelClass) {
            if (! $transaction->{$field}) {
                continue;
            }

            $worker = $modelClass::find($transaction->{$field});

            if (! $worker) {
                continue;
            }

            $paidAmount = $transaction->direction === 'out' ? $amount : -1 * $amount;
            $worker->update([
                'total_paid' => max(0, (float) $worker->total_paid + $paidAmount),
            ]);
            $worker->refresh();
            $worker->recalculateFinancials();
        }
    }

    private function authorizeShop(CashTransaction $transaction): void
    {
        if (! auth()->user()->canManageAllShops()) {
            abort_unless($transaction->shop_id === auth()->user()->shop_id, 403);
        }
    }
}
