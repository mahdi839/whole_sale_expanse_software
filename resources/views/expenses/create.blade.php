<x-app-layout>
    <x-slot name="header">Add Expense</x-slot>

    <div class="space-y-4">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('expenses.index') }}" class="hover:text-gray-600 transition">Expenses</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">Add Expense</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">New Expense</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Add expense amount, category, date, note and document.</p>
                </div>

                <span class="px-2.5 py-1.5 bg-red-50 border border-red-200 rounded-lg text-xs font-mono font-medium text-red-700">
                    {{ $nextReference }}
                </span>
            </div>

            <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="px-6 py-6">
                    @include('expenses._form', [
                        'expense' => null,
                        'categories' => $categories,
                        'nextReference' => $nextReference,
                    ])
                </div>

                <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a href="{{ route('expenses.index') }}"
                       class="h-9 px-4 inline-flex items-center text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>

                    <button type="submit"
                            class="h-9 px-5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>