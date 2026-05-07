<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b">
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Supplier</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Purchase</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Paid</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
                <tr>
                    <td class="px-5 py-3">{{ $row->name }}<div class="text-xs text-gray-400">{{ $row->phone }}</div></td>
                    <td class="px-5 py-3 text-right">৳{{ number_format($row->total_purchase, 2) }}</td>
                    <td class="px-5 py-3 text-right">৳{{ number_format($row->total_paid, 2) }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($row->due, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-12 text-center text-gray-400">No supplier dues.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($rows->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $rows->links() }}</div>@endif
