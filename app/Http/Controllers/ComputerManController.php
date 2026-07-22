<?php

namespace App\Http\Controllers;

use App\Models\ComputerMan;
use App\Support\WorkerProfilePdf;
use Illuminate\Http\Request;

class ComputerManController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $computerMen = ComputerMan::query()
            ->withCount('workLogs')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('computer_men.index', compact('computerMen', 'search'));
    }

    public function create()
    {
        return view('computer_men.create', ['computerMan' => new ComputerMan]);
    }

    public function store(Request $request)
    {
        ComputerMan::create($this->validated($request));

        return redirect()->route('computer-men.index')->with('success', 'Computer man profile created successfully.');
    }

    public function edit(ComputerMan $computerMan)
    {
        return view('computer_men.edit', compact('computerMan'));
    }

    public function show(ComputerMan $computerMan)
    {
        $computerMan->load([
            'workLogs.product' => fn ($query) => $query->orderBy('product_name'),
            'cashTransactions' => fn ($query) => $query->latest('date')->latest(),
        ]);
        $workLogs = $computerMan->workLogs()->with('product')->latest('date')->latest()->get();

        return view('shared._worker_profile_show', [
            'worker' => $computerMan,
            'title' => 'Computer Man Details',
            'routeBase' => 'computer-men',
            'workLogType' => 'computer',
            'workLogs' => $workLogs,
            'cashTransactions' => $computerMan->cashTransactions,
            'totalWorkAmount' => $workLogs->sum(fn ($log) => (float) $log->total_rate),
        ]);
    }

    public function update(Request $request, ComputerMan $computerMan)
    {
        $computerMan->update($this->validated($request));

        return redirect()->route('computer-men.index')->with('success', 'Computer man profile updated successfully.');
    }

    public function exportPdf(ComputerMan $computerMan)
    {
        $workLogs = $computerMan->workLogs()->with('product')->latest('date')->latest()->get();

        return WorkerProfilePdf::download($computerMan, 'Computer Man Profile and Work Logs', 'computer', $workLogs);
    }

    public function destroy(ComputerMan $computerMan)
    {
        if ($computerMan->workLogs()->exists()) {
            return redirect()->route('computer-men.index')->with('error', 'This computer man has work logs and cannot be deleted.');
        }

        $computerMan->delete();

        return redirect()->route('computer-men.index')->with('success', 'Computer man profile deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'total_paid' => 'nullable|numeric|min:0',
            'total_due' => 'nullable|numeric|min:0',
            'advance' => 'nullable|numeric|min:0',
        ]);

        foreach (['total_paid', 'total_due', 'advance'] as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        return $data;
    }
}
