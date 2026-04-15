<x-app-layout>
    <x-slot name="header">Edit Sale</x-slot>

    <div class="space-y-4">

        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sales.index') }}" class="hover:text-gray-600 transition">Sales</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">{{ $sale->reference }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">Edit Sale</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Update the sale information below.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-violet-50 border border-violet-200
                             rounded-lg text-xs font-mono font-medium text-violet-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    {{ $sale->reference }}
                </span>
            </div>

            <form method="POST" action="{{ route('sales.update', $sale) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="px-6 py-6 space-y-4">
                    @include('sales._form', ['sale' => $sale])
                </div>
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a href="{{ route('sales.index') }}"
                       class="h-9 px-4 inline-flex items-center text-sm font-medium text-gray-600
                              bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="h-9 px-5 inline-flex items-center gap-2 text-sm font-medium text-white
                                   bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Sale
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>