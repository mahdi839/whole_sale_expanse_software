<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100">

    {{--
        RESPONSIVE BEHAVIOUR:
        Mobile  (<768px)  : No sidebar. Bottom nav bar. Hamburger opens a full slide-over drawer.
        Tablet  (768-1279): Icon-only sidebar (56px). Hamburger expands it to a full drawer overlay.
        Desktop (>=1280px): Full 260px persistent sidebar. No overlay needed.
    --}}

    <div x-data="{ drawerOpen: false }" @resize.window="drawerOpen = false" class="flex h-screen overflow-hidden">

        {{-- Backdrop (closes drawer on tap — mobile + tablet) --}}
        <div x-show="drawerOpen" x-transition:enter="transition-opacity duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="drawerOpen = false"
            class="fixed inset-0 z-40 bg-black/50 xl:hidden"></div>

        {{-- ===== SIDEBAR ===== --}}
        {{--
            Mobile  : fixed, off-screen by default, slides to w-64 when drawerOpen
            Tablet  : fixed, visible as w-14 (icons only), slides to w-64 when drawerOpen
            Desktop : relative, always w-64, never overlays
        --}}
        <aside :class="drawerOpen ? 'w-64' : 'max-md:-translate-x-full md:w-14 xl:w-64'"
            class="fixed inset-y-0 left-0 z-50 flex flex-col bg-[#1e2433]
                   transition-all duration-300 ease-in-out w-64
                   md:relative md:translate-x-0 xl:w-64">
            {{-- Logo --}}
            <div
                class="flex items-center gap-3 px-3.5 py-4 border-b border-white/10 shrink-0 overflow-hidden min-h-[60px]">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500 shrink-0">
                    <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24">
                        <path
                            d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM4 5h16a1 1 0 000-2H4a1 1 0 000 2z" />
                    </svg>
                </div>
                <div :class="drawerOpen ? 'opacity-100' : 'opacity-0 xl:opacity-100'"
                    class="overflow-hidden whitespace-nowrap transition-opacity duration-200">
                    <div class="text-white text-sm font-medium">{{ config('app.name') }}</div>
                    <div class="text-white/40 text-[11px]">Inventory System</div>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 space-y-0.5 px-2">

                {{-- Section labels shown only when expanded --}}
                <p :class="drawerOpen ? 'block' : 'hidden xl:block'"
                    class="px-3 pt-1 pb-1 text-[10px] font-medium uppercase tracking-widest text-white/30">Overview</p>

                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                        </svg></x-slot>
                    Dashboard
                </x-sidebar-link>

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'"
                    class="px-3 pt-3 pb-1 text-[10px] font-medium uppercase tracking-widest text-white/30">Inventory</p>

                <x-sidebar-dropdown label="People" :active="request()->routeIs('people.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg></x-slot>
                    <x-sidebar-sub-link :href="route('customers.index')">Customers</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('suppliers.index')">Suppliers</x-sidebar-sub-link>
                </x-sidebar-dropdown>

                <x-sidebar-dropdown label="Products" :active="request()->routeIs('products.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg></x-slot>
                    <x-sidebar-sub-link :href="route('products.index')">All Products</x-sidebar-sub-link>
                    {{-- <x-sidebar-sub-link :href="route('dashboard')">Categories</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dashboard')">Units</x-sidebar-sub-link> --}}
                </x-sidebar-dropdown>

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'"
                    class="px-3 pt-3 pb-1 text-[10px] font-medium uppercase tracking-widest text-white/30">Purchasing
                </p>

                <x-sidebar-dropdown label="Purchase Management" :active="request()->routeIs('purchases.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75">
                            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                            <line x1="3" y1="6" x2="21" y2="6" />
                            <path d="M16 10a4 4 0 01-8 0" />
                        </svg></x-slot>
                    <x-sidebar-sub-link :href="route('dashboard')" :active="request()->routeIs('purchases.index')">Purchase</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dashboard')" :active="request()->routeIs('purchases.returns.*')">Return Purchase</x-sidebar-sub-link>
                </x-sidebar-dropdown>

                <x-sidebar-dropdown label="Sales Management" :active="request()->routeIs('sales.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75">
                            <path
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg></x-slot>
                    <x-sidebar-sub-link :href="route('dashboard')">Sales Orders</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dashboard')">Return Sales</x-sidebar-sub-link>
                </x-sidebar-dropdown>

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'"
                    class="px-3 pt-3 pb-1 text-[10px] font-medium uppercase tracking-widest text-white/30">Reports &
                    Settings</p>

                <x-sidebar-link :href="route('dashboard')" :active="false">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75">
                            <path
                                d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg></x-slot>
                    Reports
                </x-sidebar-link>

                <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75">
                            <path
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg></x-slot>
                    Settings
                </x-sidebar-link>

            </nav>

            {{-- User footer --}}
            <div class="shrink-0 border-t border-white/10 px-2 py-3">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 shrink-0 text-white text-xs font-medium">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div :class="drawerOpen ? 'opacity-100 flex-1' : 'opacity-0 w-0 xl:opacity-100 xl:flex-1'"
                        class="overflow-hidden transition-all duration-200 min-w-0">
                        <div class="text-white/90 text-xs font-medium truncate">{{ Auth::user()->name }}</div>
                        <div class="text-white/40 text-[11px] truncate">{{ Auth::user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}"
                        :class="drawerOpen ? 'block' : 'hidden xl:block'" class="shrink-0">
                        @csrf
                        <button type="submit" title="Log Out"
                            class="text-white/30 hover:text-white/70 transition-colors p-1">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ===== MAIN AREA ===== --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            {{-- Top Header --}}
            <header
                class="flex items-center justify-between h-14 px-4 sm:px-5 bg-white border-b border-gray-200 shrink-0 z-30">

                <div class="flex items-center gap-3">
                    <button @click="drawerOpen = !drawerOpen"
                        class="p-1.5 -ml-1 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition xl:hidden"
                        aria-label="Toggle menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path x-show="!drawerOpen" stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="drawerOpen" stroke-linecap="round" stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    @isset($header)
                        <h1 class="text-sm font-medium text-gray-800">{{ $header }}</h1>
                    @endisset
                </div>

                <div class="flex items-center gap-2">
                    <button
                        class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            <div
                                class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-500 text-white text-xs font-medium shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <span
                                class="hidden sm:block text-sm text-gray-700 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile
                            </a>
                            <div class="my-1 border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content (extra bottom padding on mobile for bottom nav) --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-5 pb-20 md:pb-5">
                {{ $slot }}
            </main>

            {{-- ===== BOTTOM NAV (mobile only) ===== --}}
            <nav
                class="md:hidden fixed bottom-0 inset-x-0 z-30 flex items-center justify-around h-14 bg-[#1e2433] border-t border-white/10">

                <a href="{{ route('dashboard') }}"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('dashboard') ? 'text-blue-400' : 'text-white/45' }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                    </svg>
                    <span class="text-[9px] font-medium">Home</span>
                </a>

                <a href="#"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('purchases.*') ? 'text-blue-400' : 'text-white/45' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75"
                        viewBox="0 0 24 24">
                        <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <path d="M16 10a4 4 0 01-8 0" />
                    </svg>
                    <span class="text-[9px] font-medium">Purchase</span>
                </a>

                <a href="#"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('sales.*') ? 'text-blue-400' : 'text-white/45' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75"
                        viewBox="0 0 24 24">
                        <path
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="text-[9px] font-medium">Sales</span>
                </a>

                <a href="#"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('reports.*') ? 'text-blue-400' : 'text-white/45' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75"
                        viewBox="0 0 24 24">
                        <path
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-[9px] font-medium">Reports</span>
                </a>

                <button @click="drawerOpen = !drawerOpen"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 text-white/45">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75"
                        viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h10" />
                    </svg>
                    <span class="text-[9px] font-medium">More</span>
                </button>

            </nav>

        </div>
    </div>

    @stack('scripts')
</body>

</html>
