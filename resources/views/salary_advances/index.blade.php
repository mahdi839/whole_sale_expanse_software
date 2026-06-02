<x-app-layout>
    <x-slot name="header">Advance Salary</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="flex justify-end">
            @canany(['manage salary advances', 'create salary advances'])
                <a href="{{ route('salary-advances.create') }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center">
                    Add Advance Salary
                </a>
            @endcanany
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Advance Month</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Created</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($advances as $advance)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $advance->employee?->name ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $advance->advance_month?->format('F Y') }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-amber-600">৳{{ number_format($advance->amount, 2) }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $advance->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1.5">
                                        @canany(['manage salary advances', 'edit salary advances'])
                                            <a href="{{ route('salary-advances.edit', $advance) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage salary advances', 'delete salary advances'])
                                            <form method="POST" action="{{ route('salary-advances.destroy', $advance) }}" onsubmit="return confirm('Delete this advance salary?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No advance salaries found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($advances->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $advances->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
