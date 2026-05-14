<x-app-layout>
    <x-slot name="header">Edit Sales Man</x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="POST" action="{{ route('sales-men.update', $salesMan) }}" class="space-y-5">
                @csrf
                @method('PUT')
                @include('sales_men._form')
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('sales-men.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Cancel</a>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Update</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
