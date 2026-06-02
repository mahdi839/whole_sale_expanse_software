<x-app-layout>
    <x-slot name="header">Edit Employee</x-slot>

    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('employees._form')
            <div class="flex justify-end gap-2">
                <a href="{{ route('employees.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg">Cancel</a>
                <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Update Employee</button>
            </div>
        </form>
    </div>
</x-app-layout>
