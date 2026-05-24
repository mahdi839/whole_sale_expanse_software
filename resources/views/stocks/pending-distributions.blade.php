<x-app-layout>
    <x-slot name="header">Pending Stock Receive</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="flex justify-end">
            @can('distribute stock')
                <a href="{{ route('stocks.distribute') }}" class="h-10 px-4 bg-green-600 text-white rounded-lg text-sm inline-flex items-center">New Distribution</a>
            @endcan
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b font-semibold text-sm text-gray-700">Pending Stock Distributions</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase">Date</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase">Shop</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase">Distributor</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase">Receiver</th>
                            <th class="text-left px-5 py-3 text-xs font-medium text-gray-400 uppercase">Products</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase">Qty</th>
                            <th class="text-right px-5 py-3 text-xs font-medium text-gray-400 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($distributions as $distribution)
                            <tr>
                                <td class="px-5 py-3.5">{{ $distribution->distribution_date?->format('d M Y') }}</td>
                                <td class="px-5 py-3.5">{{ $distribution->shop?->name }}</td>
                                <td class="px-5 py-3.5">{{ $distribution->distributor }}</td>
                                <td class="px-5 py-3.5">{{ $distribution->receiver }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="space-y-1">
                                        @foreach($distribution->items as $item)
                                            <div>{{ $item->product?->product_name }} <span class="text-gray-400">({{ $item->product?->sku ?? '-' }})</span> x {{ number_format($item->qty, 2) }}</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-right font-medium">{{ number_format($distribution->items->sum('qty'), 2) }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    @can('receive stock distributions')
                                        <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('stocks.distributions.receive', $distribution) }}" onsubmit="return submitDistributionAction(this, 'Receive this stock into the shop?')">
                                            @csrf
                                            <input type="hidden" name="action_note">
                                            <button class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium">Receive</button>
                                        </form>
                                        <form method="POST" action="{{ route('stocks.distributions.cancel', $distribution) }}" onsubmit="return submitDistributionAction(this, 'Cancel this stock distribution?')">
                                            @csrf
                                            <input type="hidden" name="action_note">
                                            <button class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-medium">Cancel</button>
                                        </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">Pending</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-14 text-center text-gray-400">No pending stock distributions.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function submitDistributionAction(form, message) {
                if (!confirm(message)) return false;

                const note = prompt('Note (optional)');
                if (note === null) return false;

                form.querySelector('input[name="action_note"]').value = note;
                return true;
            }
        </script>
    @endpush
</x-app-layout>
