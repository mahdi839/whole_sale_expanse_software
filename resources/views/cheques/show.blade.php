<x-app-layout>
    <x-slot name="header">Cheque Details</x-slot>

    <div class="max-w-3xl space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><span class="text-gray-400">Cheque No</span><div class="font-medium">{{ $cheque->cheque_no }}</div></div>
                <div><span class="text-gray-400">Customer</span><div class="font-medium">{{ $cheque->customer?->full_name }}</div></div>
                <div><span class="text-gray-400">Shop</span><div class="font-medium">{{ $cheque->shop?->name ?? '-' }}</div></div>
                <div><span class="text-gray-400">Bank</span><div class="font-medium">{{ $cheque->bank }}</div></div>
                <div><span class="text-gray-400">Amount</span><div class="font-medium">৳{{ number_format($cheque->amount, 2) }}</div></div>
                <div><span class="text-gray-400">Issue Date</span><div class="font-medium">{{ $cheque->issue_date?->format('d M Y') }}</div></div>
                <div><span class="text-gray-400">Deposit Date</span><div class="font-medium">{{ $cheque->deposit_date?->format('d M Y') ?? '—' }}</div></div>
                <div><span class="text-gray-400">Status</span><div class="font-medium">{{ ucfirst($cheque->status) }}</div></div>
                <div>
                    <span class="text-gray-400">Documents</span>
                    <div class="font-medium">
                        @if($cheque->documents)
                            <a href="{{ Storage::url($cheque->documents) }}" target="_blank" class="text-blue-600">Open document</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="text-gray-400">Note</span>
                <div class="mt-1 text-gray-700 whitespace-pre-line">{{ $cheque->note ?: '—' }}</div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('cheques.index') }}" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm inline-flex items-center">Back</a>
            @canany(['manage cheques', 'edit cheques'])
                <a href="{{ route('cheques.edit', $cheque) }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">Edit</a>
            @endcanany
        </div>
    </div>
</x-app-layout>
