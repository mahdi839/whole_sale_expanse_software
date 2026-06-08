<x-app-layout>
    <x-slot name="header">Customers</x-slot>

    <div class="space-y-4">

        {{-- Top bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            {{-- Search --}}
            <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:max-w-md">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Search by name, code or phone…"
                        class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                    >
                        Search
                    </button>

                    @if($search)
                        <a
                            href="{{ route('customers.index') }}"
                            class="inline-flex items-center justify-center px-3 py-2 text-sm text-gray-500 bg-white border border-gray-200 rounded-lg hover:text-gray-700 hover:bg-gray-50 transition"
                            title="Clear"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            <a
                href="{{ route('customers.create') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shrink-0 w-full sm:w-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Customer
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Customers</p>
                <p class="text-2xl font-semibold text-gray-800">{{ number_format($summary['count']) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Sales</p>
                <p class="text-2xl font-semibold text-gray-800 break-words">BDT {{ number_format($summary['total_sale'], 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Paid</p>
                <p class="text-2xl font-semibold text-green-600 break-words">BDT {{ number_format($summary['total_paid'], 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Due</p>
                <p class="text-2xl font-semibold break-words {{ $summary['total_due'] > 0 ? 'text-red-600' : 'text-gray-400' }}">BDT {{ number_format($summary['total_due'], 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-500 mb-1">Total Sale Qty</p>
                <p class="text-2xl font-semibold text-indigo-600">{{ number_format($summary['total_sell_qty'], 2) }}</p>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @forelse($customers as $customer)
                <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($customer->image)
                                <img src="{{ asset('storage/'.$customer->image) }}" alt="{{ $customer->full_name }}" class="w-10 h-10 rounded-full object-cover border border-gray-200 shrink-0">
                            @endif
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">Code</p>
                                <p class="font-mono text-sm text-blue-700 break-all">{{ $customer->code }}</p>
                            </div>
                        </div>

                        <div class="shrink-0 text-right">
                            @if($customer->due > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-medium">
                                    ৳{{ number_format($customer->due, 2) }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-medium">
                                    Paid
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <a
                            href="{{ route('customers.show', $customer) }}"
                            class="font-medium text-gray-800 hover:text-blue-600 transition break-words"
                        >
                            {{ $customer->full_name }}
                        </a>
                        <p class="text-sm text-gray-500 mt-1 break-all">
                            {{ $customer->phone ?? '—' }}
                        </p>
                        @if($customer->alternative_phone)
                            <p class="text-xs text-gray-400 mt-0.5 break-all">
                                Alt: {{ $customer->alternative_phone }}
                            </p>
                        @endif
                        @if($customer->address)
                            <p class="text-xs text-gray-400 mt-1 break-words">{{ $customer->address }}</p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Sale</p>
                            <p class="font-medium text-gray-700 break-words">৳{{ number_format($customer->total_sale, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Paid</p>
                            <p class="font-medium text-green-600 break-words">৳{{ number_format($customer->total_paid, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Sale Qty</p>
                            <p class="font-medium text-indigo-600 break-words">{{ number_format($customer->total_sell_qty ?? 0, 2) }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <a
                            href="{{ route('customers.show', $customer) }}"
                            title="View transactions"
                            class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                        <a
                            href="{{ route('customers.edit', $customer) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="{{ route('customers.destroy', $customer) }}"
                            class="flex-1"
                            onsubmit="return confirm('Delete {{ addslashes($customer->full_name) }}?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-12 text-center">
                    <div class="flex flex-col items-center gap-3 text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <p class="text-sm">
                            @if($search)
                                No customers found for <span class="font-medium text-gray-600">"{{ $search }}"</span>
                            @else
                                No customers yet.
                                <a href="{{ route('customers.create') }}" class="text-blue-600 hover:underline">Add your first customer.</a>
                            @endif
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="text-left px-5 py-3 font-medium text-gray-500 whitespace-nowrap">Code</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Full Name</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden md:table-cell">Phone</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden xl:table-cell">Address</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">total sold Product Qty</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Total Sale</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500 hidden lg:table-cell">Total Paid</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Due</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                {{-- Code --}}
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-mono font-medium">
                                        {{ $customer->code }}
                                    </span>
                                </td>

                                {{-- Name --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        @if($customer->image)
                                            <img src="{{ asset('storage/'.$customer->image) }}" alt="{{ $customer->full_name }}" class="w-9 h-9 rounded-full object-cover border border-gray-200 shrink-0">
                                        @endif
                                        <a href="{{ route('customers.show', $customer) }}" class="font-medium text-gray-800 hover:text-blue-600 transition">{{ $customer->full_name }}</a>
                                    </div>
                                </td>

                                {{-- Phone --}}
                                <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                                    {{ $customer->phone ?? '—' }}
                                    @if($customer->alternative_phone)
                                        <div class="text-xs text-gray-400 mt-0.5">Alt: {{ $customer->alternative_phone }}</div>
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-gray-500 hidden xl:table-cell max-w-xs truncate">
                                    {{ $customer->address ?? '—' }}
                                </td>

                                <td class="px-5 py-3 text-right text-indigo-600 hidden lg:table-cell">
                                    {{ number_format($customer->total_sell_qty ?? 0, 2) }}
                                </td>

                                {{-- Total Sale --}}
                                <td class="px-5 py-3 text-right text-gray-700 hidden lg:table-cell">
                                    ৳{{ number_format($customer->total_sale, 2) }}
                                </td>

                                {{-- Total Paid --}}
                                <td class="px-5 py-3 text-right text-green-600 hidden lg:table-cell">
                                    ৳{{ number_format($customer->total_paid, 2) }}
                                </td>

                                {{-- Due --}}
                                <td class="px-5 py-3 text-right">
                                    @if($customer->due > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-medium">
                                            ৳{{ number_format($customer->due, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-medium">
                                            Paid
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ route('customers.show', $customer) }}"
                                            title="View transactions"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium text-gray-700 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        <a
                                            href="{{ route('customers.edit', $customer) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('customers.destroy', $customer) }}"
                                            onsubmit="return confirm('Delete {{ addslashes($customer->full_name) }}?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition"
                                            >
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
                                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <p class="text-sm">
                                            @if($search)
                                                No customers found for <span class="font-medium text-gray-600">"{{ $search }}"</span>
                                            @else
                                                No customers yet.
                                                <a href="{{ route('customers.create') }}" class="text-blue-600 hover:underline">Add your first customer.</a>
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

        @if($customers->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $customers->firstItem() }}–{{ $customers->lastItem() }} of {{ $customers->total() }} customers
                @if($search) matching <span class="text-gray-600">"{{ $search }}"</span>@endif
            </p>
        @endif

    </div>
</x-app-layout>



