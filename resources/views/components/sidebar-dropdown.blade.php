@props(['label', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">

    {{-- Toggle button --}}
    <button
        @click="open = !open"
        class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors
            {{ $active
                ? 'text-blue-400'
                : 'text-white/60 hover:text-white hover:bg-white/6' }}"
    >
        @isset($icon)
            <span class="w-4 h-4 shrink-0">{{ $icon }}</span>
        @endisset

        <span class="flex-1 text-left truncate">{{ $label }}</span>

        {{-- Chevron --}}
        <svg
            :class="open ? 'rotate-90' : ''"
            class="w-3.5 h-3.5 shrink-0 text-white/30 transition-transform duration-200"
            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
        >
            <path d="M9 18l6-6-6-6"/>
        </svg>
    </button>

    {{-- Submenu --}}
    <div
        x-show="open"
        x-transition:enter="transition-all duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition-all duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="mt-0.5 ml-7 space-y-0.5 border-l border-white/10 pl-3"
    >
        {{ $slot }}
    </div>

</div>