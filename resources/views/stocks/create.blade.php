<x-app-layout>
    <x-slot name="header">Set Central Stock</x-slot>

    <form method="POST" action="{{ route('stocks.store') }}">
        @include('stocks._form', ['stock' => null])
    </form>
</x-app-layout>
