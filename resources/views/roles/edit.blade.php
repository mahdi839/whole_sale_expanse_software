<x-app-layout>
    <x-slot name="header">Edit Role</x-slot>
    <form method="POST" action="{{ route('roles.update', $role) }}">@method('PUT') @include('roles._form')</form>
</x-app-layout>
