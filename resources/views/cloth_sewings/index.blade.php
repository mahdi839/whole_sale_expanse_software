<x-app-layout>
    <x-slot name="header">Cloth Sewing</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('cloth-sewings.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search tailor, product, design code..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>

            @canany(['manage cloth sewings', 'create cloth sewings'])
                <a href="{{ route('cloth-sewings.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">
                    Add Sewing
                </a>
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
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Tailor</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Design Code</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($clothSewings as $item)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ $item->date->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $item->tailor?->name ?? $item->tailor_name }}</td>
                                <td class="px-5 py-3 text-gray-700">{{ $item->product?->product_name ?? '-' }}</td>
                                <td class="px-5 py-3"><span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">{{ $item->product?->sku ?? '-' }}</span></td>
                                <td class="px-5 py-3 text-right">{{ number_format($item->item_qty, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @canany(['manage cloth sewings', 'edit cloth sewings'])
                                            <a href="{{ route('cloth-sewings.edit', $item) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage cloth sewings', 'delete cloth sewings'])
                                            <form method="POST" action="{{ route('cloth-sewings.destroy', $item) }}" onsubmit="return confirm('Delete this record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No cloth sewing records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clothSewings->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $clothSewings->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
