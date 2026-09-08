<x-app-layout>
    <x-slot name="header">Track Missing Products</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('missing-products.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search product, design code, note..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <select name="product_id" class="tom-select h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">All products</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(($filters['product_id'] ?? '') == $product->id)>
                                {{ $product->displayLabel() }}
                            </option>
                        @endforeach
                    </select>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
                    <a href="{{ route('missing-products.index') }}" class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center">Reset</a>
                    @canany(['manage products', 'create products'])
                        <a href="{{ route('missing-products.create') }}" class="sm:ml-auto h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center">+ Track Missing Product</a>
                    @endcanany
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl px-5 py-4 flex items-center justify-between">
            <p class="text-sm text-gray-500">Filtered missing quantity</p>
            <p class="text-lg font-semibold text-rose-600">{{ number_format($totalMissingQty ?? 0, 2) }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Design Code</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Missing Qty</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Document</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($missingProducts as $entry)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($entry->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $entry->product?->product_name ?? '-' }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $entry->product?->sku ?: ($entry->product?->product_code ?: '-') }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-rose-600">{{ number_format($entry->missing_qty, 2) }}</td>
                                <td class="px-5 py-3">
                                    @if($entry->document)
                                        <a href="{{ asset('storage/'.$entry->document) }}" target="_blank" class="text-xs text-indigo-600">View</a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-500 max-w-xs">{{ $entry->note ?: '-' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @canany(['manage products', 'edit products'])
                                            <a href="{{ route('missing-products.edit', $entry) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage products', 'delete products'])
                                            <form method="POST" action="{{ route('missing-products.destroy', $entry) }}" onsubmit="return confirm('Delete this missing product entry?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-gray-400">No missing product entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($missingProducts->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $missingProducts->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
