<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\SalesMan;
use App\Services\CashLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'category' => $request->input('category'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $expenses = Expense::query()
            ->with('salesMan')
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];

                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('category', 'like', "%{$s}%")
                        ->orWhere('note', 'like', "%{$s}%");
                });
            })
            ->when($filters['category'], fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $totals = Expense::query()
            ->when($filters['search'], function ($q) use ($filters) {
                $s = $filters['search'];

                $q->where(function ($sub) use ($s) {
                    $sub->where('reference', 'like', "%{$s}%")
                        ->orWhere('category', 'like', "%{$s}%")
                        ->orWhere('note', 'like', "%{$s}%");
                });
            })
            ->when($filters['category'], fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['date_from'], fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when($filters['date_to'], fn ($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->selectRaw('count(*) as total_expenses, sum(amount) as total_amount')
            ->first();

        $categories = $this->categories();
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('expenses.index', compact('expenses', 'filters', 'totals', 'categories', 'salesMen'));
    }

    public function create()
    {
        $nextReference = Expense::generateReference();
        $categories = $this->categories();
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('expenses.create', compact('nextReference', 'categories', 'salesMen'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateExpense($request);

        $validated['reference'] = $validated['reference'] ?? Expense::generateReference();

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('expenses', 'public');
        }

        DB::transaction(function () use ($validated) {
            $expense = Expense::create($validated);
            $this->syncCash($expense);
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense)
    {
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = $this->categories();
        $salesMen = SalesMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('expenses.edit', compact('expense', 'categories', 'salesMen'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $this->validateExpense($request, $expense->id);

        if ($request->hasFile('document')) {
            if ($expense->document) {
                Storage::disk('public')->delete($expense->document);
            }

            $validated['document'] = $request->file('document')->store('expenses', 'public');
        }

        DB::transaction(function () use ($expense, $validated) {
            $expense->update($validated);
            $this->syncCash($expense->fresh());
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->document) {
            Storage::disk('public')->delete($expense->document);
        }

        DB::transaction(function () use ($expense) {
            app(CashLedger::class)->deleteSource('expense', $expense->id);
            $expense->delete();
        });

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'expenses-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $expenses = Expense::query()
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->latest()
            ->get();

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Reference',
                'Category',
                'Amount',
                'Date',
                'Note',
                'Created At',
            ]);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->reference,
                    $expense->category,
                    $expense->amount,
                    optional($expense->date)->format('Y-m-d'),
                    $expense->note,
                    $expense->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function validateExpense(Request $request, ?int $expenseId = null): array
    {
        return $request->validate([
            'reference' => 'nullable|string|max:50|unique:expenses,reference,' . $expenseId,
            'category' => 'required|string|max:100',
            'sales_man_id' => 'nullable|exists:sales_men,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120',
        ]);
    }

    private function categories(): array
    {
        return [
            'Rent',
            'Salary',
            'Transport',
            'Utility',
            'Office',
            'Food',
            'Maintenance',
            'Marketing',
            'Others',
        ];
    }

    private function syncCash(Expense $expense): void
    {
        app(CashLedger::class)->syncSource('expense', $expense->id, 'out', 'expense', (float) $expense->amount, [
            'date' => $expense->date?->toDateString() ?? now()->toDateString(),
            'note' => 'Expense: '.$expense->reference.' - '.$expense->category,
        ]);
    }
}
