<x-app-layout>
    <x-slot name="header">Cash Management</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Current Cash</p>
                <p class="text-xl font-semibold text-emerald-600">৳{{ number_format($balance ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Cash In</p>
                <p class="text-xl font-semibold text-blue-600">৳{{ number_format($totals->cash_in ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Cash Out</p>
                <p class="text-xl font-semibold text-red-600">৳{{ number_format($totals->cash_out ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('cash-transactions.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, party, note..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <select name="direction" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">All directions</option>
                        <option value="in" @selected(($filters['direction'] ?? '') === 'in')>Cash in</option>
                        <option value="out" @selected(($filters['direction'] ?? '') === 'out')>Cash out</option>
                    </select>
                    <select name="type" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">All types</option>
                        @foreach(['manual_add' => 'Add money', 'collection' => 'Collection', 'manual_out' => 'Cash out', 'sale_payment' => 'Sale payment', 'purchase_payment' => 'Purchase payment', 'sale_return_refund' => 'Sale return', 'purchase_return_refund' => 'Purchase return'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
                    <a href="{{ route('cash-transactions.index') }}" class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center">Reset</a>
                    <a href="{{ route('cash-transactions.create') }}" class="sm:ml-auto h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center">+ New Cash Entry</a>
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Party</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">{{ $transaction->reference }}</span>
                                    @if($transaction->source_type)
                                        <div class="text-xs text-gray-400 mt-1">Auto: {{ str_replace('_', ' ', $transaction->source_type) }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td>
                                <td class="px-5 py-3">{{ $transaction->customer?->full_name ?? $transaction->supplier?->name ?? $transaction->salesMan?->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $transaction->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $transaction->direction === 'in' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-5 py-3">{{ optional($transaction->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right">
                                    @if(! $transaction->source_type)
                                        <a href="{{ route('cash-transactions.edit', $transaction) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">Edit</a>
                                        <form method="POST" action="{{ route('cash-transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Delete cash entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">Source controlled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-16 text-center text-gray-400">No cash transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
