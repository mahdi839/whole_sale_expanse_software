<x-app-layout>
    <x-slot name="header">Expense Details</x-slot>

    <div class="space-y-4">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('expenses.index') }}" class="hover:text-gray-600 transition">Expenses</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">{{ $expense->reference }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Expense Details</h2>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $expense->reference }}</p>
                </div>

                <a href="{{ route('expenses.edit', $expense) }}"
                   class="h-9 px-4 inline-flex items-center text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Edit
                </a>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Category</p>
                    <p class="font-medium text-gray-800">{{ $expense->category }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Amount</p>
                    <p class="font-semibold text-red-600">৳{{ number_format($expense->amount, 2) }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Date</p>
                    <p class="font-medium text-gray-800">{{ optional($expense->date)->format('d M Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-400 uppercase mb-1">Document</p>
                    @if($expense->document)
                        <a href="{{ asset('storage/'.$expense->document) }}"
                           target="_blank"
                           class="text-blue-600 hover:underline">
                            View document
                        </a>
                    @else
                        <p class="text-gray-400">—</p>
                    @endif
                </div>

                <div class="md:col-span-2">
                    <p class="text-xs text-gray-400 uppercase mb-1">Note</p>
                    <p class="text-gray-700 whitespace-pre-line">{{ $expense->note ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>