<?php

namespace App\Http\Controllers;

use App\Models\GareyMan;
use Illuminate\Http\Request;

class GareyManController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $gareyMen = GareyMan::query()
            ->withCount('workLogs')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('nid_passport_no', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('garey_men.index', compact('gareyMen', 'search'));
    }

    public function create()
    {
        return view('garey_men.create', ['gareyMan' => new GareyMan()]);
    }

    public function store(Request $request)
    {
        GareyMan::create($this->validated($request));

        return redirect()->route('garey-men.index')->with('success', 'Garey man profile created successfully.');
    }

    public function edit(GareyMan $gareyMan)
    {
        return view('garey_men.edit', compact('gareyMan'));
    }

    public function show(GareyMan $gareyMan)
    {
        $gareyMan->load([
            'workLogs' => fn ($query) => $query->latest('date')->latest(),
            'cashTransactions' => fn ($query) => $query->latest('date')->latest(),
        ]);

        return view('shared._worker_profile_show', [
            'worker' => $gareyMan,
            'title' => 'Garey Man Details',
            'routeBase' => 'garey-men',
            'workLogType' => 'garey',
            'workLogs' => $gareyMan->workLogs,
            'cashTransactions' => $gareyMan->cashTransactions,
            'totalWorkAmount' => $gareyMan->workLogs->sum(fn ($log) => (float) $log->total_rate),
        ]);
    }

    public function update(Request $request, GareyMan $gareyMan)
    {
        $gareyMan->update($this->validated($request));

        return redirect()->route('garey-men.index')->with('success', 'Garey man profile updated successfully.');
    }

    public function destroy(GareyMan $gareyMan)
    {
        if ($gareyMan->workLogs()->exists()) {
            return redirect()->route('garey-men.index')->with('error', 'This garey man has work logs and cannot be deleted.');
        }

        $gareyMan->delete();

        return redirect()->route('garey-men.index')->with('success', 'Garey man profile deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'nid_passport_no' => 'nullable|string|max:100',
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
