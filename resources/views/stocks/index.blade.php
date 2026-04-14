<x-app-layout>
    <x-slot name="header">Stocks</x-slot>

    <div class="space-y-4">

        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-2 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Total Items</p>
                <p class="text-2xl font-semibold text-gray-800">
                    {{ number_format($stocks->count()) }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">Total Stock Qty</p>
                <p class="text-2xl font-semibold text-green-600">
                    {{ number_format($stocks->sum('stock_qty')) }}
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">#</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Product ID</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Stock Qty</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Created</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($stocks as $stock)
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="px-5 py-3.5 align-top font-medium text-gray-700">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-mono font-medium">
                                        {{ $stock->product_id }}
                                    </span>
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-green-600 tabular-nums">
                                    {{ number_format($stock->stock_qty) }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-gray-500 hidden md:table-cell">
                                    {{ $stock->created_at?->format('d M Y') }}
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('stocks.edit', $stock->id) }}"
                                           class="inline-flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium
                                                  text-blue-700 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('stocks.destroy', $stock->id) }}"
                                              onsubmit="return confirm('Delete this stock item?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium
                                                           text-red-700 bg-red-50 border border-red-100 rounded-lg hover:bg-red-100 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                                            <path d="M8 11h8M8 15h5"/>
                                        </svg>
                                        <p class="text-sm">
                                            No stock records found.
                                            <a href="{{ route('stocks.create') }}" class="text-blue-600 hover:underline">Add your first stock.</a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>