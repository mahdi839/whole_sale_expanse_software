<x-app-layout>
    <x-slot name="header">Users</x-slot>
    <div class="space-y-4">
        @include('partials.flash')
        <div class="flex justify-end">
            <a href="{{ route('users.create') }}" class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm inline-flex items-center">New User</a>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50"><tr><th class="text-left px-4 py-3">User</th><th class="text-left px-4 py-3">Roles</th><th class="text-left px-4 py-3">Shop</th><th class="text-right px-4 py-3">Actions</th></tr></thead>
                <tbody class="divide-y">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3">{{ $user->name }}<br><span class="text-xs text-gray-400">{{ $user->email }}</span></td>
                            <td class="px-4 py-3">{{ $user->roles->pluck('name')->implode(', ') ?: '-' }}</td>
                            <td class="px-4 py-3">{{ $user->shop?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('users.edit', $user) }}" class="text-blue-600 text-sm">Edit</a>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE') <button class="text-red-600 text-sm ml-2">Delete</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-12 text-center text-gray-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-4 py-3">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
