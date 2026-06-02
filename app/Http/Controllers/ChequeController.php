<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ChequeController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ];

        $cheques = Cheque::with('customer')
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['search'], function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($sub) use ($search) {
                    $sub->where('cheque_no', 'like', "%{$search}%")
                        ->orWhere('bank', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($customer) => $customer->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->latest('issue_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('cheques.index', compact('cheques', 'filters'));
    }

    public function create()
    {
        return view('cheques.create', [
            'cheque' => new Cheque(['issue_date' => now()->toDateString(), 'status' => 'pending']),
            'customers' => $this->customers(),
        ]);
    }

    public function store(Request $request)
    {
        Cheque::create($this->validated($request));

        return redirect()->route('cheques.index')->with('success', 'Cheque saved successfully.');
    }

    public function show(Cheque $cheque)
    {
        $cheque->load('customer');

        return view('cheques.show', compact('cheque'));
    }

    public function edit(Cheque $cheque)
    {
        return view('cheques.edit', [
            'cheque' => $cheque,
            'customers' => $this->customers(),
        ]);
    }

    public function update(Request $request, Cheque $cheque)
    {
        $data = $this->validated($request, $cheque);

        $oldDocument = $cheque->documents;

        $cheque->update($data);

        if (($data['documents'] ?? null) && $oldDocument) {
            Storage::disk('public')->delete($oldDocument);
        }

        return redirect()->route('cheques.index')->with('success', 'Cheque updated successfully.');
    }

    public function destroy(Cheque $cheque)
    {
        if ($cheque->documents) {
            Storage::disk('public')->delete($cheque->documents);
        }

        $cheque->delete();

        return redirect()->route('cheques.index')->with('success', 'Cheque deleted successfully.');
    }

    private function validated(Request $request, ?Cheque $cheque = null): array
    {
        $data = $request->validate([
            'cheque_no' => ['required', 'string', 'max:255', Rule::unique('cheques', 'cheque_no')->ignore($cheque?->id)],
            'customer_id' => 'required|exists:customers,id',
            'bank' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'deposit_date' => 'nullable|date',
            'status' => ['required', Rule::in(['pending', 'received'])],
            'documents' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
            'note' => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('documents')) {
            $data['documents'] = $request->file('documents')->store('cheques', 'public');
        } else {
            unset($data['documents']);
        }

        return $data;
    }

    private function customers()
    {
        return Customer::orderBy('full_name')->get(['id', 'full_name', 'phone']);
    }
}
