<x-app-layout>
    <x-slot name="header">Edit Product</x-slot>

    <div class="max-w-xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-gray-400 mb-5">
            <a href="{{ route('products.index') }}" class="hover:text-gray-600 transition">Products</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
            <span class="text-gray-600">Edit — {{ $product->product_name }}</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-semibold text-gray-800">Edit Product</h2>
                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-1 rounded-md">#{{ $product->id }}</span>
            </div>

            <form
                method="POST"
                action="{{ route('products.update', $product) }}"
                enctype="multipart/form-data"
                class="space-y-5"
            >
                @csrf
                @method('PUT')
                @include('products._form', ['product' => $product])

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">

                    <div class="flex items-center gap-3">
                        <a href="{{ route('products.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                            Update Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>