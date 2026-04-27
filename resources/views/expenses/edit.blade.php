<x-app-layout>
    <x-slot name="header">Edit Expense</x-slot>

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
                    <h2 class="text-sm font-semibold text-gray-800">Edit Expense</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Update expense information below.</p>
                </div>

                <span class="px-2.5 py-1.5 bg-red-50 border border-red-200 rounded-lg text-xs font-mono font-medium text-red-700">
                    {{ $expense->reference }}
                </span>
            </div>

            <form method="POST" action="{{ route('expenses.update', $expense) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="px-6 py-6">
                    @include('expenses._form', [
                        'expense' => $expense,
                        'categories' => $categories,
                    ])
                </div>

                <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a href="{{ route('expenses.index') }}"
                       class="h-9 px-4 inline-flex items-center text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>

                    <button type="submit"
                            class="h-9 px-5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Update Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>