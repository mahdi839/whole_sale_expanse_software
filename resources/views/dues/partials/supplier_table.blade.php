<div class="overflow-x-auto">
    <table class="w-full table-fixed text-sm">
        <colgroup>
            <col class="w-[28%]">
            <col class="w-[16%]">
            <col class="w-[12%]">
            <col class="w-[14%]">
            <col class="w-[14%]">
            <col class="w-[12%]">
            <col class="w-[4%]">
        </colgroup>
        <thead>
            <tr class="bg-gray-50 border-b">
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Supplier</th>
                <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Purchase Date</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Qty</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Purchase</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
                <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Log</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
                <tr>
                    <td class="px-5 py-3 truncate">
                        {{ $row->name }}
                        <div class="text-xs text-gray-400 truncate">{{ $row->phone }}</div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">{{ $row->latest_due_purchase_date ? \Carbon\Carbon::parse($row->latest_due_purchase_date)->format('d M Y') : '-' }}</td>
                    <td class="px-5 py-3 text-right">{{ number_format($row->total_due_purchase_qty ?? 0, 2) }}</td>
                    <td class="px-5 py-3 text-right">BDT {{ number_format($row->total_purchase, 2) }}</td>
                    <td class="px-5 py-3 text-right">BDT {{ number_format($row->total_paid, 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-red-600">BDT {{ number_format($row->due, 2) }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('suppliers.show', $row) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100" title="View transaction logs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-gray-400">No supplier dues.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($rows->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $rows->links() }}</div>@endif
