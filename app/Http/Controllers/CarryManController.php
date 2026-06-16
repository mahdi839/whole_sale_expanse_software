<?php

namespace App\Http\Controllers;

use App\Models\CarryMan;
use Illuminate\Http\Request;

class CarryManController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $carryMen = CarryMan::query()
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

        return view('carry_men.index', compact('carryMen', 'search'));
    }

    public function create()
    {
        return view('carry_men.create', ['carryMan' => new CarryMan()]);
    }

    public function store(Request $request)
    {
        CarryMan::create($this->validated($request));

        return redirect()->route('carry-men.index')->with('success', 'Carry man profile created successfully.');
    }

    public function edit(CarryMan $carryMan)
    {
        return view('carry_men.edit', compact('carryMan'));
    }

    public function update(Request $request, CarryMan $carryMan)
    {
        $carryMan->update($this->validated($request));

        return redirect()->route('carry-men.index')->with('success', 'Carry man profile updated successfully.');
    }

    public function destroy(CarryMan $carryMan)
    {
        if ($carryMan->workLogs()->exists()) {
            return redirect()->route('carry-men.index')->with('error', 'This carry man has work logs and cannot be deleted.');
        }

        $carryMan->delete();

        return redirect()->route('carry-men.index')->with('success', 'Carry man profile deleted successfully.');
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
