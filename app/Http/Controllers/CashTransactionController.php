<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SalesMan;
use App\Models\Supplier;
use App\Models\Tailor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->with(['customer', 'supplier', 'salesMan', 'tailor'])
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
                        ->orWhereHas('tailor', fn($tailor) => $tailor->where('name', 'like', "%{$s}%"));
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
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);
        $tailors = Tailor::orderBy('name')->get(['id', 'name']);

        return view('cash_transactions.create', compact('transaction', 'customers', 'suppliers', 'salesMen', 'tailors'));
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
        $customers = Customer::orderBy('full_name')->get(['id', 'full_name', 'phone']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name', 'phone']);
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);
        $tailors = Tailor::orderBy('name')->get(['id', 'name']);

        return view('cash_transactions.edit', compact('transaction', 'customers', 'suppliers', 'salesMen', 'tailors'));
    }

    public function update(Request $request, CashTransaction $cashTransaction)
    {
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
            'direction' => ['required', Rule::in(['in', 'out'])],
            'type' => ['required', Rule::in(['manual_add', 'collection', 'manual_out'])],
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:100',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'sales_man_id' => 'nullable|exists:sales_men,id',
            'tailor_id' => 'nullable|exists:tailors,id',
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

    private function applyPartyPayment(CashTransaction $transaction, int $multiplier): void
    {
        $amount = (float) $transaction->amount * $multiplier;
        $customerAmount = $transaction->direction === 'in' ? $amount : -1 * $amount;
        $supplierAmount = $transaction->direction === 'out' ? $amount : -1 * $amount;

        if ($transaction->customer_id) {
            $customer = Customer::find($transaction->customer_id);
            if ($customer) {
                $customer->increment('total_paid', $customerAmount);
                $customer->recalculateDue();
                $this->applyCustomerPaymentToSales((int) $transaction->customer_id, $customerAmount);
            }
        }

        if ($transaction->supplier_id) {
            $supplier = Supplier::find($transaction->supplier_id);
            if ($supplier) {
                $supplier->increment('total_paid', $supplierAmount);
                $supplier->update(['due' => max(0, (float) $supplier->total_purchase - (float) $supplier->total_paid)]);
                $this->applySupplierPaymentToPurchases((int) $transaction->supplier_id, $supplierAmount);
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
    }

    private function applyCustomerPaymentToSales(int $customerId, float $amount): void
    {
        if ($amount > 0) {
            $remaining = $amount;

            Sale::where('customer_id', $customerId)
                ->where('due', '>', 0)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get()
                ->each(function (Sale $sale) use (&$remaining) {
                    if ($remaining <= 0) {
                        return false;
                    }

                    $paid = min($remaining, (float) $sale->due);
                    $this->applySalePaymentDelta($sale, $paid);
                    $remaining -= $paid;

                    return true;
                });

            return;
        }

        if ($amount < 0) {
            $remaining = abs($amount);

            Sale::where('customer_id', $customerId)
                ->where('paid', '>', 0)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->each(function (Sale $sale) use (&$remaining) {
                    if ($remaining <= 0) {
                        return false;
                    }

                    $reversed = min($remaining, (float) $sale->paid);
                    $this->applySalePaymentDelta($sale, -1 * $reversed);
                    $remaining -= $reversed;

                    return true;
                });
        }
    }

    private function applySalePaymentDelta(Sale $sale, float $delta): void
    {
        $grandTotal = max(0, (float) $sale->grand_total - (float) $sale->return_amount);
        $paid = min($grandTotal, max(0, (float) $sale->paid + $delta));
        $due = max(0, $grandTotal - $paid);

        $sale->update([
            'paid' => $paid,
            'due' => $due,
            'payment_status' => match (true) {
                $due <= 0 => 'paid',
                $paid > 0 => 'partial',
                default => 'due',
            },
        ]);
    }

    private function applySupplierPaymentToPurchases(int $supplierId, float $amount): void
    {
        if ($amount > 0) {
            $remaining = $amount;

            Purchase::where('supplier_id', $supplierId)
                ->where('due_amount', '>', 0)
                ->orderBy('date')
                ->orderBy('id')
                ->get()
                ->each(function (Purchase $purchase) use (&$remaining) {
                    if ($remaining <= 0) {
                        return false;
                    }

                    $paid = min($remaining, (float) $purchase->due_amount);
                    $this->applyPurchasePaymentDelta($purchase, $paid);
                    $remaining -= $paid;

                    return true;
                });

            return;
        }

        if ($amount < 0) {
            $remaining = abs($amount);

            Purchase::where('supplier_id', $supplierId)
                ->where('paid_amount', '>', 0)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
                ->each(function (Purchase $purchase) use (&$remaining) {
                    if ($remaining <= 0) {
                        return false;
                    }

                    $reversed = min($remaining, (float) $purchase->paid_amount);
                    $this->applyPurchasePaymentDelta($purchase, -1 * $reversed);
                    $remaining -= $reversed;

                    return true;
                });
        }
    }

    private function applyPurchasePaymentDelta(Purchase $purchase, float $delta): void
    {
        $grandTotal = (float) $purchase->grand_total;
        $paid = min($grandTotal, max(0, (float) $purchase->paid_amount + $delta));
        $due = max(0, $grandTotal - $paid);

        $purchase->update([
            'paid_amount' => $paid,
            'due_amount' => $due,
            'payment_status' => match (true) {
                $due <= 0 => 'paid',
                $paid > 0 => 'partial',
                default => 'due',
            },
        ]);
    }
}
