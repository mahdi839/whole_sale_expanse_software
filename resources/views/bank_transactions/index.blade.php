<x-app-layout>
    <x-slot name="header">Bank Management</x-slot>

    <style>
        .bank-page {
            --ink-3: #7a7e8a;
            --surface: #ffffff;
            --border: rgba(15,17,23,0.08);
            --border-strong: rgba(15,17,23,0.14);
            --indigo: #4f46e5;
            --emerald: #059669;
            --sky: #0284c7;
            --rose: #e11d48;
            --radius-lg: 16px;
            --shadow-xs: 0 1px 2px rgba(15,17,23,0.05);
            --shadow-sm: 0 1px 3px rgba(15,17,23,0.08), 0 1px 2px rgba(15,17,23,0.04);
        }
        .bank-metric-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }
        @media (max-width: 1024px) { .bank-metric-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px)  { .bank-metric-grid { grid-template-columns: 1fr 1fr; } }
        .bank-metric-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 22px 18px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xs);
        }
        .bank-metric-card:hover { box-shadow: var(--shadow-sm); border-color: var(--border-strong); }
        .bank-metric-card::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .bank-metric-card.mc-indigo::before { background: var(--indigo); }
        .bank-metric-card.mc-emerald::before { background: var(--emerald); }
        .bank-metric-card.mc-sky::before { background: var(--sky); }
        .bank-metric-card.mc-rose::before { background: var(--rose); }
        .bank-metric-label {
            font-size: 10.5px;
            font-weight: 500;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: var(--ink-3);
            margin: 0 0 10px;
        }
        .bank-metric-value {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: -0.03em;
            line-height: 1;
            margin: 0;
        }
        .bank-metric-card.mc-indigo .bank-metric-value { color: var(--indigo); }
        .bank-metric-card.mc-emerald .bank-metric-value { color: var(--emerald); }
        .bank-metric-card.mc-sky .bank-metric-value { color: var(--sky); }
        .bank-metric-card.mc-rose .bank-metric-value { color: var(--rose); }
    </style>

    <div class="bank-page space-y-4">
        @if(session('success'))
            <div class="px-4 py-3 text-sm text-green-700 bg-green-50 border border-green-200 rounded-xl">{{ session('success') }}</div>
        @endif

        <div class="bank-metric-grid">
            <div class="bank-metric-card mc-indigo">
                <p class="bank-metric-label">All Bank Balance</p>
                <p class="bank-metric-value">৳{{ number_format($overallBalance ?? 0, 2) }}</p>
            </div>
            <div class="bank-metric-card mc-emerald">
                <p class="bank-metric-label">Date Bank Balance</p>
                <p class="bank-metric-value">৳{{ number_format($dateBalance ?? 0, 2) }}</p>
            </div>
            <div class="bank-metric-card mc-sky">
                <p class="bank-metric-label">Bank In</p>
                <p class="bank-metric-value">৳{{ number_format($totals->bank_in ?? 0, 2) }}</p>
            </div>
            <div class="bank-metric-card mc-rose">
                <p class="bank-metric-label">Bank Out</p>
                <p class="bank-metric-value">৳{{ number_format($totals->bank_out ?? 0, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
            <form method="GET" action="{{ route('bank-transactions.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-3.5">
                    @if(auth()->user()->canManageAllShops())
                        <select name="shop_id" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                            <option value="">All shops</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" @selected(($filters['shop_id'] ?? null) == $shop->id)>{{ $shop->displayLabel() }}</option>
                            @endforeach
                        </select>
                    @endif
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Reference, bank, party, note..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <input type="text" name="bank_name" value="{{ $filters['bank_name'] ?? '' }}" placeholder="Bank name..."
                        class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <select name="entry_type" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">All entry types</option>
                        <option value="customer" @selected(($filters['entry_type'] ?? '') === 'customer')>Customer</option>
                        <option value="supplier" @selected(($filters['entry_type'] ?? '') === 'supplier')>Supplier</option>
                        <option value="tailor" @selected(($filters['entry_type'] ?? '') === 'tailor')>Tailor</option>
                        <option value="computer" @selected(($filters['entry_type'] ?? '') === 'computer')>Computer Man</option>
                        <option value="carry_man" @selected(($filters['entry_type'] ?? '') === 'carry_man')>Carry Man</option>
                        <option value="garey_man" @selected(($filters['entry_type'] ?? '') === 'garey_man')>Garey Man</option>
                    </select>
                    <select name="direction" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                        <option value="">All directions</option>
                        <option value="in" @selected(($filters['direction'] ?? '') === 'in')>Bank in</option>
                        <option value="out" @selected(($filters['direction'] ?? '') === 'out')>Bank out</option>
                    </select>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg">
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <button class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm">Filter</button>
                    <a href="{{ route('bank-transactions.index') }}" class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center">Reset</a>
                    @canany(['manage bank', 'create bank'])
                        <a href="{{ route('bank-transactions.create') }}" class="sm:ml-auto h-10 px-4 bg-indigo-600 text-white rounded-lg text-sm inline-flex items-center justify-center">+ New Bank Entry</a>
                    @endcanany
                </div>
            </form>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Reference</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Shop</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Bank</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Party</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Amount</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-400">Note</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 rounded-md text-xs font-mono">{{ $transaction->reference }}</span>
                                    @if($transaction->source_type)
                                        <div class="text-xs text-gray-400 mt-1">Auto: {{ str_replace('_', ' ', $transaction->source_type) }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $transaction->shop?->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-800">{{ $transaction->bank_name ?: '—' }}</div>
                                    @if($transaction->bank_details)
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $transaction->bank_details }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    {{ $transaction->partyName() }}
                                    @if($transaction->entry_type === 'customer')
                                        <span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded ml-1">Customer</span>
                                    @elseif($transaction->entry_type === 'supplier')
                                        <span class="text-xs bg-sky-100 text-sky-800 px-2 py-0.5 rounded ml-1">Supplier</span>
                                    @elseif($transaction->entry_type === 'tailor')
                                        <span class="text-xs bg-violet-100 text-violet-800 px-2 py-0.5 rounded ml-1">Tailor</span>
                                    @elseif($transaction->entry_type === 'computer')
                                        <span class="text-xs bg-fuchsia-100 text-fuchsia-800 px-2 py-0.5 rounded ml-1">Computer Man</span>
                                    @elseif($transaction->entry_type === 'carry_man')
                                        <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded ml-1">Carry Man</span>
                                    @elseif($transaction->entry_type === 'garey_man')
                                        <span class="text-xs bg-orange-100 text-orange-800 px-2 py-0.5 rounded ml-1">Garey Man</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-semibold {{ $transaction->direction === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $transaction->direction === 'in' ? '+' : '-' }}৳{{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-5 py-3">{{ optional($transaction->date)->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-gray-500 max-w-xs">
                                    {{ $transaction->note ?: '—' }}
                                    @if($transaction->document)
                                        <div><a href="{{ asset('storage/'.$transaction->document) }}" target="_blank" class="text-xs text-indigo-600">Document</a></div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @if(! $transaction->source_type)
                                        @canany(['manage bank', 'edit bank'])
                                            <a href="{{ route('bank-transactions.edit', $transaction) }}" class="px-2.5 py-1 text-xs bg-blue-50 text-blue-700 rounded-lg">Edit</a>
                                        @endcanany
                                        @canany(['manage bank', 'delete bank'])
                                            <form method="POST" action="{{ route('bank-transactions.destroy', $transaction) }}" class="inline" onsubmit="return confirm('Delete bank entry?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-2.5 py-1 text-xs bg-red-50 text-red-700 rounded-lg">Delete</button>
                                            </form>
                                        @endcanany
                                    @else
                                        <span class="text-xs text-gray-400">Source controlled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-16 text-center text-gray-400">No bank transactions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-5 py-3 border-t bg-gray-50/50">{{ $transactions->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
