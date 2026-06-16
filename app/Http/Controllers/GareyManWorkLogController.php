<?php

namespace App\Http\Controllers;

use App\Models\GareyMan;
use App\Models\GareyManWorkLog;
use Illuminate\Http\Request;

class GareyManWorkLogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $workLogs = GareyManWorkLog::query()
            ->with('gareyMan')
            ->when($search, fn ($query) => $query->whereHas('gareyMan', fn ($worker) => $worker->where('name', 'like', "%{$search}%")))
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

    public function store(Request $request)
    {
        GareyManWorkLog::create($this->validated($request));

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Garey man work log created successfully.');
    }

    public function edit(GareyManWorkLog $gareyManWorkLog)
    {
        $gareyMen = GareyMan::orderBy('name')->get(['id', 'name', 'phone']);

        return view('garey_man_work_logs.edit', compact('gareyManWorkLog', 'gareyMen'));
    }

    public function update(Request $request, GareyManWorkLog $gareyManWorkLog)
    {
        $gareyManWorkLog->update($this->validated($request));

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Garey man work log updated successfully.');
    }

    public function destroy(GareyManWorkLog $gareyManWorkLog)
    {
        $gareyManWorkLog->delete();

        return redirect()->route('garey-man-work-logs.index')->with('success', 'Garey man work log deleted successfully.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'garey_man_id' => 'required|exists:garey_men,id',
            'date' => 'required|date',
            'qty' => 'required|numeric|min:0',
            'unit' => 'required|string|max:30',
            'rate_per_goj' => 'required|numeric|min:0',
        ]);

        $data['total_rate'] = round((float) $data['qty'] * (float) $data['rate_per_goj'], 2);

        return $data;
    }
}
