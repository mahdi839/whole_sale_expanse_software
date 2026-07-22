<x-app-layout>
    <x-slot name="header">Garey Man Work Logs</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('garey-man-work-logs.index') }}" class="flex items-center gap-2 w-full sm:max-w-md">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search garey man or memo..."
                    class="w-full h-10 px-3 text-sm bg-white border border-gray-200 rounded-lg">
                <button class="h-10 px-4 text-sm bg-white border border-gray-200 rounded-lg">Search</button>
            </form>
            <div class="flex gap-2">
            @canany(['manage garey man work logs', 'view garey man work logs'])<a href="{{ route('garey-man-work-logs.export.pdf', array_filter(['search' => $search])) }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg">PDF</a>@endcanany
            @canany(['manage garey man work logs', 'create garey man work logs'])
                <a href="{{ route('garey-man-work-logs.create') }}" class="inline-flex items-center justify-center h-10 px-4 text-sm font-medium text-white bg-blue-600 rounded-lg">Add Work Log</a>
            @endcanany
            </div>
        </div>

        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Memo No</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Garey Man</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Received Qty</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Balance</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Unit</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Per Goj Rate</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Total</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($workLogs as $workLog)
                            <tr>
                                <td class="px-5 py-3 whitespace-nowrap">{{ optional($workLog->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $workLog->memo_no ?? '-' }}</td>
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $workLog->gareyMan?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->qty, 2) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->received_qty, 2) }}</td>
                                <td class="px-5 py-3 text-right text-red-600">{{ number_format((float) $workLog->qty - (float) $workLog->received_qty, 2) }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $workLog->unit }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($workLog->rate_per_goj, 2) }}</td>
                                <td class="px-5 py-3 text-right text-green-600">{{ number_format($workLog->total_rate, 2) }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex justify-end gap-2">
                                        @canany(['manage garey man work logs', 'edit garey man work logs'])
                                            <button type="button"
                                                class="open-receive px-3 py-1.5 text-xs text-white bg-green-500 rounded-lg"
                                                data-action="{{ route('garey-man-work-logs.receive', $workLog) }}"
                                                data-title="{{ $workLog->gareyMan?->name ?? '-' }} - {{ $workLog->memo_no ?? 'No memo' }}"
                                                data-total="{{ (float) $workLog->qty }}"
                                                data-received="{{ (float) $workLog->received_qty }}"
                                                data-unit="{{ $workLog->unit }}">
                                                Receive
                                            </button>
                                            <a href="{{ route('garey-man-work-logs.edit', $workLog) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage garey man work logs', 'delete garey man work logs'])
                                            <form method="POST" action="{{ route('garey-man-work-logs.destroy', $workLog) }}" onsubmit="return confirm('Delete this work log?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-3 py-1.5 text-xs text-red-700 bg-red-50 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-5 py-12 text-center text-gray-400">No work logs found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($workLogs->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">{{ $workLogs->links() }}</div>
            @endif
        </div>
    </div>

    <div id="receive-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Receive Work</h3>
                    <p id="receive-title" class="text-xs text-gray-400 mt-0.5"></p>
                </div>
                <button type="button" class="close-receive text-gray-400 hover:text-gray-600">X</button>
            </div>
            <form id="receive-form" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                        <div class="text-xs text-gray-400">Work Qty</div>
                        <div id="receive-total" class="font-medium text-gray-800"></div>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                        <div class="text-xs text-gray-400">Balance</div>
                        <div id="receive-balance" class="font-medium text-red-600"></div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Received Qty</label>
                    <input id="receive-input" type="number" name="received_qty" min="0" step="0.01" class="w-full h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                </div>
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <button type="button" class="close-receive px-4 py-2 text-sm bg-white border border-gray-200 text-gray-700 rounded-lg">Cancel</button>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const receiveModal = document.getElementById('receive-modal');
        const receiveForm = document.getElementById('receive-form');
        const receiveInput = document.getElementById('receive-input');
        const receiveTotal = document.getElementById('receive-total');
        const receiveBalance = document.getElementById('receive-balance');
        let receiveMax = 0;
        let receiveUnit = '';

        function formatQty(value) {
            return (Number(value) || 0).toFixed(2);
        }

        function updateReceiveBalance() {
            const value = Math.max(0, Math.min(receiveMax, Number(receiveInput.value) || 0));
            receiveInput.value = formatQty(value);
            receiveBalance.textContent = `${formatQty(receiveMax - value)} ${receiveUnit}`;
        }

        document.querySelectorAll('.open-receive').forEach(button => {
            button.addEventListener('click', () => {
                receiveMax = Number(button.dataset.total) || 0;
                receiveUnit = button.dataset.unit || '';
                receiveForm.action = button.dataset.action;
                document.getElementById('receive-title').textContent = button.dataset.title;
                receiveInput.max = receiveMax;
                receiveInput.value = formatQty(button.dataset.received);
                receiveTotal.textContent = `${formatQty(receiveMax)} ${receiveUnit}`;
                updateReceiveBalance();
                receiveModal.classList.remove('hidden');
                receiveModal.classList.add('flex');
            });
        });

        receiveInput.addEventListener('input', updateReceiveBalance);
        document.querySelectorAll('.close-receive').forEach(button => button.addEventListener('click', () => {
            receiveModal.classList.add('hidden');
            receiveModal.classList.remove('flex');
        }));
    </script>
</x-app-layout>
