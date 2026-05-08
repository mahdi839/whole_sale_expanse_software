<x-app-layout>
    <x-slot name="header">Sale Wise Due</x-slot>

    <div class="space-y-4">
        @include('dues.partials.filters', [
            'route' => route('dues.sale'),
            'filters' => $filters,
            'placeholder' => 'Reference, customer, phone, bill no...',
        ])

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b">
                <h2 class="text-sm font-semibold text-gray-800">Sale Wise Due</h2>
            </div>
            @include('dues.partials.sale_table', ['rows' => $rows])
        </div>
    </div>
</x-app-layout>
