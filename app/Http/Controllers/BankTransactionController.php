<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\CarryMan;
use App\Models\ComputerMan;
use App\Models\Customer;
use App\Models\GareyMan;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\Tailor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BankTransactionController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $filters = [
            'shop_id' => $request->input('shop_id'),
            'search' => $request->input('search'),
            'direction' => $request->input('direction'),
            'entry_type' => $request->input('entry_type'),
            'bank_name' => $request->input('bank_name'),
            'date_from' => $request->input('date_from', $today),
            'date_to' => $request->input('date_to', $today),
        ];

        $query = BankTransaction::query()
            ->with(['shop', 'customer', 'supplier', 'tailor', 'carryMan', 'computerMan', 'gareyMan'])
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
            ->when($filters['direction'], fn ($q) => $q->where('direction', $filters['direction']))
            ->when($filters['entry_type'], fn ($q) => $q->where('entry_type', $filters['entry_type']))
            ->when($filters['bank_name'], fn ($q) => $q->where('bank_name', 'like', '%'.$filters['bank_name'].'%'))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('bank_name', 'like', "%{$s}%")
                        ->orWhere('bank_details', 'like', "%{$s}%")
                        ->orWhere('note', 'like', "%{$s}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('full_name', 'like', "%{$s}%")->orWhere('address', 'like', "%{$s}%"))
                        ->orWhereHas('supplier', fn ($sp) => $sp->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('tailor', fn ($tailor) => $tailor->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('carryMan', fn ($worker) => $worker->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('computerMan', fn ($worker) => $worker->where('name', 'like', "%{$s}%"))
                        ->orWhereHas('gareyMan', fn ($worker) => $worker->where('name', 'like', "%{$s}%"));
                });
            });

        $transactions = (clone $query)->latest('date')->latest('id')->paginate(15)->withQueryString();

        $totals = (clone $query)->selectRaw('
            COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE 0 END), 0) as bank_in,
            COALESCE(SUM(CASE WHEN direction = "out" THEN amount ELSE 0 END), 0) as bank_out,
            COUNT(*) as entries
        ')->first();

        $overallBalance = BankTransaction::query()
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE -amount END), 0) as balance')
            ->value('balance');

        $dateBalance = BankTransaction::query()
            ->when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($q) => $q->where('shop_id', $filters['shop_id']))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = "in" THEN amount ELSE -amount END), 0) as balance')
            ->value('balance');

        $shops = auth()->user()->canManageAllShops() ? Shop::where('is_active', true)->orderBy('name')->get() : collect();

        return view('bank_transactions.index', compact('transactions', 'filters', 'totals', 'overallBalance', 'dateBalance', 'shops'));
    }

    public function create()
    {
        $transaction = new BankTransaction([
            'date' => now()->toDateString(),
            'direction' => 'in',
            'type' => 'manual',
        ]);

        return view('bank_transactions.create', $this->formData($transaction));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $transaction = BankTransaction::create($this->validated($request));
            $this->applyPartyPayment($transaction, 1);
        });

        return redirect()->route('bank-transactions.index')->with('success', 'Bank transaction saved successfully.');
    }

    public function edit(BankTransaction $bankTransaction)
    {
        abort_if($bankTransaction->source_type, 403, 'Automatic bank entries are edited from their source document.');
        $this->authorizeShop($bankTransaction);

        return view('bank_transactions.edit', $this->formData($bankTransaction));
    }

    public function update(Request $request, BankTransaction $bankTransaction)
    {
        $this->authorizeShop($bankTransaction);
        abort_if($bankTransaction->source_type, 403, 'Automatic bank entries are edited from their source document.');

        DB::transaction(function () use ($request, $bankTransaction) {
            $this->applyPartyPayment($bankTransaction, -1);
            $bankTransaction->update($this->validated($request, $bankTransaction));
            $this->applyPartyPayment($bankTransaction->fresh(), 1);
        });

        return redirect()->route('bank-transactions.index')->with('success', 'Bank transaction updated successfully.');
    }

    public function destroy(BankTransaction $bankTransaction)
    {
        $this->authorizeShop($bankTransaction);
        abort_if($bankTransaction->source_type, 403, 'Automatic bank entries are deleted from their source document.');

        DB::transaction(function () use ($bankTransaction) {
            $this->applyPartyPayment($bankTransaction, -1);
            if ($bankTransaction->document) {
                Storage::disk('public')->delete($bankTransaction->document);
            }
            $bankTransaction->delete();
        });

        return redirect()->route('bank-transactions.index')->with('success', 'Bank transaction deleted successfully.');
    }

    private function formData(BankTransaction $transaction): array
    {
        return [
            'transaction' => $transaction,
            'customers' => Customer::when(! auth()->user()->canManageAllShops(), fn ($q) => $q->where('shop_id', auth()->user()->shop_id ?: -1))
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'phone', 'address']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name', 'phone', 'due']),
            'tailors' => Tailor::orderBy('name')->get(['id', 'name']),
            'carryMen' => CarryMan::orderBy('name')->get(['id', 'name', 'phone']),
            'computerMen' => ComputerMan::orderBy('name')->get(['id', 'name', 'phone']),
            'gareyMen' => GareyMan::orderBy('name')->get(['id', 'name', 'phone']),
            'shops' => auth()->user()->canManageAllShops()
                ? Shop::where('is_active', true)->orderBy('name')->get()
                : collect([auth()->user()->shop]),
        ];
    }

    private function validated(Request $request, ?BankTransaction $transaction = null): array
    {
        $data = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
            'direction' => ['required', Rule::in(['in', 'out'])],
            'bank_name' => 'required|string|max:100',
            'bank_details' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'entry_type' => ['nullable', Rule::in(['customer', 'supplier', 'tailor', 'computer', 'carry_man', 'garey_man'])],
            'customer_id' => 'nullable|required_if:entry_type,customer|exists:customers,id',
            'supplier_id' => 'nullable|required_if:entry_type,supplier|exists:suppliers,id',
            'tailor_id' => 'nullable|required_if:entry_type,tailor|exists:tailors,id',
            'carry_man_id' => 'nullable|required_if:entry_type,carry_man|exists:carry_men,id',
            'computer_man_id' => 'nullable|required_if:entry_type,computer|exists:computer_men,id',
            'garey_man_id' => 'nullable|required_if:entry_type,garey_man|exists:garey_men,id',
            'note' => 'nullable|string|max:2000',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ]);

        if (auth()->user()->canManageAllShops()) {
            abort_unless(! empty($data['shop_id']), 422, 'Please select a shop.');
        } else {
            abort_unless(auth()->user()->shop_id, 403, 'No shop assigned to your user.');
            $data['shop_id'] = auth()->user()->shop_id;
        }

        $data['type'] = 'manual';
        $entryType = $data['entry_type'] ?? null;

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

        if ($request->hasFile('document')) {
            if ($transaction?->document) {
                Storage::disk('public')->delete($transaction->document);
            }
            $data['document'] = $request->file('document')->store('bank-transactions', 'public');
        } else {
            unset($data['document']);
        }

        return $data;
    }

    private function applyPartyPayment(BankTransaction $transaction, int $multiplier): void
    {
        $amount = (float) $transaction->amount * $multiplier;
        $customerAmount = $transaction->direction === 'in' ? $amount : -1 * $amount;
        $supplierAmount = $transaction->direction === 'out' ? $amount : -1 * $amount;

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

    private function authorizeShop(BankTransaction $transaction): void
    {
        if (! auth()->user()->canManageAllShops()) {
            abort_unless($transaction->shop_id === auth()->user()->shop_id, 403);
        }
    }
}
