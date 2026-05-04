<x-app-layout>
    <x-slot name="header">Edit Central Stock</x-slot>
    <form method="POST" action="{{ route('stocks.update', $stock) }}">
        @method('PUT')
        @include('stocks._form')
    </form>
</x-app-layout>
