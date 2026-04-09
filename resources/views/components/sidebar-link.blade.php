@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
        {{ $active
            ? 'bg-blue-500/20 text-blue-400'
            : 'text-white/60 hover:text-white hover:bg-white/6' }}"
>
    @isset($icon)
        <span class="w-4 h-4 shrink-0">{{ $icon }}</span>
    @endisset
    <span class="truncate">{{ $slot }}</span>
</a>