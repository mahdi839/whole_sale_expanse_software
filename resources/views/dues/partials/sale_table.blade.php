<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b">
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Sale</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Customer</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Due</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($rows as $row)
                <tr>
                    <td class="px-5 py-3"><a href="{{ route('sales.show', $row) }}" class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">{{ $row->reference }}</a></td>
                    <td class="px-5 py-3">{{ $row->customer?->full_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($row->due, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="px-5 py-12 text-center text-gray-400">No sale dues.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($rows->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $rows->links() }}</div>@endif
