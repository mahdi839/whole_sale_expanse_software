<div class="overflow-x-auto">
    <table class="w-full table-fixed text-sm">
        <colgroup>
            <col class="w-[24%]">
            <col class="w-[34%]">
            <col class="w-[20%]">
            <col class="w-[22%]">
        </colgroup>
        <thead><tr class="bg-gray-50 border-b">
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Purchase</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Supplier</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Purchase Date</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
                <tr>
                    <td class="px-5 py-3"><a href="{{ route('purchases.show', $row) }}" class="inline-block max-w-full truncate px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">{{ $row->reference }}</a></td>
                    <td class="px-5 py-3 truncate">
                        {{ $row->supplier?->name ?? $row->seller_store_name ?? '-' }}
                        <div class="text-xs text-gray-400 truncate">{{ $row->supplier?->phone ?? '—' }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ $row->supplier?->address ?? '—' }}</div>
                    </td>
                    <td class="px-5 py-3 whitespace-nowrap">{{ optional($row->date)->format('d M Y') }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($row->due_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-12 text-center text-gray-400">No purchase dues.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($rows->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $rows->links() }}</div>@endif
