<?php

namespace App\Http\Controllers;

use App\Models\GareyMan;
use App\Models\GareyManWorkLog;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GareyManWorkLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $workLogs = GareyManWorkLog::query()
            ->with('gareyMan')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('memo_no', 'like', "%{$search}%")
                    ->orWhereHas('gareyMan', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
            }))
            ->latest('date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('garey_man_work_logs.index', compact('workLogs', 'search'));
    }

    public function create()
    {
        $workLog = new GareyManWorkLog(['date' => now()->toDateString(), 'unit' => 'goj']);
        $gareyMen = GareyMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('garey_man_work_logs.create', compact('workLog', 'gareyMen'));
    }

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $logs = GareyManWorkLog::with('gareyMan')
            ->when($search, fn ($query) => $query->where(function ($match) use ($search) {
                $match->where('memo_no', 'like', "%{$search}%")
                    ->orWhereHas('gareyMan', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
            }))->latest('date')->latest()->get();
        $rows = $logs->map(fn ($log) => [
            optional($log->date)->format('Y-m-d'), $log->memo_no ?: '-', $log->gareyMan?->name ?? '-', $log->qty,
            $log->received_qty, (float) $log->qty - (float) $log->received_qty, $log->unit, $log->rate_per_goj, $log->total_rate,
        ]);

        return Response::make(SimplePdf::table('Inaya Creation - Garey Man Work Logs', ['Date', 'Memo No', 'Garey Man', 'Qty', 'Received Qty', 'Balance', 'Unit', 'Rate/Goj', 'Total'], $rows, null, ['logo_path' => public_path('inaya_creation_logo.jpeg')]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="garey-man-work-logs-'.now()->format('Y-m-d-H-i-s').'.pdf"',
        ]);
    }

    public function store(Request $request)
    {
        $workLog = GareyManWorkLog::create($this->validated($request));
        $workLog->gareyMan?->recalculateFinancials();

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Garey man work log created successfully.');
    }

    public function edit(GareyManWorkLog $gareyManWorkLog)
    {
        $gareyMen = GareyMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('garey_man_work_logs.edit', compact('gareyManWorkLog', 'gareyMen'));
    }

    public function update(Request $request, GareyManWorkLog $gareyManWorkLog)
    {
        $oldGareyManId = $gareyManWorkLog->garey_man_id;
        $gareyManWorkLog->update($this->validated($request));
        $gareyManWorkLog->fresh('gareyMan')->gareyMan?->recalculateFinancials();

        if ($oldGareyManId !== $gareyManWorkLog->garey_man_id) {
            GareyMan::find($oldGareyManId)?->recalculateFinancials();
        }

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Garey man work log updated successfully.');
    }

    public function destroy(GareyManWorkLog $gareyManWorkLog)
    {
        $gareyMan = $gareyManWorkLog->gareyMan;
        $gareyManWorkLog->delete();
        $gareyMan?->recalculateFinancials();

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Garey man work log deleted successfully.');
    }

    public function receive(Request $request, GareyManWorkLog $gareyManWorkLog)
    {
        $data = $request->validate([
            'received_qty' => 'required|numeric|min:0|max:'.$gareyManWorkLog->qty,
        ]);

        $gareyManWorkLog->update([
            'received_qty' => round((float) $data['received_qty'], 2),
        ]);

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Received quantity updated successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'garey_man_id' => 'required|exists:garey_men,id',
            'date' => 'required|date',
            'memo_no' => 'nullable|string|max:100',
            'qty' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
            'rate_per_goj' => 'required|numeric|min:0',
        ]);

        $data['total_rate'] = round((float) $data['qty'] * (float) $data['rate_per_goj'], 2);

        return $data;
    }
}
