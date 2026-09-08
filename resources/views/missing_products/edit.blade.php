<x-app-layout>
    <x-slot name="header">Edit Missing Product</x-slot>

    <div class="max-w-3xl mx-auto">
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-5">
            <a href="{{ route('missing-products.index') }}" class="hover:text-gray-600 transition">Track Missing Products</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6" />
            </svg>
            <span class="text-gray-600">Edit — {{ $missingProduct->product?->product_name ?? 'Entry' }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Edit Missing Product</h2>
            <form method="POST" action="{{ route('missing-products.update', $missingProduct) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                @include('missing_products._form')
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('missing-products.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg">Cancel</a>
                    <button class="px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
