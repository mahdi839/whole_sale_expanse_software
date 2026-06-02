<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Salary;
use App\Models\SalaryAdvance;
use App\Services\CashLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalaryController extends Controller
{
    public function index()
    {
        $employees = Employee::orderBy('name')->get(['id', 'name', 'phone', 'salary_amount']);
        $salaries = Salary::with('employee')->latest('salary_month')->latest()->paginate(15);
        $months = collect(range(0, 23))->map(fn ($offset) => now()->startOfMonth()->subMonths($offset));
        $advanceAmounts = SalaryAdvance::get(['employee_id', 'advance_month', 'amount'])
            ->groupBy('employee_id')
            ->map(fn ($rows) => $rows
                ->groupBy(fn ($advance) => $advance->advance_month?->format('Y-m'))
                ->map(fn ($monthAdvances) => (float) $monthAdvances->sum('amount')));

        return view('salaries.index', compact('employees', 'salaries', 'months', 'advanceAmounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'salary_month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
        ]);

        $validated['salary_month'] = Carbon::createFromFormat('Y-m', $validated['salary_month'])
            ->startOfMonth()
            ->toDateString();

        if (Salary::where('employee_id', $validated['employee_id'])->where('salary_month', $validated['salary_month'])->exists()) {
            throw ValidationException::withMessages([
                'salary_month' => 'Salary for this employee and month has already been submitted.',
            ]);
        }

        DB::transaction(function () use ($validated) {
            $salary = Salary::create($validated);
            $this->syncCash($salary);
        });

        return redirect()->route('salaries.index')->with('success', 'Salary submitted successfully.');
    }

    private function syncCash(Salary $salary): void
    {
        $salary->loadMissing('employee');

        app(CashLedger::class)->syncSource('salary', $salary->id, 'out', 'salary', (float) $salary->amount, [
            'date' => $salary->salary_month?->toDateString() ?? now()->toDateString(),
            'note' => 'Salary: '.$salary->employee?->name.' - '.$salary->salary_month?->format('F Y'),
        ]);
    }
}
