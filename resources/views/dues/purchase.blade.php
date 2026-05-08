<x-app-layout>
    <x-slot name="header">Purchase Wise Due</x-slot>

    <div class="space-y-4">
        @include('dues.partials.filters', [
            'route' => route('dues.purchase'),
            'filters' => $filters,
            'placeholder' => 'Reference, supplier, phone, bill no...',
        ])

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b">
                <h2 class="text-sm font-semibold text-gray-800">Purchase Wise Due</h2>
            </div>
            @include('dues.partials.purchase_table', ['rows' => $rows])
        </div>
    </div>
</x-app-layout>
