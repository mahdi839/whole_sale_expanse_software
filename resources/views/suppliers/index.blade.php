<x-app-layout>
    <x-slot name="header">Suppliers</x-slot>

    <div class="space-y-4">

        {{-- Top bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            <form method="GET" action="{{ route('suppliers.index') }}"
                  class="flex items-center gap-2 w-full sm:max-w-md">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                           placeholder="Search by name, code, phone or address..."
                           class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"/>
                </div>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-lg
                               text-gray-700 hover:bg-gray-50 transition whitespace-nowrap">
                    Search
                </button>
                @if($search)
                    <a href="{{ route('suppliers.index') }}"
                       class="p-2 text-gray-400 hover:text-gray-600 transition" title="Clear">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                @endif
            </form>

            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('suppliers.export.pdf', request()->only('search')) }}"
                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition shrink-0">
                    PDF
                </a>
                <a href="{{ route('suppliers.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white
                          bg-blue-600 rounded-lg hover:bg-blue-700 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Supplier
                </a>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 text-sm text-green-800 bg-green-50
                        border border-green-200 rounded-lg">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Suppliers</p>
                <p class="text-2xl font-semibold text-gray-800">{{ number_format($totals->cnt) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Purchase Qty</p>
                <p class="text-2xl font-semibold text-indigo-600">{{ number_format($totals->total_purchase_qty ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Purchase</p>
                @foreach($currencyTotals as $currencyTotal)
                    <p class="text-lg font-semibold text-gray-800">
                        {{ $currencyTotal->currency }} {{ number_format($currencyTotal->total_purchase, 2) }}
                    </p>
                @endforeach
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Paid</p>
                @foreach($currencyTotals as $currencyTotal)
                    <p class="text-lg font-semibold text-green-600">
                        {{ $currencyTotal->currency }} {{ number_format($currencyTotal->total_paid, 2) }}
                    </p>
                @endforeach
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Due</p>
                @foreach($currencyTotals as $currencyTotal)
                    <p class="text-lg font-semibold {{ $currencyTotal->total_due > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        {{ $currencyTotal->currency }} {{ number_format($currencyTotal->total_due, 2) }}
                    </p>
                @endforeach
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="text-left px-5 py-3 font-medium text-gray-500 whitespace-nowrap">Code</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Name</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden md:table-cell">Phone</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Address</th><th class="text-right px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Total Qty</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Purchase</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Paid</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Due</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-gray-50/50 transition-colors">

                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md
                                                 bg-violet-50 text-violet-700 text-xs font-mono font-medium">
                                        {{ $supplier->code }}
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                       class="font-medium text-gray-800 hover:text-blue-600 transition">
                                        {{ $supplier->name }}
                                    </a>
                                </td>

                                <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                                    {{ $supplier->phone ?? '—' }}
                                </td>

                                <td class="px-5 py-3 text-gray-500 hidden lg:table-cell max-w-xs truncate">{{ $supplier->address ?? '—' }}</td><td class="px-5 py-3 text-right text-indigo-600 hidden lg:table-cell">{{ number_format($supplier->total_purchase_qty ?? 0, 2) }}</td>

                                <td class="px-5 py-3 text-right text-gray-700 hidden lg:table-cell">
                                    {{ $supplier->currency }} {{ number_format($supplier->total_purchase, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right text-green-600 hidden lg:table-cell">
                                    {{ $supplier->currency }} {{ number_format($supplier->total_paid, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right">
                                    @if($supplier->due > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                     bg-red-50 text-red-700 text-xs font-medium">
                                            {{ $supplier->currency }} {{ number_format($supplier->due, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full
                                                     bg-green-50 text-green-700 text-xs font-medium">
                                            Cleared
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('suppliers.show', $supplier) }}"
                                           class="inline-flex items-center justify-center w-8 h-8 text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                                           title="View transactions">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                                  text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                              onsubmit="return confirm('Delete {{ addslashes($supplier->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                                           text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                <td colspan="9" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                                        </svg>
                                        <p class="text-sm">
                                            @if($search)
                                                No suppliers found for <span class="font-medium text-gray-600">"{{ $search }}"</span>
                                            @else
                                                No suppliers yet.
                                                <a href="{{ route('suppliers.create') }}" class="text-blue-600 hover:underline">Add your first supplier.</a>
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($suppliers->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>

        @if($suppliers->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}
                of {{ $suppliers->total() }} suppliers
                @if($search) matching <span class="text-gray-600">"{{ $search }}"</span>@endif
            </p>
        @endif

    </div>
</x-app-layout>
