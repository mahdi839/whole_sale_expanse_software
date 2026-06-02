<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $employees = Employee::query()
            ->withSum('salaries as paid_salary_total', 'amount')
            ->withSum('salaryAdvances as advance_salary_total', 'amount')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('documents', 'like', "%{$search}%")
                    ->orWhere('employment_type', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        $employee = new Employee(['joining_date' => now()->toDateString()]);

        return view('employees.create', compact('employee'));
    }

    public function store(Request $request)
    {
        Employee::create($this->validated($request));

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load([
            'salaries' => fn ($query) => $query->latest('salary_month'),
            'salaryAdvances',
        ]);

        $advanceAmounts = $employee->salaryAdvances
            ->groupBy(fn ($advance) => $advance->advance_month?->format('Y-m'))
            ->map(fn ($advances) => (float) $advances->sum('amount'));

        return view('employees.show', compact('employee', 'advanceAmounts'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $employee->update($this->validated($request));

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'documents' => 'nullable|string|max:255',
            'employment_type' => 'nullable|string|max:100',
            'joining_date' => 'nullable|date',
            'salary_amount' => 'required|numeric|min:0',
        ]);
    }
}
