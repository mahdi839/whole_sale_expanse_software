<x-app-layout>
    <x-slot name="header">Add Return</x-slot>

    <div class="space-y-4">

        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sale-returns.index') }}" class="hover:text-gray-600 transition">Sale Returns</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">Add Return</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">New Return</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Fill in the details below to register a sale return.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-orange-50 border border-orange-200
                             rounded-lg text-xs font-mono font-medium text-orange-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    {{ $nextReference }}
                </span>
            </div>

            <form method="POST" action="{{ route('sale-returns.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="px-6 py-6 space-y-4">
                    @include('sale_returns._form', ['sale' => $sale ?? null])
                </div>
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a href="{{ route('sale-returns.index') }}"
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
                        Save Return
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>