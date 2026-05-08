<div class="overflow-x-auto">
    <table class="w-full table-fixed text-sm">
        <colgroup>
            <col class="w-[34%]">
            <col class="w-[18%]">
            <col class="w-[16%]">
            <col class="w-[16%]">
            <col class="w-[16%]">
        </colgroup>
        <thead><tr class="bg-gray-50 border-b">
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Customer</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Sale Date</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Sale</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
                <tr>
                    <td class="px-5 py-3 truncate">{{ $row->full_name }}<div class="text-xs text-gray-400 truncate">{{ $row->phone }}</div></td>
                    <td class="px-5 py-3 whitespace-nowrap">{{ $row->latest_due_sale_at ? \Carbon\Carbon::parse($row->latest_due_sale_at)->format('d M Y') : '-' }}</td>
                    <td class="px-5 py-3 text-right">৳{{ number_format($row->total_sale, 2) }}</td>
                    <td class="px-5 py-3 text-right">৳{{ number_format($row->total_paid, 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($row->due, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No customer dues.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($rows->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $rows->links() }}</div>@endif
