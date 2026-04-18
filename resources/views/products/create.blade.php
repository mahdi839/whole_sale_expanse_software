<x-app-layout>
    <x-slot name="header">Add Product</x-slot>

    <div class="max-w-xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-5">
            <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition">Products</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6" />
            </svg>
            <span class="text-gray-600">Add Product</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-800 mb-5">Product Details</h2>

            <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @include('products._form')

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('products.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5"
                            viewBox="0 0 24 24">
                            <path d="M5 13l4 4L19 7" />
                        </svg>
                        Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
