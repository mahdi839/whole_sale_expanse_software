<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryAdvance;
use App\Services\CashLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        $advances = SalaryAdvance::with('employee')->latest('advance_month')->latest()->paginate(15);

        return view('salary_advances.index', compact('advances'));
    }

    public function create()
    {
        return view('salary_advances.create', [
            'advance' => new SalaryAdvance(['advance_month' => now()->startOfMonth()]),
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'phone']),
            'months' => $this->months(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated) {
            $advance = SalaryAdvance::create($validated);
            $this->syncCash($advance);
        });

        return redirect()->route('salary-advances.index')->with('success', 'Advance salary created successfully.');
    }

    public function edit(SalaryAdvance $salaryAdvance)
    {
        return view('salary_advances.edit', [
            'advance' => $salaryAdvance,
            'employees' => Employee::orderBy('name')->get(['id', 'name', 'phone']),
            'months' => $this->months(),
        ]);
    }

    public function update(Request $request, SalaryAdvance $salaryAdvance)
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($salaryAdvance, $validated) {
            $salaryAdvance->update($validated);
            $this->syncCash($salaryAdvance->fresh());
        });

        return redirect()->route('salary-advances.index')->with('success', 'Advance salary updated successfully.');
    }

    public function destroy(SalaryAdvance $salaryAdvance)
    {
        DB::transaction(function () use ($salaryAdvance) {
            app(CashLedger::class)->deleteSource('salary_advance', $salaryAdvance->id);
            $salaryAdvance->delete();
        });

        return redirect()->route('salary-advances.index')->with('success', 'Advance salary deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'advance_month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $validated['advance_month'] = Carbon::createFromFormat('Y-m', $validated['advance_month'])
            ->startOfMonth()
            ->toDateString();

        return $validated;
    }

    private function months()
    {
        return collect(range(0, 23))->map(fn ($offset) => now()->startOfMonth()->subMonths($offset));
    }

    private function syncCash(SalaryAdvance $advance): void
    {
        $advance->loadMissing('employee');

        app(CashLedger::class)->syncSource('salary_advance', $advance->id, 'out', 'salary_advance', (float) $advance->amount, [
            'date' => $advance->advance_month?->toDateString() ?? now()->toDateString(),
            'note' => 'Advance salary: '.$advance->employee?->name.' - '.$advance->advance_month?->format('F Y'),
        ]);
    }
}
