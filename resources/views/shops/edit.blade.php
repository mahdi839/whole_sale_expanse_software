<x-app-layout>
    <x-slot name="header">Edit Shop</x-slot>
    <form method="POST" action="{{ route('shops.update', $shop) }}">@method('PUT') @include('shops._form')</form>
</x-app-layout>
