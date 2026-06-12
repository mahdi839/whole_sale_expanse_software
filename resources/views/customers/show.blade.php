<x-app-layout>
    <x-slot name="header">Customer Profile</x-slot>

    <div class="max-w-5xl mx-auto space-y-4 text-center">

        <nav class="flex items-center justify-center gap-2 text-xs text-gray-400">
            <a href="{{ route('customers.index') }}" class="hover:text-gray-600 transition">Customers</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-gray-600">{{ $customer->full_name }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex flex-col items-center justify-center gap-4 mb-6">
                <div class="flex flex-col items-center justify-center gap-3">
                    @if($customer->image)
                        <img src="{{ asset('storage/'.$customer->image) }}" alt="{{ $customer->full_name }}" class="w-14 h-14 rounded-full object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="flex items-center justify-center w-14 h-14 rounded-full bg-blue-100 text-blue-700 text-lg font-semibold shrink-0">
                            {{ strtoupper(substr($customer->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $customer->full_name }}</h2>
                        <span class="inline-flex items-center gap-1 text-xs font-mono text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                            {{ $customer->code }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-center gap-2 shrink-0">
                    <a href="{{ route('customers.transactions.export', $customer) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                        CSV
                    </a>
                    <a href="{{ route('customers.transactions.export', [$customer, 'format' => 'pdf']) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5"/><path d="M9 15h8M9 18h5"/></svg>
                        PDF
                    </a>
                    <a href="{{ route('customers.edit', $customer) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                </div>
            </div>

            <dl class="divide-y divide-gray-100 text-sm">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-1 sm:gap-10 py-3">
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="font-medium text-gray-800">{{ $customer->phone ?? '-' }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-1 sm:gap-10 py-3">
                    <dt class="text-gray-500">Alternative Phone</dt>
                    <dd class="font-medium text-gray-800">{{ $customer->alternative_phone ?? '-' }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-1 sm:gap-10 py-3">
                    <dt class="text-gray-500">Address</dt>
                    <dd class="font-medium text-gray-800">{{ $customer->address ?? '-' }}</dd>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-center gap-1 sm:gap-10 py-3">
                    <dt class="text-gray-500">Member Since</dt>
                    <dd class="font-medium text-gray-800">{{ $customer->created_at->format('d M Y') }}</dd>
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
                            <th class="px-5 py-3 text-center text-xs font-medium text-gray-400">Date & Time</th>
                            <th class="px-5 py-3 text-center text-xs font-medium text-gray-400">Type</th>
                            <th class="px-5 py-3 text-center text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                            <th class="px-5 py-3 text-center text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($logs as $log)
                            <tr>
                                <td class="px-5 py-3 align-middle whitespace-nowrap">{{ optional($log['display_at'] ?? $log['date'])->format('d M Y h:i A') }}</td>
                                <td class="px-5 py-3 align-middle">{{ $log['type'] }}</td>
                                <td class="px-5 py-3 align-middle">
                                    <a href="{{ $log['url'] }}" class="font-mono text-xs text-blue-700 hover:underline">{{ $log['reference'] }}</a>
                                </td>
                                <td class="px-5 py-3 align-middle text-right {{ $log['amount'] < 0 ? 'text-red-600' : 'text-gray-700' }}">BDT {{ $log['amount'] }}</td>
                                <td class="px-5 py-3 align-middle text-right text-gray-700">{{ is_null($log['qty']) ? '-' : $log['qty'] }}</td>
                                <td class="px-5 py-3 align-middle text-right text-green-600">BDT {{ $log['paid'] }}</td>
                                <td class="px-5 py-3 align-middle text-right text-red-600">{{ $log['due']>0?"BDT":"" }}{{ $log['due'] }}</td>
                                <td class="px-5 py-3 align-middle text-gray-500 max-w-sm whitespace-normal">{{ $log['note'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-5 py-12 text-center text-gray-400">No transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Qty</p>
                <p class="text-xl font-semibold text-gray-800">{{ $totalQty }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Sale</p>
                <p class="text-xl font-semibold text-gray-800">BDT {{ $customer->total_sale }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Paid</p>
                <p class="text-xl font-semibold text-green-600">BDT {{ $customer->total_paid }}</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Due</p>
                <p class="text-xl font-semibold {{ $customer->due > 0 ? 'text-red-600' : 'text-gray-400' }}">
                    BDT {{ $customer->due }}
                </p>
                @if($customer->due <= 0)
                    <span class="text-xs text-green-600 font-medium">Cleared</span>
                @endif
            </div>
        </div>

        <div class="flex justify-center">
            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                Back to Customers
            </a>
        </div>

    </div>
</x-app-layout>
