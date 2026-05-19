<x-app-layout>
    <x-slot name="header">Supplier Profile</x-slot>

    <div class="max-w-5xl space-y-4">
        <nav class="flex items-center gap-2 text-xs text-gray-400">
            <a href="{{ route('suppliers.index') }}" class="hover:text-gray-600 transition">Suppliers</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-gray-600">{{ $supplier->name }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-14 h-14 rounded-xl bg-violet-100 text-violet-700 text-lg font-semibold shrink-0">
                        {{ strtoupper(substr($supplier->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $supplier->name }}</h2>
                        <span class="inline-flex items-center gap-1 text-xs font-mono text-violet-700 bg-violet-50 px-2 py-0.5 rounded-md border border-violet-100">
                            {{ $supplier->code }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('suppliers.transactions.export', $supplier) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                        CSV
                    </a>
                    <a href="{{ route('suppliers.transactions.export', [$supplier, 'format' => 'pdf']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/><path d="M9 15h8M9 18h5"/></svg>
                        PDF
                    </a>
                    <a href="{{ route('suppliers.edit', $supplier) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                </div>
            </div>

            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="font-medium text-gray-800">{{ $supplier->phone ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-gray-500">Address</dt>
                    <dd class="font-medium text-gray-800 text-right">{{ $supplier->address ?? '-' }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500">Added on</dt>
                    <dd class="font-medium text-gray-800">{{ $supplier->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
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
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($log['date'])->format('d M Y') }}</td>
                                <td class="px-5 py-3">{{ $log['type'] }}</td>
                                <td class="px-5 py-3">
                                    <a href="{{ $log['url'] }}" class="font-mono text-xs text-blue-700 hover:underline">{{ $log['reference'] }}</a>
                                </td>
                                <td class="px-5 py-3 text-right {{ $log['amount'] < 0 ? 'text-red-600' : 'text-gray-700' }}">৳{{ number_format($log['amount'], 2) }}</td>
                                <td class="px-5 py-3 text-right text-gray-700">{{ is_null($log['qty']) ? '-' : number_format($log['qty'], 2) }}</td>
                                <td class="px-5 py-3 text-right text-green-600">৳{{ number_format($log['paid'], 2) }}</td>
                                <td class="px-5 py-3 text-right text-red-600">৳{{ number_format($log['due'], 2) }}</td>
                                <td class="px-5 py-3 text-gray-500 max-w-xs truncate">{{ $log['note'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Qty</p>
                <p class="text-xl font-semibold text-indigo-600">{{ number_format($totalQty, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Purchase</p>
                <p class="text-xl font-semibold text-gray-800">৳{{ number_format($supplier->total_purchase, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Paid</p>
                <p class="text-xl font-semibold text-green-600">৳{{ number_format($supplier->total_paid, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Due</p>
                <p class="text-xl font-semibold {{ $supplier->due > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    ৳{{ number_format($supplier->due, 2) }}
                </p>
                @if($supplier->due <= 0)
                    <span class="text-xs text-green-600 font-medium">Cleared</span>
                @endif
            </div>
        </div>

        <div>
            <a href="{{ route('suppliers.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                Back to Suppliers
            </a>
        </div>
    </div>
</x-app-layout>

