<x-app-layout>
    <x-slot name="header">Add Stock</x-slot>

    <div class="space-y-4">

        <nav class="flex items-center gap-1.5 text-xs text-gray-400">
            <a href="{{ route('stocks.index') }}" class="hover:text-gray-600 transition">Stocks</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 18l6-6-6-6"/>
            </svg>
            <span class="text-gray-600">Add Stock</span>
        </nav>

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                <div>
                    <h2 class="text-sm font-semibold text-gray-800">New Stock</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Fill in the details below to register stock.</p>
                </div>

                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-violet-50 border border-violet-200
                             rounded-lg text-xs font-mono font-medium text-violet-700 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    Stock Entry
                </span>
            </div>

            <form method="POST" action="{{ route('stocks.store') }}">
                @csrf

                <div class="px-6 py-6 space-y-4">

                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div class="flex items-center gap-2.5 px-5 py-3 border-b border-gray-100 bg-gray-50/60">
                            <span class="flex items-center justify-center w-6 h-6 rounded-md bg-blue-50 text-blue-700 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                                    <path d="M8 11h8M8 15h5"/>
                                </svg>
                            </span>
                            <span class="text-xs font-semibold text-gray-700 tracking-wide uppercase">Stock Information</span>
                        </div>

                        <div class="p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <div>
                                    <label for="product_id" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">
                                        Product ID <span class="text-red-400 normal-case">*</span>
                                    </label>
                                    <input type="number" id="product_id" name="product_id"
                                           value="{{ old('product_id') }}"
                                           placeholder="Enter product id"
                                           class="w-full h-9 px-3 text-sm bg-gray-50 border {{ $errors->has('product_id') ? 'border-red-300 bg-red-50' : 'border-gray-200' }} rounded-lg
                                                  text-gray-800 placeholder-gray-400
                                                  focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                                    @error('product_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="stock_qty" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">
                                        Stock Qty <span class="text-red-400 normal-case">*</span>
                                    </label>
                                    <input type="number" id="stock_qty" name="stock_qty"
                                           value="{{ old('stock_qty') }}"
                                           placeholder="Enter stock quantity"
                                           min="0"
                                           class="w-full h-9 px-3 text-sm bg-gray-50 border {{ $errors->has('stock_qty') ? 'border-red-300 bg-red-50' : 'border-gray-200' }} rounded-lg
                                                  text-gray-800 placeholder-gray-400
                                                  focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition">
                                    @error('stock_qty')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                    <a href="{{ route('stocks.index') }}"
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
                        Save Stock
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>