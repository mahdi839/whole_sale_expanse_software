<x-app-layout>
    <x-slot name="header">Salaries</x-slot>

    <div class="space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="POST" action="{{ route('salaries.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                    <select name="employee_id" class="tom-select w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">Select employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" data-amount="{{ $employee->salary_amount }}" @selected(old('employee_id') == $employee->id)>
                                {{ $employee->name }}{{ $employee->phone ? ' - '.$employee->phone : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select name="salary_month" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        @foreach($months as $month)
                            <option value="{{ $month->format('Y-m') }}" @selected(old('salary_month', now()->format('Y-m')) === $month->format('Y-m'))>
                                {{ $month->format('F Y') }}
                            </option>
                        @endforeach
                    </select>
                    @error('salary_month')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salary Amount</label>
                    <input type="number" name="amount" id="salary-amount" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-xs text-amber-600 mt-1" id="salary-advance-text">Advance: ৳0.00</p>
                    @error('amount')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <button class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm">Submit</button>

                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                    <textarea name="note" rows="2" class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-lg">{{ old('note') }}</textarea>
                    @error('note')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b font-semibold text-sm text-gray-700">Submitted Salaries</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Month</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Submitted</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($salaries as $salary)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $salary->employee?->name ?? '-' }}</td>
                                <td class="px-5 py-3">{{ $salary->salary_month?->format('F Y') }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-red-600">৳{{ number_format($salary->amount, 2) }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $salary->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $salary->note ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400">No salaries submitted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($salaries->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $salaries->links() }}</div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const employeeSelect = document.querySelector('select[name="employee_id"]');
                const monthSelect = document.querySelector('select[name="salary_month"]');
                const amountInput = document.getElementById('salary-amount');
                const advanceText = document.getElementById('salary-advance-text');
                const advanceAmounts = @json($advanceAmounts);

                const formatMoney = (value) => Number(value || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });

                const updateSalaryAmount = () => {
                    const option = employeeSelect?.options[employeeSelect.selectedIndex];
                    const employeeId = employeeSelect?.value;
                    const month = monthSelect?.value;
                    const monthlySalary = Number(option?.dataset.amount || 0);
                    const advance = Number(advanceAmounts?.[employeeId]?.[month] || 0);
                    const remaining = Math.max(0, monthlySalary - advance);

                    if (advanceText) {
                        advanceText.textContent = `Advance: ৳${formatMoney(advance)}`;
                    }

                    if (!amountInput.dataset.userEdited) {
                        amountInput.value = remaining ? remaining.toFixed(2) : '';
                    }
                };

                if (amountInput?.value) {
                    amountInput.dataset.userEdited = '1';
                }

                amountInput?.addEventListener('input', () => amountInput.dataset.userEdited = '1');
                employeeSelect?.addEventListener('change', () => {
                    amountInput.dataset.userEdited = '';
                    updateSalaryAmount();
                });
                monthSelect?.addEventListener('change', () => {
                    amountInput.dataset.userEdited = '';
                    updateSalaryAmount();
                });
                updateSalaryAmount();
            });
        </script>
    @endpush
</x-app-layout>
