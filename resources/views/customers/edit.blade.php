<x-app-layout>
    <x-slot name="header">Edit Customer</x-slot>

    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-8 px-4">
        <div class="w-full max-w-xl">

            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-gray-400 mb-5">
                <a href="{{ route('customers.index') }}" class="hover:text-gray-600 transition">Customers</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                <span class="text-gray-600">Edit — {{ $customer->full_name }}</span>
            </nav>

            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">

                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-base font-semibold text-gray-800">Edit Customer</h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 border border-blue-100 rounded-lg">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        <span class="text-xs font-mono font-medium text-blue-700">{{ $customer->code }}</span>
                    </span>
                </div>

                <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-5">
                    @csrf
                    @method('PUT')
                    @include('customers._form', ['customer' => $customer])

                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('customers.index') }}"
                               class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                Cancel
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                Update Customer
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>