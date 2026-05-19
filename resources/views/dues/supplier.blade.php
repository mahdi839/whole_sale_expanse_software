<x-app-layout>
    <x-slot name="header">Supplier Wise Due</x-slot>

    <div class="space-y-4">
        @include('dues.partials.filters', [
            'route' => route('dues.supplier'),
            'exportRoute' => route('dues.supplier.export', request()->query()),
            'filters' => $filters,
            'placeholder' => 'Supplier name, phone, address, code...',
        ])

        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b">
                <h2 class="text-sm font-semibold text-gray-800">Supplier Wise Due</h2>
            </div>
            @include('dues.partials.supplier_table', ['rows' => $rows])
        </div>
    </div>
</x-app-layout>
