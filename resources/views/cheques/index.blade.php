<x-app-layout>
    <x-slot name="header">Cheque Management</x-slot>

    <div class="space-y-4">
        @include('partials.flash')

        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('cheques.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-3.5">
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cheque no, customer, bank, note..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <select name="status" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">All statuses</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                        <option value="received" @selected(($filters['status'] ?? '') === 'received')>Received</option>
                    </select>
                    @if(auth()->user()->canManageAllShops())
                        <select name="shop_id" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                            <option value="">All shops</option>
                            @foreach($shops as $shop)<option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? '') == $shop->id)>{{ $shop->name }}</option>@endforeach
                        </select>
                    @endif
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
                    <a href="{{ route('cheques.index') }}" class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center">Reset</a>
                    @canany(['manage cheques', 'create cheques'])
                        <a href="{{ route('cheques.create') }}" class="sm:ml-auto h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center">+ New Cheque</a>
                    @endcanany
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Cheque No</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Customer</th>
                            @if(auth()->user()->canManageAllShops())<th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Shop</th>@endif
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Bank</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400 uppercase">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Issue</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Deposit</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($cheques as $cheque)
                            <tr>
                                <td class="px-5 py-3">
                                    <a href="{{ route('cheques.show', $cheque) }}" class="text-blue-600 font-medium">{{ $cheque->cheque_no }}</a>
                                    @if($cheque->documents)
                                        <a href="{{ Storage::url($cheque->documents) }}" target="_blank" class="block text-xs text-gray-400 mt-1">Document</a>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $cheque->customer?->full_name }}</td>
                                @if(auth()->user()->canManageAllShops())<td class="px-5 py-3">{{ $cheque->shop?->name ?? '-' }}</td>@endif
                                <td class="px-5 py-3">{{ $cheque->bank }}</td>
                                <td class="px-5 py-3 text-right font-semibold">৳{{ number_format($cheque->amount, 2) }}</td>
                                <td class="px-5 py-3">{{ $cheque->issue_date?->format('d M Y') }}</td>
                                <td class="px-5 py-3">{{ $cheque->deposit_date?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $cheque->status === 'received' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ ucfirst($cheque->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    @canany(['manage cheques', 'edit cheques'])
                                        <a href="{{ route('cheques.edit', $cheque) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">Edit</a>
                                    @endcanany
                                    @canany(['manage cheques', 'delete cheques'])
                                        <form method="POST" action="{{ route('cheques.destroy', $cheque) }}" class="inline" onsubmit="return confirm('Delete this cheque?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">Delete</button>
                                        </form>
                                    @endcanany
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ auth()->user()->canManageAllShops() ? 9 : 8 }}" class="px-5 py-16 text-center text-gray-400">No cheques found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cheques->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $cheques->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
