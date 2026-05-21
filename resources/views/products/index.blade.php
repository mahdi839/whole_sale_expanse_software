<x-app-layout>
    <x-slot name="header">Products</x-slot>

    <div class="space-y-4">

        {{-- Top bar: search + add button --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            {{-- Search --}}
            <form method="GET" action="{{ route('products.index') }}"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:max-w-md">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="Search by name, design code or product code…"
                        class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="flex-1 sm:flex-none px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Search
                    </button>

                    @if ($search)
                        <a href="{{ route('products.index') }}"
                            class="inline-flex items-center justify-center px-3 py-2 text-sm text-gray-500 bg-white border border-gray-200 rounded-lg hover:text-gray-700 hover:bg-gray-50 transition"
                            title="Clear search">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Add button --}}
            <a href="{{ route('products.create') }}"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shrink-0 w-full sm:w-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Add Product
            </a>
        </div>

        @if (session('error'))
            <div
                class="flex items-center gap-3 px-4 py-3 text-sm text-red-800 bg-red-50 border border-red-200 rounded-lg">
                <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9a1 1 0 012 0v3a1 1 0 01-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Flash messages --}}
        @if (session('success'))
            <div
                class="flex items-center gap-3 px-4 py-3 text-sm text-green-800 bg-green-50 border border-green-200 rounded-lg">
                <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @forelse($products as $product)
                <div class="bg-white border border-gray-200 rounded-xl p-4 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0">
                            @if ($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->product_name }}"
                                    class="w-14 h-14 rounded-lg object-cover border border-gray-100" />
                            @else
                                <div
                                    class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-100 flex items-center justify-center text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-medium text-gray-800 break-words">
                                {{ $product->product_name }}
                            </h3>

                            <div class="mt-2">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-xs font-mono font-medium break-all">
                                    {{ $product->sku }}
                                </span>
                                @if($product->product_code)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 text-xs font-mono font-medium break-all">
                                        {{ $product->product_code }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Stock</p>
                            <p class="mt-1">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-md @if ($product->stock?->stock_qty < 10) bg-red-100 text-red-600 @else bg-gray-100 text-gray-600 @endif text-xs font-mono font-medium">
                                    {{ $product->stock?->stock_qty ?? 0 }}
                                </span>
                            </p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Purchase Price</p>
                            <p class="mt-1 font-medium text-gray-700">৳{{ number_format($product->purchase_price, 2) }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Sale Price</p>
                            <p class="mt-1 font-medium text-gray-700">৳{{ number_format($product->selling_price, 2) }}</p>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-2">
                            <p class="text-gray-400">Added</p>
                            <p class="mt-1 font-medium text-gray-700">
                                {{ $product->created_at->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <a href="{{ route('products.barcode', $product) }}"
                            class="inline-flex items-center justify-center w-9 h-9 text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition"
                            title="Generate barcode">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M4 5v14M8 5v14M13 5v14M17 5v14M20 5v14"/>
                            </svg>
                        </a>
                        <a href="{{ route('products.edit', $product) }}"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </a>

                        <form method="POST" action="{{ route('products.destroy', $product) }}" class="flex-1"
                            onsubmit="return confirm('Delete \'{{ addslashes($product->product_name) }}\'? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-xl px-5 py-12 text-center">
                    <div class="flex flex-col items-center gap-3 text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path
                                d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM4 5h16a1 1 0 000-2H4a1 1 0 000 2z" />
                        </svg>
                        <p class="text-sm">
                            @if ($search)
                                No products found for <span
                                    class="font-medium text-gray-600">"{{ $search }}"</span>
                            @else
                                No products yet.
                                <a href="{{ route('products.create') }}" class="text-blue-600 hover:underline">Add
                                    your first product.</a>
                            @endif
                        </p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60">
                            <th class="text-left px-5 py-3 font-medium text-gray-500 w-16">Image</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Product Name</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Design Code</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Product Code</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Purchase Price</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Sale Price</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500">Stock</th>
                            <th class="text-left px-5 py-3 font-medium text-gray-500 hidden sm:table-cell">Added</th>
                            <th class="text-right px-5 py-3 font-medium text-gray-500">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50/50 transition-colors">

                                {{-- Image --}}
                                <td class="px-5 py-3">
                                    @if ($product->image)
                                        <img src="{{ Storage::url($product->image) }}"
                                            alt="{{ $product->product_name }}"
                                            class="w-10 h-10 rounded-lg object-cover border border-gray-100" />
                                    @else
                                        <div
                                            class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-100 flex items-center justify-center text-gray-300">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                stroke-width="1.5" viewBox="0 0 24 24">
                                                <path
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>

                                {{-- Name --}}
                                <td class="px-5 py-3 font-medium text-gray-800">{{ $product->product_name }}</td>

                                {{-- SKU --}}
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 text-xs font-mono font-medium">
                                        {{ $product->sku }}
                                    </span>
                                </td>

                                <td class="px-5 py-3">
                                    <span class="text-xs font-mono text-gray-600">{{ $product->product_code ?? '—' }}</span>
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-gray-700">
                                    ৳{{ number_format($product->purchase_price, 2) }}
                                </td>

                                <td class="px-5 py-3 text-right font-medium text-gray-700">
                                    ৳{{ number_format($product->selling_price, 2) }}
                                </td>

                                {{-- Stock --}}
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-md @if ($product->stock?->stock_qty < 10) bg-red-100 text-red-600 @else bg-gray-100 text-gray-600 @endif text-xs font-mono font-medium">
                                        {{ $product->stock?->stock_qty ?? 0 }}
                                    </span>
                                </td>

                                {{-- Date --}}
                                <td class="px-5 py-3 text-gray-400 text-xs hidden sm:table-cell">
                                    {{ $product->created_at->format('d M Y') }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.barcode', $product) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 text-amber-700 bg-amber-50 rounded-lg hover:bg-amber-100 transition"
                                            title="Generate barcode">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M4 5v14M8 5v14M13 5v14M17 5v14M20 5v14"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>

                                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                                            onsubmit="return confirm('Delete \'{{ addslashes($product->product_name) }}\'? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    stroke-width="2" viewBox="0 0 24 24">
                                                    <path
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-gray-400">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path
                                                d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM4 5h16a1 1 0 000-2H4a1 1 0 000 2z" />
                                        </svg>
                                        <p class="text-sm">
                                            @if ($search)
                                                No products found for <span
                                                    class="font-medium text-gray-600">"{{ $search }}"</span>
                                            @else
                                                No products yet.
                                                <a href="{{ route('products.create') }}"
                                                    class="text-blue-600 hover:underline">Add your first product.</a>
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($products->hasPages())
                <div class="px-5 py-3 border-t border-gray-100 bg-gray-50/40">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        {{-- Results count --}}
        @if ($products->total() > 0)
            <p class="text-xs text-gray-400">
                Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} products
                @if ($search)
                    matching <span class="text-gray-600">"{{ $search }}"</span>
                @endif
            </p>
        @endif

    </div>
</x-app-layout>
