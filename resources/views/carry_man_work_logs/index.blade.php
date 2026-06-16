<x-app-layout>
    <x-slot name="header">Carry Man Work Logs</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('carry-man-work-logs.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search carry man or marka..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>
            @canany(['manage carry man work logs', 'create carry man work logs'])
                <a href="{{ route('carry-man-work-logs.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">Add Work Log</a>
            @endcanany
        </div>

        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Carry Man</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Marka</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Document</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Bale Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">KG</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Rate/KG</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workLogs as $workLog)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($workLog->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $workLog->carryMan?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $workLog->marka ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    @if($workLog->document_path)
                                        <a href="{{ asset('storage/'.$workLog->document_path) }}" target="_blank" class="text-blue-600 hover:underline">View</a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->bale_qty, 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->total_unit_kg, 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->rate_per_kg, 2) }}</td>
                                <td class="px-5 py-3 text-right text-green-600">{{ number_format($workLog->total_rate, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @canany(['manage carry man work logs', 'edit carry man work logs'])
                                            <a href="{{ route('carry-man-work-logs.edit', $workLog) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage carry man work logs', 'delete carry man work logs'])
                                            <form method="POST" action="{{ route('carry-man-work-logs.destroy', $workLog) }}" onsubmit="return confirm('Delete this work log?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-5 py-12 text-center text-gray-400">No work logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($workLogs->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $workLogs->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
