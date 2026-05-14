<x-app-layout>
    <x-slot name="header">Sales Man Details</x-slot>

    <div class="max-w-5xl space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $salesMan->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $salesMan->phone ?? '-' }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $salesMan->address ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500">Total Expense</p>
                    <p class="text-2xl font-semibold text-red-600">৳{{ number_format($salesMan->total_expense, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-800">Transaction Logs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Type</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Method</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($log->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-violet-700">{{ $log->reference }}</td>
                                <td class="px-5 py-3">{{ ucwords(str_replace('_', ' ', $log->type)) }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $log->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $log->direction === 'in' ? '+' : '-' }}৳{{ number_format($log->amount, 2) }}
                                </td>
                                <td class="px-5 py-3">{{ $log->payment_method ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-500 max-w-xs truncate">{{ $log->note ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('sales-men.index') }}" class="inline-flex text-sm text-gray-500 hover:text-gray-700">Back to Sales Men</a>
    </div>
</x-app-layout>
