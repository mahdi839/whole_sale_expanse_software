<x-app-layout>
    <x-slot name="header">Assign Executives</x-slot>
    <form method="POST" action="{{ route('shops.executives.sync', $shop) }}" class="bg-white border border-gray-200 rounded-xl p-5 space-y-4">
        @csrf
        <h2 class="font-semibold text-gray-800">{{ $shop->name }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach($users as $user)
                <label class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg text-sm">
                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" @checked($user->shop_id === $shop->id)>
                    <span>{{ $user->name }} <span class="text-gray-400">({{ $user->roles->pluck('name')->implode(', ') ?: 'no role' }})</span></span>
                </label>
            @endforeach
        </div>
        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Save Assignments</button>
            <a href="{{ route('shops.show', $shop) }}" class="px-4 py-2 bg-gray-100 rounded-lg text-sm">Cancel</a>
        </div>
    </form>
</x-app-layout>
