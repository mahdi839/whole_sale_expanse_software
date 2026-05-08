<x-app-layout>
    <x-slot name="header">Customer Wise Due</x-slot>

    <div class="space-y-4">
        @include('dues.partials.filters', [
            'route' => route('dues.customer'),
            'filters' => $filters,
            'placeholder' => 'Customer name, phone, code...',
        ])

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b">
                <h2 class="text-sm font-semibold text-gray-800">Customer Wise Due</h2>
            </div>
            @include('dues.partials.customer_table', ['rows' => $rows])
        </div>
    </div>
</x-app-layout>
