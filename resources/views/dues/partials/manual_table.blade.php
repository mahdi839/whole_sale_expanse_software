<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 border-b">
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Party</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Adjustment</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($manualDues as $due)
                <tr>
                    <td class="px-5 py-3"><span class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">{{ $due->reference }}</span></td>
                    <td class="px-5 py-3">{{ $due->party_type === 'customer' ? $due->customer?->full_name : $due->supplier?->name }}</td>
                    <td class="px-5 py-3">
                        @if(($due->adjustment_type ?? 'add') === 'subtract')
                            <span class="px-2 py-1 text-xs text-green-700 bg-green-50 rounded-lg">Minus Due</span>
                        @else
                            <span class="px-2 py-1 text-xs text-red-700 bg-red-50 rounded-lg">Add Due</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right {{ ($due->adjustment_type ?? 'add') === 'subtract' ? 'text-green-600' : 'text-red-600' }} font-semibold">
                        {{ ($due->adjustment_type ?? 'add') === 'subtract' ? '-' : '+' }}{{ number_format($due->amount, 2) }}
                    </td>
                    <td class="px-5 py-3">{{ optional($due->date)->format('d M Y') }}</td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('dues.edit', $due) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">Edit</a>
                        <form method="POST" action="{{ route('dues.destroy', $due) }}" class="inline" onsubmit="return confirm('Delete manual due?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No manual dues found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($manualDues->hasPages())<div class="px-5 py-3 border-t bg-gray-50/50">{{ $manualDues->links() }}</div>@endif
