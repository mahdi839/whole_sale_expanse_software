<x-app-layout>
    <x-slot name="header">Edit Sale</x-slot>

    <div class="space-y-4">
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('sales.index') }}" class="hover:text-gray-600">Sales</a>
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600 break-all">{{ $sale->reference }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-gray-800">Edit Sale</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Update products, discount or payment</p>
                </div>

                <span class="inline-flex items-center w-fit max-w-full px-2.5 py-1.5 bg-violet-50 border border-violet-200 rounded-lg text-xs font-mono font-medium text-violet-700 break-all">
                    {{ $sale->reference }}
                </span>
            </div>

            <form method="POST" action="{{ route('sales.update', $sale) }}">
                @csrf
                @method('PUT')

                <div class="p-4 sm:p-6">
                    @if($errors->any())
                        <div class="mb-4 px-4 py-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl">
                            {{ $errors->first() }}
                        </div>
                    @endif
                    @include('sales._form', ['sale' => $sale])
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5 px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a
                        href="{{ route('sales.index') }}"
                        class="h-10 px-4 inline-flex items-center justify-center text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 w-full sm:w-auto"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="h-10 px-5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 w-full sm:w-auto"
                    >
                        Update Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
