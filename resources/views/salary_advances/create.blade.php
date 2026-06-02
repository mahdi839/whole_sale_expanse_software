<x-app-layout>
    <x-slot name="header">Add Advance Salary</x-slot>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <form method="POST" action="{{ route('salary-advances.store') }}" class="space-y-5">
            @csrf
            @include('salary_advances._form')
            <div class="flex justify-end gap-2">
                <a href="{{ route('salary-advances.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Cancel</a>
                <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Save Advance</button>
            </div>
        </form>
    </div>
</x-app-layout>
