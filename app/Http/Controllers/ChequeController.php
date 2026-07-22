<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Shop;
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
            'shop_id' => $request->input('shop_id'),
        ];

        $cheques = Cheque::with(['customer', 'shop'])
            ->when(! auth()->user()->canManageAllShops(), fn ($query) => $query->where('shop_id', auth()->user()->shop_id ?: -1))
            ->when(auth()->user()->canManageAllShops() && $filters['shop_id'], fn ($query) => $query->where('shop_id', $filters['shop_id']))
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

        $shops = auth()->user()->canManageAllShops() ? Shop::where('is_active', true)->orderBy('name')->get() : collect();

        return view('cheques.index', compact('cheques', 'filters', 'shops'));
    }

    public function create()
    {
        return view('cheques.create', [
            'cheque' => new Cheque(['issue_date' => now()->toDateString(), 'status' => 'pending']),
            'customers' => $this->customers(),
            'shops' => $this->shops(),
        ]);
    }

    public function store(Request $request)
    {
        Cheque::create($this->validated($request));

        return redirect()->route('cheques.index')->with('success', 'Cheque saved successfully.');
    }

    public function show(Cheque $cheque)
    {
        $this->authorizeShop($cheque);
        $cheque->load(['customer', 'shop']);

        return view('cheques.show', compact('cheque'));
    }

    public function edit(Cheque $cheque)
    {
        $this->authorizeShop($cheque);

        return view('cheques.edit', [
            'cheque' => $cheque,
            'customers' => $this->customers(),
            'shops' => $this->shops(),
        ]);
    }

    public function update(Request $request, Cheque $cheque)
    {
        $this->authorizeShop($cheque);
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
        $this->authorizeShop($cheque);
        if ($cheque->documents) {
            Storage::disk('public')->delete($cheque->documents);
        }

        $cheque->delete();

        return redirect()->route('cheques.index')->with('success', 'Cheque deleted successfully.');
    }

    private function validated(Request $request, ?Cheque $cheque = null): array
    {
        $data = $request->validate([
            'shop_id' => 'nullable|exists:shops,id',
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

        $data['shop_id'] = $this->resolveShopId($data, $cheque);
        abort_unless(
            Customer::whereKey($data['customer_id'])->where('shop_id', $data['shop_id'])->exists(),
            422,
            'The selected customer belongs to another shop.'
        );

        if ($request->hasFile('documents')) {
            $data['documents'] = $request->file('documents')->store('cheques', 'public');
        } else {
            unset($data['documents']);
        }

        return $data;
    }

    private function customers()
    {
        return Customer::query()
            ->when(! auth()->user()->canManageAllShops(), fn ($query) => $query->where('shop_id', auth()->user()->shop_id ?: -1))
            ->orderBy('full_name')
            ->get(['id', 'shop_id', 'full_name', 'phone']);
    }

    private function shops()
    {
        return auth()->user()->canManageAllShops()
            ? Shop::where('is_active', true)->orderBy('name')->get()
            : collect([auth()->user()->shop])->filter();
    }

    private function resolveShopId(array $data, ?Cheque $cheque = null): int
    {
        if (auth()->user()->canManageAllShops()) {
            $shopId = $data['shop_id'] ?? $cheque?->shop_id;
            abort_unless($shopId, 422, 'Please select a shop.');

            return (int) $shopId;
        }

        abort_unless(auth()->user()->shop_id, 403, 'No shop assigned to your user.');

        return (int) auth()->user()->shop_id;
    }

    private function authorizeShop(Cheque $cheque): void
    {
        if (! auth()->user()->canManageAllShops()) {
            abort_unless($cheque->shop_id === auth()->user()->shop_id, 403);
        }
    }
}
