<x-app-layout>
    <x-slot name="header">Supplier Profile</x-slot>

    <div class="flex justify-center">
        <div class="w-full max-w-2xl space-y-4">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-gray-400">
                <a href="{{ route('suppliers.index') }}" class="hover:text-gray-600 transition">Suppliers</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
                <span class="text-gray-600">{{ $supplier->name }}</span>
            </nav>

            {{-- Profile card --}}
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

                {{-- Header strip --}}
                <div class="px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            {{-- Initials avatar --}}
                            <div class="flex items-center justify-center w-14 h-14 rounded-xl
                                        bg-violet-100 text-violet-700 text-lg font-semibold shrink-0">
                                {{ strtoupper(substr($supplier->name, 0, 2)) }}
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-gray-800">{{ $supplier->name }}</h2>
                                <span class="inline-flex items-center gap-1 mt-1 text-xs font-mono
                                             text-violet-700 bg-violet-50 px-2 py-0.5 rounded-md border border-violet-100">
                                    {{ $supplier->code }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('suppliers.edit', $supplier) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium
                                  text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                        </a>
                    </div>
                </div>

                {{-- Details --}}
                <dl class="divide-y divide-gray-100 px-6 text-sm">
                    <div class="flex justify-between py-3">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium text-gray-800">{{ $supplier->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-800">{{ $supplier->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-gray-500">Address</dt>
                        <dd class="font-medium text-gray-800 text-right max-w-xs">{{ $supplier->address ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-3">
                        <dt class="text-gray-500">Added on</dt>
                        <dd class="font-medium text-gray-800">{{ $supplier->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Financial summary --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Total Purchase</p>
                    <p class="text-xl font-semibold text-gray-800">৳{{ number_format($supplier->total_purchase, 2) }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Total Paid</p>
                    <p class="text-xl font-semibold text-green-600">৳{{ number_format($supplier->total_paid, 2) }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Due</p>
                    <p class="text-xl font-semibold {{ $supplier->due > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ৳{{ number_format($supplier->due, 2) }}
                    </p>
                    @if($supplier->due <= 0)
                        <span class="text-xs text-green-600 font-medium">Cleared</span>
                    @endif
                </div>
            </div>

            {{-- Back --}}
            <div>
                <a href="{{ route('suppliers.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Suppliers
                </a>
            </div>

        </div>
    </div>
</x-app-layout>