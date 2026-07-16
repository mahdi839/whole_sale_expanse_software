<x-app-layout>
    <x-slot name="header">Expenses</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    @if(auth()->user()->canManageAllShops())
                    <div><label class="block text-xs font-medium text-gray-500 mb-1">Shop</label><select name="shop_id" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full"><option value="">All shops</option>@foreach($shops as $shop)<option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? null) == $shop->id)>{{ $shop->name }}</option>@endforeach</select></div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <input type="text"
                               name="search"
                               value="{{ $filters['search'] ?? '' }}"
                               placeholder="Reference, category, note..."
                               class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                        <select name="category"
                                class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                            <option value="">All categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}"
                                    @selected(($filters['category'] ?? '') === $category)>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">From date</label>
                        <input type="date"
                               name="date_from"
                               value="{{ $filters['date_from'] ?? '' }}"
                               class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To date</label>
                        <input type="date"
                               name="date_to"
                               value="{{ $filters['date_to'] ?? '' }}"
                               class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                    <button type="submit"
                            class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">
                        Filter
                    </button>

                    <a href="{{ route('expenses.index') }}"
                       class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center">
                        Reset
                    </a>

                    <div class="sm:ml-auto flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('expenses.export', request()->query()) }}"
                           class="h-10 px-4 bg-green-50 text-green-700 border border-green-200 rounded-lg text-sm inline-flex items-center justify-center">
                            ⬇ CSV
                        </a>

                        <a href="{{ route('expenses.create') }}"
                           class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center">
                            + New Expense
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Expenses</p>
                <p class="text-xl font-semibold text-gray-800">
                    {{ number_format($totals->total_expenses ?? 0) }}
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <p class="text-xs text-gray-400 uppercase">Total Amount</p>
                <p class="text-xl font-semibold text-red-600">
                    ৳{{ number_format($totals->total_amount ?? 0, 2) }}
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Category</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Document</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 bg-red-50 text-red-700 rounded-md text-xs font-mono">
                                        {{ $expense->reference }}
                                    </span>
                                    @if($expense->note)
                                        <div class="text-xs text-gray-400 mt-1 max-w-xs truncate">
                                            {{ $expense->note }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-3">
                                    {{ $expense->category }}
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-red-600">
                                    ৳{{ number_format($expense->amount, 2) }}
                                </td>

                                <td class="px-5 py-3 text-gray-600">
                                    {{ optional($expense->date)->format('d M Y') }}
                                </td>

                                <td class="px-5 py-3">
                                    @if($expense->document)
                                        <a href="{{ asset('storage/'.$expense->document) }}"
                                           target="_blank"
                                           class="text-xs text-blue-600 hover:underline">
                                            View
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3 text-right">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('expenses.show', $expense) }}"
                                           class="px-2.5 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg">
                                            View
                                        </a>

                                        <a href="{{ route('expenses.edit', $expense) }}"
                                           class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('expenses.destroy', $expense) }}"
                                              onsubmit="return confirm('Delete this expense?')">
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
                                    No expenses found.
                                    <a href="{{ route('expenses.create') }}" class="text-blue-600 hover:underline">
                                        Create first expense
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
