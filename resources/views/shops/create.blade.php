<x-app-layout>
    <x-slot name="header">Create Shop</x-slot>
    <form method="POST" action="{{ route('shops.store') }}">@include('shops._form')</form>
</x-app-layout>
