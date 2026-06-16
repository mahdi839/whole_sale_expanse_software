<x-app-layout>
    <x-slot name="header">Computer Man Work Logs</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('computer-man-work-logs.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search computer man or product..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>
            @canany(['manage computer man work logs', 'create computer man work logs'])
                <a href="{{ route('computer-man-work-logs.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">Add Work Log</a>
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
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Computer Man</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Design Code</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Design Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Received Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Rate/Piece</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workLogs as $workLog)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($workLog->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $workLog->computerMan?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $workLog->product?->product_name ?? '-' }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $workLog->product?->sku ?: ($workLog->product?->product_code ?: '-') }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->computer_design_qty, 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->received_qty, 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->rate_per_piece, 2) }}</td>
                                <td class="px-5 py-3 text-right text-green-600">{{ number_format($workLog->total_rate, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @canany(['manage computer man work logs', 'edit computer man work logs'])
                                            <a href="{{ route('computer-man-work-logs.edit', $workLog) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage computer man work logs', 'delete computer man work logs'])
                                            <form method="POST" action="{{ route('computer-man-work-logs.destroy', $workLog) }}" onsubmit="return confirm('Delete this work log?')">
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
