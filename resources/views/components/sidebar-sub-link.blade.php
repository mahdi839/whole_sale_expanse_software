@props(['href', 'active' => false])

<a
    href="{{ $href }}"
    class="flex items-center gap-2 px-2 py-1.5 rounded-md text-xs transition-colors
        {{ $active
            ? 'text-blue-400'
            : 'text-white hover:text-white/85 hover:bg-white/5' }}"
>
    <span class="w-1.5 h-1.5 rounded-full bg-current shrink-0"></span>
    {{ $slot }}
</a>