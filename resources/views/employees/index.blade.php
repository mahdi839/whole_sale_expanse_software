<x-app-layout>
    <x-slot name="header">Employees</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
            <form method="GET" action="{{ route('employees.index') }}" class="flex gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search employee..." class="h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg w-full">
                <button class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">Search</button>
            </form>

            @canany(['manage employees', 'create employees'])
                <a href="{{ route('employees.create') }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center justify-center">
                    Add Employee
                </a>
            @endcanany
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Phone</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Address</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Documents</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Type</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Joining</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Monthly Salary</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Advance Salary</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $employee->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $employee->phone ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $employee->address ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $employee->documents ?? '-' }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $employee->employment_type ?? '-' }}</td>
                                <td class="px-5 py-3">{{ optional($employee->joining_date)->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-indigo-600">৳{{ number_format($employee->salary_amount, 2) }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-amber-600">৳{{ number_format($employee->advance_salary_total ?? 0, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-1.5">
                                        <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100" title="Month wise salaries">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/>
                                                <circle cx="12" cy="12" r="3"/>
                                            </svg>
                                        </a>
                                        @canany(['manage employees', 'edit employees'])
                                            <a href="{{ route('employees.edit', $employee) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-5 py-12 text-center text-gray-400">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $employees->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
