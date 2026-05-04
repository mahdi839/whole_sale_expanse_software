<x-app-layout>
    <x-slot name="header">Create User</x-slot>
    <form method="POST" action="{{ route('users.store') }}">
        @include('users._form')
    </form>
</x-app-layout>
