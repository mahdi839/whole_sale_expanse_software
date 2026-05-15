<x-app-layout>
    <x-slot name="header">Add Received Cloth</x-slot>
    <div class="max-w-3xl space-y-4">
        <div class="bg-white border border-gray-200 rounded-xl p-5">
            <form method="POST" action="{{ route('received-cloths.store') }}" class="space-y-5">
                @csrf
                @include('cloth_sewings._form', ['receivedCloth' => $receivedCloth, 'routeBase' => 'received-cloths'])
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('received-cloths.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Cancel</a>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
