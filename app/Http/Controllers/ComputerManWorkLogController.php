<?php

namespace App\Http\Controllers;

use App\Models\ComputerMan;
use App\Models\ComputerManWorkLog;
use App\Models\Product;
use Illuminate\Http\Request;

class ComputerManWorkLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $workLogs = ComputerManWorkLog::query()
            ->with(['computerMan', 'product'])
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('memo_no', 'like', "%{$search}%")
                    ->orWhereHas('computerMan', fn ($worker) => $worker->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($product) => $product
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('product_code', 'like', "%{$search}%"));
            }))
            ->latest('date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('computer_man_work_logs.index', compact('workLogs', 'search'));
    }

    public function create()
    {
        $workLog = new ComputerManWorkLog(['date' => now()->toDateString()]);
        $computerMen = ComputerMan::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('computer_man_work_logs.create', compact('workLog', 'computerMen', 'products'));
    }

    public function store(Request $request)
    {
        $workLog = ComputerManWorkLog::create($this->validated($request));
        $workLog->computerMan?->recalculateFinancials();

        return redirect()->route('computer-man-work-logs.index')->with('success', 'Computer man work log created successfully.');
    }

    public function edit(ComputerManWorkLog $computerManWorkLog)
    {
        $computerMen = ComputerMan::orderBy('name')->get(['id', 'name', 'phone']);
        $products = Product::orderBy('product_name')->get(['id', 'product_name', 'sku', 'product_code']);

        return view('computer_man_work_logs.edit', compact('computerManWorkLog', 'computerMen', 'products'));
    }

    public function update(Request $request, ComputerManWorkLog $computerManWorkLog)
    {
        $oldComputerManId = $computerManWorkLog->computer_man_id;
        $computerManWorkLog->update($this->validated($request));
        $computerManWorkLog->fresh('computerMan')->computerMan?->recalculateFinancials();

        if ($oldComputerManId !== $computerManWorkLog->computer_man_id) {
            ComputerMan::find($oldComputerManId)?->recalculateFinancials();
        }

        return redirect()->route('computer-man-work-logs.index')->with('success', 'Computer man work log updated successfully.');
    }

    public function destroy(ComputerManWorkLog $computerManWorkLog)
    {
        $computerMan = $computerManWorkLog->computerMan;
        $computerManWorkLog->delete();
        $computerMan?->recalculateFinancials();

        return redirect()->route('computer-man-work-logs.index')->with('success', 'Computer man work log deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'computer_man_id' => 'required|exists:computer_men,id',
            'product_id' => 'required|exists:products,id',
            'date' => 'required|date',
            'memo_no' => 'nullable|string|max:100',
            'computer_design_qty' => 'required|numeric|min:0',
            'received_qty' => 'required|numeric|min:0',
            'rate_per_piece' => 'required|numeric|min:0',
        ]);

        $data['total_rate'] = round((float) $data['computer_design_qty'] * (float) $data['rate_per_piece'], 2);

        return $data;
    }
}
