<x-app-layout>
    <x-slot name="header">Edit Permission</x-slot>
    <form method="POST" action="{{ route('permissions.update', $permission) }}">@method('PUT') @include('permissions._form')</form>
</x-app-layout>
