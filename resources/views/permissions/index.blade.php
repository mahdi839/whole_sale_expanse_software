<x-app-layout>
    <x-slot name="header">Permissions</x-slot>
    <div class="space-y-4">
        @include('partials.flash')
        <div class="flex justify-end"><a href="{{ route('permissions.create') }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">New Permission</a></div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <tbody class="divide-y">
                    @foreach($permissions as $permission)
                        <tr>
                            <td class="px-4 py-3">{{ $permission->name }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('permissions.edit', $permission) }}" class="text-blue-600">Edit</a>
                                <form method="POST" action="{{ route('permissions.destroy', $permission) }}" class="inline" onsubmit="return confirm('Delete this permission?')">@csrf @method('DELETE') <button class="text-red-600 ml-2">Delete</button></form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
