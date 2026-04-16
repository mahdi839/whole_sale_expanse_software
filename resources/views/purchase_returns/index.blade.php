<x-app-layout>
    <x-slot name="header">Purchase Returns</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="flex items-center gap-2.5 px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="GET" action="{{ route('purchase-returns.index') }}">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, supplier, product..." class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">
                    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">
                    <select name="return_status" class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">
                        <option value="">Return status</option>
                        <option value="pending" @selected(($filters['return_status'] ?? '') === 'pending')>Pending</option>
                        <option value="approved" @selected(($filters['return_status'] ?? '') === 'approved')>Approved</option>
                        <option value="rejected" @selected(($filters['return_status'] ?? '') === 'rejected')>Rejected</option>
                    </select>
                    <select name="return_type" class="h-9 px-3 text-sm bg-gray-50 border rounded-lg">
                        <option value="">Return type</option>
                        <option value="refund" @selected(($filters['return_type'] ?? '') === 'refund')>Refund</option>
                        <option value="exchange" @selected(($filters['return_type'] ?? '') === 'exchange')>Exchange</option>
                        <option value="credit" @selected(($filters['return_type'] ?? '') === 'credit')>Credit</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="h-9 px-4 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
                    <a href="{{ route('purchase-returns.index') }}" class="h-9 px-4 bg-cyan-600 text-white rounded-lg text-sm">Reset</a>
                    <span class="flex-1"></span>
                    <a href="{{ route('purchase-returns.export', request()->query()) }}" class="h-9 px-4 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm flex items-center gap-1">⬇ CSV</a>
                    <a href="{{ route('purchase-returns.create') }}" class="h-9 px-4 bg-blue-600 text-white rounded-lg text-sm flex items-center gap-1">+ New Return</a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Returns</p>
                <p class="text-xl font-semibold">{{ number_format($totals->total_returns ?? 0) }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Subtotal</p>
                <p class="text-xl font-semibold">৳{{ number_format($totals->total_subtotal ?? 0, 2) }}</p>
            </div>
            <div class="bg-white border rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Return Amount</p>
                <p class="text-xl font-semibold text-red-600">৳{{ number_format($totals->total_return_amount ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Supplier</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Items</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($returns as $return)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3">
                                    <a href="{{ route('purchase-returns.show', $return) }}" class="px-2 py-0.5 bg-violet-50 text-violet-700 rounded-md text-xs font-mono">
                                        {{ $return->reference }}
                                    </a>
                                    <div class="text-xs text-gray-400 mt-1">{{ optional($return->date)->format('d M Y') }}</div>
                                </td>

                                <td class="px-5 py-3">
                                    {{ $return->supplier?->name ?? '—' }}
                                </td>

                                <td class="px-5 py-3 text-xs text-gray-600">
                                    @foreach($return->items->take(2) as $item)
                                        {{ $item->product->product_name ?? 'Unknown' }} (x{{ number_format($item->qty, 2) }})<br>
                                    @endforeach
                                    @if($return->items->count() > 2)
                                        +{{ $return->items->count() - 2 }} more
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-red-600">
                                    ৳{{ number_format($return->return_amount, 2) }}
                                </td>

                                <td class="px-5 py-3">
                                    <div class="flex flex-col gap-1.5">
                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-xs font-medium w-fit',
                                            'bg-green-50 text-green-700' => $return->return_status === 'approved',
                                            'bg-amber-50 text-amber-700' => $return->return_status === 'pending',
                                            'bg-red-50 text-red-700' => $return->return_status === 'rejected',
                                        ])>{{ ucfirst($return->return_status) }}</span>

                                        <span @class([
                                            'px-2 py-0.5 rounded-full text-xs font-medium w-fit',
                                            'bg-blue-50 text-blue-700' => $return->return_type === 'refund',
                                            'bg-purple-50 text-purple-700' => $return->return_type === 'exchange',
                                            'bg-gray-100 text-gray-600' => $return->return_type === 'credit',
                                        ])>{{ ucfirst($return->return_type) }}</span>
                                    </div>
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        @if($return->return_status === 'pending')
                                            <form method="POST" action="{{ route('purchase-returns.approve', $return) }}" onsubmit="return confirm('Approve this purchase return?')">
                                                @csrf
                                                <button class="px-2.5 py-1 text-xs bg-green-50 text-green-700 rounded-lg">
                                                    Approve
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('purchase-returns.edit', $return) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('purchase-returns.destroy', $return) }}" onsubmit="return confirm('Delete purchase return?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-20 text-center text-gray-400">
                                    No purchase returns found.
                                    <a href="{{ route('purchase-returns.create') }}" class="text-blue-600">Create first purchase return</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($returns->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">
                    {{ $returns->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>