<x-app-layout>
    <x-slot name="header">New Purchase Return</x-slot>

    <div class="space-y-4">
        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('purchase-returns.index') }}" class="hover:text-gray-600">Purchase Returns</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">Add Purchase Return</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/60 flex justify-between items-center">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">New Purchase Return</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Add return items, discount, and approval status</p>
                </div>
                <span class="px-2.5 py-1.5 bg-violet-50 border border-violet-200 rounded-lg text-xs font-mono font-medium text-violet-700">
                    {{ $nextReference }}
                </span>
            </div>

            <form method="POST" action="{{ route('purchase-returns.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="p-6">
                    @include('purchase_returns._form', ['nextReference' => $nextReference, 'purchaseReturn' => null, 'purchase' => $purchase ?? null])
                </div>
                <div class="flex justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a href="{{ route('purchase-returns.index') }}" class="h-9 px-4 inline-flex items-center text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="h-9 px-5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Save Purchase Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>