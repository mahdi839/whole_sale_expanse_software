<x-app-layout>
    <x-slot name="header">Roles</x-slot>
    <div class="space-y-4">
        @include('partials.flash')
        <div class="flex justify-end"><a href="{{ route('roles.create') }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">New Role</a></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($roles as $role)
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex justify-between gap-3">
                        <h3 class="font-semibold text-gray-800">{{ $role->name }}</h3>
                        <div class="shrink-0">
                            <a href="{{ route('roles.edit', $role) }}" class="text-blue-600 text-sm">Edit</a>
                            <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Delete this role?')">@csrf @method('DELETE') <button class="text-red-600 text-sm ml-2">Delete</button></form>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">{{ $role->permissions->pluck('name')->implode(', ') ?: 'No permissions' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
