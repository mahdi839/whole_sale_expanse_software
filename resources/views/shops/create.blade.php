<x-app-layout>
    <x-slot name="header">Create Shop</x-slot>
    <div class="min-h-[calc(100vh-7rem)] flex items-center justify-center">
        <form method="POST" action="{{ route('shops.store') }}" class="w-full max-w-2xl">@include('shops._form')</form>
    </div>
</x-app-layout>
