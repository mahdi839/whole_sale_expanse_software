<x-app-layout>
    <x-slot name="header">Tailors</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('tailors.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, phone, address..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>

            @canany(['manage cloth sewings', 'create cloth sewings'])
                <a href="{{ route('tailors.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">
                    Add Tailor
                </a>
            @endcanany
        </div>

        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ session('error') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Tailor</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Document</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Phone</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Address</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Sewing Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Received Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($tailors as $tailor)
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($tailor->profile_picture)
                                            <img src="{{ asset('storage/'.$tailor->profile_picture) }}" alt="{{ $tailor->name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-sm font-semibold">
                                                {{ strtoupper(substr($tailor->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-800">{{ $tailor->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    @if($tailor->document_path)
                                        <a href="{{ asset('storage/'.$tailor->document_path) }}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:underline">
                                            <img src="{{ asset('storage/'.$tailor->document_path) }}" alt="{{ $tailor->name }} document" class="w-12 h-9 rounded object-cover border border-gray-200">
                                            <span class="text-xs">View</span>
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $tailor->phone ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $tailor->address ?? '-' }}</td>
                                <td class="px-5 py-3 text-right text-indigo-600">{{ number_format($tailor->sewing_qty ?? 0, 2) }}</td>
                                <td class="px-5 py-3 text-right text-green-600">{{ number_format($tailor->received_qty ?? 0, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('tailors.show', $tailor) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100" title="View profile">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                        @canany(['manage cloth sewings', 'edit cloth sewings'])
                                            <a href="{{ route('tailors.edit', $tailor) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage cloth sewings', 'delete cloth sewings'])
                                            <form method="POST" action="{{ route('tailors.destroy', $tailor) }}" onsubmit="return confirm('Delete this tailor?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No tailors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tailors->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $tailors->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
