<x-app-layout>
    <x-slot name="header">Sales Men</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('sales-men.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, phone, address..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>

            <a href="{{ route('sales-men.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">
                Add Sales Man
            </a>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Phone</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Address</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Joining Date</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total Expense</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($salesMen as $salesMan)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $salesMan->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $salesMan->phone ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $salesMan->address ?? '-' }}</td>
                                <td class="px-5 py-3">{{ optional($salesMan->joining_date)->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($salesMan->total_expense, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('sales-men.show', $salesMan) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100" title="View transactions">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        <a href="{{ route('sales-men.edit', $salesMan) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        <form method="POST" action="{{ route('sales-men.destroy', $salesMan) }}" onsubmit="return confirm('Delete this sales man?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No sales men found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($salesMen->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $salesMen->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
