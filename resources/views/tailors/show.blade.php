<x-app-layout>
    <x-slot name="header">Tailor Profile</x-slot>

    <div class="max-w-5xl space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if($tailor->profile_picture)
                        <img src="{{ asset('storage/'.$tailor->profile_picture) }}" alt="{{ $tailor->name }}" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                    @else
                        <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center text-lg font-semibold">
                            {{ strtoupper(substr($tailor->name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">{{ $tailor->name }}</h2>
                        <p class="text-sm text-gray-500">{{ $tailor->phone ?? 'No phone' }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if($tailor->document_path)
                        <a href="{{ asset('storage/'.$tailor->document_path) }}" target="_blank" class="px-3 py-1.5 text-xs text-emerald-700 bg-emerald-50 rounded-lg">Document</a>
                    @endif
                    <a href="{{ route('tailors.edit', $tailor) }}" class="px-3 py-1.5 text-xs text-blue-700 bg-blue-50 rounded-lg">Edit</a>
                </div>
            </div>

            <dl class="mt-6 divide-y divide-gray-100 text-sm">
                <div class="flex justify-between gap-4 py-3">
                    <dt class="text-gray-500">Address</dt>
                    <dd class="font-medium text-gray-800 text-right">{{ $tailor->address ?? '-' }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500">Total Sewing Qty</dt>
                    <dd class="font-medium text-indigo-600">{{ number_format($tailor->clothSewings->sum(fn ($item) => (float) $item->item_qty), 2) }}</dd>
                </div>
                <div class="flex justify-between py-3">
                    <dt class="text-gray-500">Total Received Qty</dt>
                    <dd class="font-medium text-green-600">{{ number_format($tailor->receivedCloths->sum(fn ($item) => (float) $item->item_qty), 2) }}</dd>
                </div>
            </dl>
        </div>

        <div>
            <a href="{{ route('tailors.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700">
                Back to Tailors
            </a>
        </div>
    </div>
</x-app-layout>
