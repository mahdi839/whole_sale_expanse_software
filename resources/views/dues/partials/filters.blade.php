<div class="bg-white border border-gray-200 rounded-xl p-4 sm:p-5">
    <form method="GET" action="{{ $route }}">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                placeholder="{{ $placeholder }}"
                class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">

            <input type="date" name="date" value="{{ $filters['date'] ?? now()->toDateString() }}"
                class="h-10 px-3 text-sm bg-gray-50 border border-gray-200 rounded-lg w-full">

            <button type="submit"
                class="h-10 px-4 bg-gray-800 text-white rounded-lg text-sm w-full sm:w-auto">
                Filter
            </button>

            <a href="{{ $route }}"
                class="h-10 px-4 bg-cyan-600 text-white rounded-lg text-sm inline-flex items-center justify-center w-full sm:w-auto">
                Reset
            </a>
        </div>
    </form>
</div>
