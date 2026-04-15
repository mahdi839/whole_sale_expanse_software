<x-app-layout>
    <x-slot name="header">Sale Returns</x-slot>

    <div class="space-y-4">

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v4a1 1 0 01-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter card --}}
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="GET" action="{{ route('sale-returns.index') }}">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-3.5">

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Ref, product, memo…"
                               class="w-full pl-9 pr-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                      placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>
                    </div>

                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}"
                           class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                  text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition"/>

                    <select name="return_status"
                            class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                        <option value="">Return status</option>
                        @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($filters['return_status'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>

                    <select name="return_type"
                            class="w-full px-3 h-9 text-sm bg-gray-50 border border-gray-200 rounded-lg
                                   text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                        <option value="">Return type</option>
                        @foreach(['refund'=>'Refund','exchange'=>'Exchange','credit'=>'Credit'] as $v=>$l)
                            <option value="{{ $v }}" {{ ($filters['return_type'] ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit"
                            class="h-9 px-4 text-sm font-medium text-white bg-gray-800 rounded-lg transition">
                        Filter
                    </button>
                    <a href="{{ route('sale-returns.index') }}"
                       class="h-9 px-4 inline-flex items-center text-sm font-medium text-white bg-cyan-600 rounded-lg hover:bg-cyan-700 transition">
                        Reset
                    </a>
                    <span class="flex-1"></span>
                    <a href="{{ route('sale-returns.export', request()->query()) }}"
                       class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-green-700
                              bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16"/>
                        </svg>
                        Download CSV
                    </a>
                    <a href="{{ route('sale-returns.create') }}"
                       class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Return
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-3 gap-3">
            @foreach([
                ['label' => 'Total Returns',       'value' => number_format($totals->total_returns ?? 0),              'color' => 'text-gray-800'],
                ['label' => 'Total Qty Returned',  'value' => number_format($totals->total_qty ?? 0, 2),               'color' => 'text-gray-800'],
                ['label' => 'Total Return Amount', 'value' => '৳'.number_format($totals->total_return_amount ?? 0, 2), 'color' => 'text-red-600'],
            ] as $card)
            <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1.5">{{ $card['label'] }}</p>
                <p class="text-xl font-semibold {{ $card['color'] }}">{{ $card['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Reference</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Original Sale</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Customer</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden lg:table-cell">Product</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Qty</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide hidden md:table-cell">Amount</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Status</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($returns as $return)
                            <tr class="hover:bg-gray-50/60 transition-colors">

                                <td class="px-5 py-3.5 align-top">
                                    <a href="{{ route('sale-returns.show', $return) }}"
                                       class="inline-block px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 text-xs font-mono font-medium hover:bg-orange-100 transition">
                                        {{ $return->reference }}
                                    </a>
                                    <p class="text-xs text-gray-400 mt-1">{{ optional($return->date)->format('d M Y') }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top hidden md:table-cell">
                                    @if($return->sale)
                                        <a href="{{ route('sales.show', $return->sale) }}"
                                           class="inline-block px-2 py-0.5 rounded-md bg-violet-50 text-violet-700 text-xs font-mono font-medium hover:bg-violet-100 transition">
                                            {{ $return->sale->reference }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <p class="font-medium text-gray-800">{{ $return->customer?->full_name ?? '—' }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top hidden lg:table-cell">
                                    <p class="font-medium text-gray-800">{{ $return->product_name ?? '—' }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $return->product_code ?: '—' }}</p>
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-gray-700 tabular-nums">
                                    {{ number_format($return->qty, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top text-right font-medium text-red-600 tabular-nums hidden md:table-cell">
                                    ৳{{ number_format($return->return_amount, 2) }}
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'inline-flex items-center w-fit px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-green-50 text-green-700'  => $return->return_status === 'approved',
                                            'bg-amber-50 text-amber-700'  => $return->return_status === 'pending',
                                            'bg-red-50 text-red-700'      => $return->return_status === 'rejected',
                                        ])>{{ ucfirst($return->return_status) }}</span>

                                        <span @class([
                                            'inline-flex items-center w-fit px-2 py-0.5 rounded-full text-xs font-medium',
                                            'bg-blue-50 text-blue-700'    => $return->return_type === 'refund',
                                            'bg-purple-50 text-purple-700'=> $return->return_type === 'exchange',
                                            'bg-gray-100 text-gray-600'   => $return->return_type === 'credit',
                                        ])>{{ ucfirst($return->return_type) }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3.5 align-top">
                                    <div class="flex items-center justify-end gap-1.5">

                                        {{-- Approve button (only for pending) --}}
                                        @if($return->return_status === 'pending')
                                            <form method="POST" action="{{ route('sale-returns.approve', $return) }}"
                                                  onsubmit="return confirm('Approve this return? Stock and financials will be updated.')">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 h-7 px-2.5 text-xs font-medium
                                                               text-green-700 bg-green-50 border border-green-100 rounded-lg hover:bg-green-100 transition">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                        <path d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Approve
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('sale-returns.edit', $return) }}"
                                           class="inline-flex items-center gap-1.5 h-7 px-2.5 text-xs font-medium
                                                  text-blue-700 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('sale-returns.destroy', $return) }}"
                                              onsubmit="return confirm('Delete return {{ addslashes($return->reference) }}?')">
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
                                <td colspan="8" class="px-5 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        <p class="text-sm">No returns found.
                                            <a href="{{ route('sale-returns.create') }}" class="text-blue-600 hover:underline">Add your first return.</a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($returns->hasPages())
                <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>

        @if($returns->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $returns->firstItem() }}–{{ $returns->lastItem() }}
                of {{ number_format($returns->total()) }} returns
            </p>
        @endif

    </div>
</x-app-layout>