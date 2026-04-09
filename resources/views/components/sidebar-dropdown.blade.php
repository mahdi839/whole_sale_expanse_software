@props(['label', 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">

    <button
        @click="open = !open"
        :title="drawerOpen || window.innerWidth >= 1280 ? '' : '{{ $label }}'"
        class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-[13px] transition-colors
            {{ $active
                ? 'text-blue-400'
                : 'text-white/60 hover:text-white hover:bg-white/6' }}"
    >
        @isset($icon)
            <span class="w-4 h-4 shrink-0 flex items-center justify-center">{{ $icon }}</span>
        @endisset

        {{-- Label --}}
        <span
            :class="drawerOpen ? 'opacity-100 flex-1' : 'opacity-0 w-0 flex-none xl:opacity-100 xl:flex-1'"
            class="overflow-hidden whitespace-nowrap text-left transition-all duration-200"
        >{{ $label }}</span>

        {{-- Chevron (only shown when label is visible) --}}
        <svg
            x-show="drawerOpen || window.innerWidth >= 1280"
            :class="open ? 'rotate-90' : ''"
            class="w-3.5 h-3.5 shrink-0 text-white/30 transition-transform duration-200"
            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
        >
            <path d="M9 18l6-6-6-6"/>
        </svg>
    </button>

    {{-- Submenu: hidden when icon-only --}}
    <div
        x-show="open && (drawerOpen || window.innerWidth >= 1280)"
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