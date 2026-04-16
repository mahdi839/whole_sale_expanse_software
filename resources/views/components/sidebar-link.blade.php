@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    title="{{ $slot }}"
    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm transition-colors group
        {{ $active
            ? 'bg-blue-500/20 text-white'
            : 'text-white hover:text-white hover:bg-white/6' }}"
>
    @isset($icon)
        <span class="w-4 h-4 shrink-0 flex items-center justify-center">{{ $icon }}</span>
    @endisset

    {{-- Label: hidden on tablet icon-only, shown when drawer open or desktop --}}
    <span
        :class="drawerOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0 xl:opacity-100 xl:w-auto'"
        class="overflow-hidden whitespace-nowrap transition-all duration-200 text-[13px]"
    >{{ $slot }}</span>
</a>