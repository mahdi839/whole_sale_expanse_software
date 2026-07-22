<?php

namespace App\Http\Controllers;

use App\Models\CarryMan;
use App\Models\CarryManWorkLog;
use App\Support\SimplePdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class CarryManWorkLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $workLogs = CarryManWorkLog::query()
            ->with('carryMan')
            ->when($search, fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('memo_no', 'like', "%{$search}%")
                    ->orWhere('marka', 'like', "%{$search}%")
                    ->orWhereHas('carryMan', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
            }))
            ->latest('date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('carry_man_work_logs.index', compact('workLogs', 'search'));
    }

    public function create()
    {
        $workLog = new CarryManWorkLog(['date' => now()->toDateString()]);
        $carryMen = CarryMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('carry_man_work_logs.create', compact('workLog', 'carryMen'));
    }

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $logs = CarryManWorkLog::with('carryMan')
            ->when($search, fn ($query) => $query->where(function ($match) use ($search) {
                $match->where('memo_no', 'like', "%{$search}%")
                    ->orWhere('marka', 'like', "%{$search}%")
                    ->orWhereHas('carryMan', fn ($worker) => $worker->where('name', 'like', "%{$search}%"));
            }))->latest('date')->latest()->get();

        return $this->workLogPdf('Carry Man Work Logs', ['Date', 'Memo No', 'Carry Man', 'Marka', 'Bale Qty', 'KG', 'Received KG', 'Balance KG', 'Rate/KG', 'Total'], $logs->map(fn ($log) => [
            optional($log->date)->format('Y-m-d'), $log->memo_no ?: '-', $log->carryMan?->name ?? '-', $log->marka ?: '-',
            $log->bale_qty, $log->total_unit_kg, $log->received_qty, (float) $log->total_unit_kg - (float) $log->received_qty,
            $log->rate_per_kg, $log->total_rate,
        ]), 'carry-man-work-logs');
    }

    private function workLogPdf(string $title, array $headers, $rows, string $fileName)
    {
        return Response::make(SimplePdf::table('Inaya Creation - '.$title, $headers, $rows, null, ['logo_path' => public_path('inaya_creation_logo.jpeg')]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'-'.now()->format('Y-m-d-H-i-s').'.pdf"',
        ]);
    }

    public function store(Request $request)
    {
        $workLog = CarryManWorkLog::create($this->validated($request));
        $workLog->carryMan?->recalculateFinancials();

        return redirect()->route('carry-man-work-logs.index')->with('success', 'Carry man work log created successfully.');
    }

    public function edit(CarryManWorkLog $carryManWorkLog)
    {
        $carryMen = CarryMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('carry_man_work_logs.edit', compact('carryManWorkLog', 'carryMen'));
    }

    public function update(Request $request, CarryManWorkLog $carryManWorkLog)
    {
        $oldCarryManId = $carryManWorkLog->carry_man_id;
        $data = $this->validated($request, $carryManWorkLog);

        if (isset($data['document_path']) && $carryManWorkLog->document_path) {
            Storage::disk('public')->delete($carryManWorkLog->document_path);
        }

        $carryManWorkLog->update($data);
        $carryManWorkLog->fresh('carryMan')->carryMan?->recalculateFinancials();

        if ($oldCarryManId !== $carryManWorkLog->carry_man_id) {
            CarryMan::find($oldCarryManId)?->recalculateFinancials();
        }

        return redirect()->route('carry-man-work-logs.index')->with('success', 'Carry man work log updated successfully.');
    }

    public function destroy(CarryManWorkLog $carryManWorkLog)
    {
        $carryMan = $carryManWorkLog->carryMan;

        if ($carryManWorkLog->document_path) {
            Storage::disk('public')->delete($carryManWorkLog->document_path);
        }

        $carryManWorkLog->delete();
        $carryMan?->recalculateFinancials();

        return redirect()->route('carry-man-work-logs.index')->with('success', 'Carry man work log deleted successfully.');
    }

    public function receive(Request $request, CarryManWorkLog $carryManWorkLog)
    {
        $data = $request->validate([
            'received_qty' => 'required|numeric|min:0|max:'.$carryManWorkLog->total_unit_kg,
        ]);

        $carryManWorkLog->update([
            'received_qty' => round((float) $data['received_qty'], 2),
        ]);

        return redirect()->route('carry-man-work-logs.index')->with('success', 'Received quantity updated successfully.');
    }

    private function validated(Request $request, ?CarryManWorkLog $workLog = null): array
    {
        $data = $request->validate([
            'carry_man_id' => 'required|exists:carry_men,id',
            'date' => 'required|date',
            'memo_no' => 'nullable|string|max:100',
            'marka' => 'nullable|string|max:255',
            'document_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'bale_qty' => 'required|numeric|min:0',
            'total_unit_kg' => 'required|numeric|min:0',
            'rate_per_kg' => 'required|numeric|min:0',
        ]);

        $data['total_rate'] = round((float) $data['total_unit_kg'] * (float) $data['rate_per_kg'], 2);

        if ($request->hasFile('document_path')) {
            $data['document_path'] = $request->file('document_path')->store('carry-man-work-logs/documents', 'public');
        } elseif ($workLog?->exists) {
            unset($data['document_path']);
        }

        return $data;
    }
}
