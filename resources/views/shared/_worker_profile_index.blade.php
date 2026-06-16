<x-app-layout>
    <x-slot name="header">{{ $title }}</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route($routeBase.'.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search name, phone, address..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>

            @canany(['manage '.$permissionBase, 'create '.$permissionBase])
                <a href="{{ route($routeBase.'.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">
                    Add {{ $singularTitle }}
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
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Phone</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Address</th>
                            @if($hasDocumentNo ?? false)
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">NID / Passport</th>
                            @endif
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Advance</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Logs</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workers as $worker)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $worker->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $worker->phone ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $worker->address ?? '-' }}</td>
                                @if($hasDocumentNo ?? false)
                                    <td class="px-5 py-3 text-gray-600">{{ $worker->nid_passport_no ?? '-' }}</td>
                                @endif
                                <td class="px-5 py-3 text-right text-green-600">{{ number_format($worker->total_paid ?? 0, 2) }}</td>
                                <td class="px-5 py-3 text-right text-red-600">{{ number_format($worker->total_due ?? 0, 2) }}</td>
                                <td class="px-5 py-3 text-right text-indigo-600">{{ number_format($worker->advance ?? 0, 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($worker->work_logs_count ?? 0) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @canany(['manage '.$permissionBase, 'view '.$permissionBase])
                                            <a href="{{ route($routeBase.'.show', $worker) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100" title="View details">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endcanany
                                        @canany(['manage '.$permissionBase, 'edit '.$permissionBase])
                                            <a href="{{ route($routeBase.'.edit', $worker) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage '.$permissionBase, 'delete '.$permissionBase])
                                            <form method="POST" action="{{ route($routeBase.'.destroy', $worker) }}" onsubmit="return confirm('Delete this profile?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ ($hasDocumentNo ?? false) ? 9 : 8 }}" class="px-5 py-12 text-center text-gray-400">No profiles found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($workers->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $workers->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
