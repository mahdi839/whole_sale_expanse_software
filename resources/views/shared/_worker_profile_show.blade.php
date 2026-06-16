<x-app-layout>
    <x-slot name="header">{{ $title }}</x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <a href="{{ route($routeBase.'.index') }}" class="px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-lg">Back</a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $worker->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $worker->phone ?? 'No phone' }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $worker->address ?? 'No address' }}</p>
                    @if(isset($worker->nid_passport_no))
                        <p class="text-sm text-gray-500 mt-1">NID / Passport: {{ $worker->nid_passport_no ?? '-' }}</p>
                    @endif
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full sm:w-auto">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400">Work Total</p>
                        <p class="text-base font-semibold text-gray-800">{{ number_format($totalWorkAmount, 2) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-600">Paid</p>
                        <p class="text-base font-semibold text-green-700">{{ number_format($worker->total_paid ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-xs text-red-600">Due</p>
                        <p class="text-base font-semibold text-red-700">{{ number_format($worker->total_due ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="text-xs text-indigo-600">Advance</p>
                        <p class="text-base font-semibold text-indigo-700">{{ number_format($worker->advance ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Cash Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Type</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Payment Method</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($cashTransactions as $transaction)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($transaction->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-violet-700">{{ $transaction->reference }}</td>
                                <td class="px-5 py-3">{{ ucwords(str_replace('_', ' ', $transaction->type)) }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $transaction->direction === 'in' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $transaction->direction === 'in' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-5 py-3">{{ $transaction->payment_method ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $transaction->note ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No cash transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Work Logs Summary</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        @if($workLogType === 'computer')
                            <tr class="bg-gray-50 border-b">
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Memo No</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Product</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Design Code</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Design Qty</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Received Qty</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Rate</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            </tr>
                        @elseif($workLogType === 'carry')
                            <tr class="bg-gray-50 border-b">
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Memo No</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Marka</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Bale Qty</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">KG</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Rate/KG</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            </tr>
                        @else
                            <tr class="bg-gray-50 border-b">
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Memo No</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Qty</th>
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Unit</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Per Goj Rate</th>
                                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workLogs as $log)
                            @if($workLogType === 'computer')
                                <tr>
                                    <td class="px-5 py-3 whitespace-nowrap">{{ optional($log->date)->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $log->memo_no ?? '-' }}</td>
                                    <td class="px-5 py-3">{{ $log->product?->product_name ?? '-' }}</td>
                                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $log->product?->sku ?: ($log->product?->product_code ?: '-') }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->computer_design_qty, 2) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->received_qty, 2) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->rate_per_piece, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-green-600">{{ number_format($log->total_rate, 2) }}</td>
                                </tr>
                            @elseif($workLogType === 'carry')
                                <tr>
                                    <td class="px-5 py-3 whitespace-nowrap">{{ optional($log->date)->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $log->memo_no ?? '-' }}</td>
                                    <td class="px-5 py-3">{{ $log->marka ?? '-' }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->bale_qty, 2) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->total_unit_kg, 2) }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->rate_per_kg, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-green-600">{{ number_format($log->total_rate, 2) }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="px-5 py-3 whitespace-nowrap">{{ optional($log->date)->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ $log->memo_no ?? '-' }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->qty, 2) }}</td>
                                    <td class="px-5 py-3">{{ $log->unit }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($log->rate_per_goj, 2) }}</td>
                                    <td class="px-5 py-3 text-right text-green-600">{{ number_format($log->total_rate, 2) }}</td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">No work logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
