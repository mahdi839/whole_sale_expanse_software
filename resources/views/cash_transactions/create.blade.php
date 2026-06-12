<x-app-layout>
    <x-slot name="header">New Cash Entry</x-slot>

    <div class="min-h-[calc(100vh-180px)] flex items-center justify-center">
        <form method="POST" action="{{ route('cash-transactions.store') }}" class="w-full max-w-3xl bg-white border border-gray-200 rounded-xl p-5">
            @csrf
            @include('cash_transactions._form')
            <div class="flex gap-2 mt-5">
                <button class="h-10 px-4 bg-blue-600 text-white rounded-lg text-sm">Save</button>
                <a href="{{ route('cash-transactions.index') }}" class="h-10 px-4 bg-gray-100 text-gray-700 rounded-lg text-sm inline-flex items-center">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
