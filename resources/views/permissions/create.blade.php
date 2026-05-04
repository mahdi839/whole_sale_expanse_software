<x-app-layout>
    <x-slot name="header">Create Permission</x-slot>
    <form method="POST" action="{{ route('permissions.store') }}">@include('permissions._form')</form>
</x-app-layout>
