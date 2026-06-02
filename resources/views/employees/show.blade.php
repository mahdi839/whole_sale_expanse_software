<x-app-layout>
    <x-slot name="header">Employee Salary History</x-slot>

    <div class="space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $employee->name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $employee->employment_type ?? '-' }} · {{ $employee->phone ?? '-' }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $employee->documents ?? '-' }}</p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs text-gray-400 uppercase">Monthly Salary</p>
                    <p class="text-xl font-semibold text-indigo-600">৳{{ number_format($employee->salary_amount, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b font-semibold text-sm text-gray-700">Month Wise Salaries</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Month</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Salary Amount</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Advance Amount</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total Paid</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Submitted At</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($employee->salaries as $salary)
                            @php
                                $advanceAmount = $advanceAmounts[$salary->salary_month?->format('Y-m')] ?? 0;
                            @endphp
                            <tr>
                                <td class="px-5 py-3">{{ $salary->salary_month?->format('F Y') }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($salary->amount, 2) }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-amber-600">৳{{ number_format($advanceAmount, 2) }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-indigo-600">৳{{ number_format((float) $salary->amount + (float) $advanceAmount, 2) }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $salary->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $salary->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">No salary submitted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('employees.index') }}" class="inline-flex text-sm text-gray-500 hover:text-gray-700">Back to Employees</a>
    </div>
</x-app-layout>
