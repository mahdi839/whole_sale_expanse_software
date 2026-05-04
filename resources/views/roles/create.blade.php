<x-app-layout>
    <x-slot name="header">Create Role</x-slot>
    <form method="POST" action="{{ route('roles.store') }}">@include('roles._form')</form>
</x-app-layout>
