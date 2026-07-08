<?php

namespace App\Http\Controllers;

use App\Models\Tailor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TailorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $tailors = Tailor::query()
            ->withSum('clothSewings as sewing_qty', 'item_qty')
            ->withSum('receivedCloths as received_qty', 'item_qty')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();
        return view('tailors.index', compact('tailors', 'search'));
    }

    public function create()
    {
        return view('tailors.create', ['tailor' => new Tailor()]);
    }

    public function store(Request $request)
    {
        Tailor::create($this->validated($request));

        return redirect()->route('tailors.index')->with('success', 'Tailor profile created successfully.');
    }

    public function show(Tailor $tailor)
    {
        $tailor->load([
            'cashTransactions' => fn ($query) => $query->latest('date')->latest(),
        ]);
        $workLogs = $tailor->clothSewings()->with('product')->latest('date')->latest()->get();

        return view('shared._worker_profile_show', [
            'worker' => $tailor,
            'title' => 'Tailor Details',
            'routeBase' => 'tailors',
            'workLogType' => 'tailor',
            'workLogs' => $workLogs,
            'cashTransactions' => $tailor->cashTransactions,
            'totalWorkAmount' => $workLogs->sum(fn ($log) => (float) $log->total_rate),
        ]);
    }

    public function edit(Tailor $tailor)
    {
        return view('tailors.edit', compact('tailor'));
    }

    public function update(Request $request, Tailor $tailor)
    {
        $data = $this->validated($request, $tailor);

        foreach (['profile_picture', 'document_path'] as $field) {
            if (isset($data[$field]) && $tailor->{$field}) {
                Storage::disk('public')->delete($tailor->{$field});
            }
        }

        $tailor->update($data);

        return redirect()->route('tailors.index')->with('success', 'Tailor profile updated successfully.');
    }

    public function destroy(Tailor $tailor)
    {
        Storage::disk('public')->delete(array_filter([
            $tailor->profile_picture,
            $tailor->document_path,
        ]));

        $tailor->delete();

        return redirect()->route('tailors.index')->with('success', 'Tailor profile deleted successfully.');
    }

    private function validated(Request $request, ?Tailor $tailor = null): array
    {
        $uniqueName = Rule::unique('tailors', 'name');

        if ($tailor?->exists) {
            $uniqueName->ignore($tailor->id);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', $uniqueName],
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'document_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('tailors/profile-pictures', 'public');
        }

        if ($request->hasFile('document_path')) {
            $data['document_path'] = $request->file('document_path')->store('tailors/documents', 'public');
        }

        return $data;
    }
}
