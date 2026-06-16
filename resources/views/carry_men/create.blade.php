<x-app-layout>
    <x-slot name="header">Add Carry Man</x-slot>

    <div class="min-h-[calc(100vh-11rem)] flex items-center justify-center py-8">
        <div class="w-full max-w-3xl bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <form method="POST" action="{{ route('carry-men.store') }}" class="space-y-5">
                @csrf
                @include('shared._worker_profile_form', ['worker' => $carryMan, 'hasDocumentNo' => true])
                <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <a href="{{ route('carry-men.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Cancel</a>
                    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
