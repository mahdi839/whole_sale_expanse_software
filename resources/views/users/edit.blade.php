<x-app-layout>
    <x-slot name="header">Edit User</x-slot>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @method('PUT')
        @include('users._form')
    </form>
</x-app-layout>
