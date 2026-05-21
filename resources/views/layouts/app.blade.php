<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('inaya_creation_logo.jpeg') }}">
    <title>{{ config('app.name', 'Inaya Creation') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-brand:        #3726b0;
            --sidebar-brand-dark:   #130f55;
            --sidebar-active-bg:    rgba(255,255,255,0.12);
            --sidebar-hover-bg:     rgba(255,255,255,0.06);
            --sidebar-divider:      rgba(255,255,255,0.08);
            --sidebar-text:         rgba(255,255,255,0.70);
            --sidebar-text-bright:  #ffffff;
            --sidebar-text-dim:     rgba(255,255,255,0.28);
            --sidebar-accent:       #a78bfa;
            --sidebar-accent-glow:  rgba(167,139,250,0.15);
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ── Sidebar shell ── */
        #app-sidebar {
            background: linear-gradient(175deg, #2a1fa8 0%, #1e1680 40%, #130f55 100%);
            border-right: 1px solid rgba(255,255,255,0.06);
            position: relative;
            overflow: hidden;
        }
        #app-sidebar::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(167,139,250,0.18) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        #app-sidebar > * { position: relative; z-index: 1; }

        /* ── Logo row ── */
        .sidebar-logo-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 18px 14px 16px;
            border-bottom: 1px solid var(--sidebar-divider);
            flex-shrink: 0;
        }
        .sidebar-logo-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.06));
            border: 1px solid rgba(255,255,255,0.15);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
        }
        .sidebar-logo-icon img { width: 28px; height: 28px; object-fit: contain; }
        .sidebar-logo-name { font-size: 13.5px; font-weight: 600; color: #fff; letter-spacing: -0.01em; line-height: 1.2; }
        .sidebar-logo-sub  { font-size: 10.5px; color: rgba(255,255,255,0.45); margin-top: 1px; letter-spacing: 0.02em; }

        /* ── Nav ── */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 10px 10px 6px;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        .sidebar-section {
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--sidebar-text-dim);
            padding: 12px 8px 5px;
        }
        /* hide section labels in icon-only mode */
        .sidebar-section-hidden {
            display: none;
        }

        /* ── Nav item (single link) ── */
        .sidebar-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 9px;
            transition: background 0.15s ease;
            margin-bottom: 1px;
            position: relative;
            color: var(--sidebar-text);
            text-decoration: none;
        }
        .sidebar-nav-item:hover { background: var(--sidebar-hover-bg); color: var(--sidebar-text-bright); }
        .sidebar-nav-item.active { background: var(--sidebar-active-bg); color: var(--sidebar-text-bright); }
        .sidebar-nav-item.active::before {
            content: '';
            position: absolute;
            left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 18px;
            background: var(--sidebar-accent);
            border-radius: 0 3px 3px 0;
        }
        .sidebar-nav-icon {
            width: 16px; height: 16px;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .sidebar-nav-icon svg { width: 16px; height: 16px; }
        .sidebar-nav-item.active .sidebar-nav-icon { color: var(--sidebar-accent); }

        .sidebar-nav-label {
            font-size: 13px;
            font-weight: 500;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-nav-badge {
            font-size: 10px; font-weight: 600;
            background: var(--sidebar-accent-glow);
            border: 1px solid rgba(167,139,250,0.3);
            color: var(--sidebar-accent);
            padding: 1px 6px;
            border-radius: 20px;
        }

        /* ── Dropdown ── */
        .sidebar-dropdown-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 9px;
            cursor: pointer;
            transition: background 0.15s;
            color: var(--sidebar-text);
            width: 100%;
            background: none;
            border: none;
            text-align: left;
            margin-bottom: 1px;
        }
        .sidebar-dropdown-trigger:hover,
        .sidebar-dropdown-trigger[data-open="true"] { background: var(--sidebar-hover-bg); color: var(--sidebar-text-bright); }

        .sidebar-dropdown-label { font-size: 13px; font-weight: 500; flex: 1; white-space: nowrap; }
        .sidebar-chevron {
            width: 12px; height: 12px;
            flex-shrink: 0;
            color: var(--sidebar-text-dim);
            transition: transform 0.2s ease;
        }
        .sidebar-chevron.open { transform: rotate(90deg); }

        .sidebar-submenu {
            padding: 3px 0 3px 36px;
            display: flex;
            flex-direction: column;
            gap: 1px;
        }

        /* ── Sub-link ── */
        .sidebar-sub-item {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            border-radius: 7px;
            font-size: 12.5px;
            color: rgba(255,255,255,0.5);
            font-weight: 400;
            text-decoration: none;
            transition: all 0.12s;
        }
        .sidebar-sub-item::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.5;
            flex-shrink: 0;
        }
        .sidebar-sub-item:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .sidebar-sub-item:hover::before { opacity: 1; }
        .sidebar-sub-item.active { color: var(--sidebar-accent); font-weight: 500; }
        .sidebar-sub-item.active::before { opacity: 1; }

        /* ── User footer ── */
        .sidebar-footer {
            flex-shrink: 0;
            padding: 10px;
            border-top: 1px solid var(--sidebar-divider);
            background: rgba(0,0,0,0.1);
        }
        .sidebar-user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 10px;
            border-radius: 10px;
            transition: background 0.15s;
            cursor: pointer;
        }
        .sidebar-user-card:hover { background: var(--sidebar-hover-bg); }
        .sidebar-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 600; color: #fff;
            flex-shrink: 0;
            border: 1.5px solid rgba(255,255,255,0.15);
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name  { font-size: 12.5px; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-email { font-size: 10.5px; color: rgba(255,255,255,0.45); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-logout-btn {
            width: 28px; height: 28px;
            border-radius: 7px;
            border: none;
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.45);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: all 0.15s;
            flex-shrink: 0;
        }
        .sidebar-logout-btn:hover { background: rgba(239,68,68,0.15); color: #f87171; }
        .sidebar-logout-btn svg { width: 14px; height: 14px; }

        /* ── Responsive show/hide labels ── */
        @media (max-width: 1279px) {
            .sidebar-nav-label,
            .sidebar-dropdown-label,
            .sidebar-logo-sub,
            .sidebar-user-info,
            .sidebar-logout-btn,
            .sidebar-nav-badge,
            .sidebar-chevron { display: none; }
        }

        /* ── Main gradient background ── */
        .app-main-bg {
            background-image:
                radial-gradient(circle at 20% 80%, rgba(255,220,190,0.25) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,245,238,0.3) 0%, transparent 50%);
            background-color: #f8fafc;
        }
    </style>
</head>

<body class="font-sans antialiased">

    <div x-data="{ drawerOpen: false }" @resize.window="drawerOpen = false" class="flex h-screen overflow-hidden">

        {{-- Backdrop --}}
        <div x-show="drawerOpen"
            x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="drawerOpen = false"
            class="fixed inset-0 z-40 bg-black/50 xl:hidden"></div>

        {{-- ===== SIDEBAR ===== --}}
        <aside id="app-sidebar"
            :class="drawerOpen ? 'w-64' : 'max-md:-translate-x-full md:w-14 xl:w-64'"
            class="fixed inset-y-0 left-0 z-50 flex flex-col
                   transition-all duration-300 ease-in-out w-64
                   md:relative md:translate-x-0 xl:w-64">

            {{-- Logo --}}
            <div class="sidebar-logo-row">
                <div class="sidebar-logo-icon">
                    <img src="/inaya_creation_logo.jpeg" alt="Logo">
                </div>
                <div :class="drawerOpen ? 'opacity-100' : 'opacity-0 xl:opacity-100'"
                     class="overflow-hidden whitespace-nowrap transition-opacity duration-200">
                    <div class="sidebar-logo-name">Inaya Creation</div>
                    <div class="sidebar-logo-sub">Inventory System</div>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="sidebar-nav">

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'" class="sidebar-section">Overview</p>

                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg></x-slot>
                    Dashboard
                </x-sidebar-link>

                @canany(['manage users', 'view users', 'manage roles', 'view roles', 'manage permissions', 'view permissions'])
                    <x-sidebar-dropdown label="Access Control" :active="request()->routeIs('users.*') || request()->routeIs('roles.*') || request()->routeIs('permissions.*')">
                        <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 12a5 5 0 100-10 5 5 0 000 10z"/><path d="M3 22a9 9 0 0118 0"/><path d="M17 11l2 2 4-4"/></svg></x-slot>
                        @canany(['manage users', 'view users'])<x-sidebar-sub-link :href="route('users.index')">Users</x-sidebar-sub-link>@endcanany
                        @canany(['manage roles', 'view roles'])<x-sidebar-sub-link :href="route('roles.index')">Roles</x-sidebar-sub-link>@endcanany
                        @canany(['manage permissions', 'view permissions'])<x-sidebar-sub-link :href="route('permissions.index')">Permissions</x-sidebar-sub-link>@endcanany
                    </x-sidebar-dropdown>
                @endcanany

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'" class="sidebar-section">Inventory</p>

                @canany(['manage customers', 'view customers', 'manage suppliers', 'view suppliers'])
                <x-sidebar-dropdown label="People" :active="request()->routeIs('customers.*') || request()->routeIs('suppliers.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></x-slot>
                    @canany(['manage customers', 'view customers'])<x-sidebar-sub-link :href="route('customers.index')">Customers</x-sidebar-sub-link>@endcanany
                    @canany(['manage suppliers', 'view suppliers'])<x-sidebar-sub-link :href="route('suppliers.index')">Suppliers</x-sidebar-sub-link>@endcanany
                </x-sidebar-dropdown>
                @endcanany

                @canany(['manage products', 'view products'])
                <x-sidebar-dropdown label="Products" :active="request()->routeIs('products.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></x-slot>
                    <x-sidebar-sub-link :href="route('products.index')">All Products</x-sidebar-sub-link>
                </x-sidebar-dropdown>
                @endcanany

                @canany(['manage cloth sewings', 'view cloth sewings'])
                <x-sidebar-dropdown label="Cloth Management" :active="request()->routeIs('cloth-sewings.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 7h16"/><path d="M6 7v11a2 2 0 002 2h8a2 2 0 002-2V7"/><path d="M9 7V4h6v3"/><path d="M9 12h6"/><path d="M9 16h4"/></svg></x-slot>
                    @canany(['manage cloth sewings', 'view cloth sewings'])<x-sidebar-sub-link :href="route('cloth-sewings.index')">Cloth Sewing</x-sidebar-sub-link>@endcanany
                </x-sidebar-dropdown>
                @endcanany

                @canany(['manage sales men', 'view sales men'])
                <x-sidebar-link :href="route('sales-men.index')" :active="request()->routeIs('sales-men.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="7" r="4"/><path d="M5 21a7 7 0 0114 0"/><path d="M18 8h4"/><path d="M20 6v4"/></svg></x-slot>
                    Sales Men
                </x-sidebar-link>
                @endcanany

                @canany(['manage shops', 'view shops', 'manage stock', 'view stock', 'distribute stock'])
                    <x-sidebar-dropdown label="Stock Management" :active="request()->routeIs('shops.*') || request()->routeIs('stocks.*')">
                        <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg></x-slot>
                        @canany(['manage shops', 'view shops'])<x-sidebar-sub-link :href="route('shops.index')">Shops</x-sidebar-sub-link>@endcanany
                        @canany(['manage stock', 'view stock'])<x-sidebar-sub-link :href="route('stocks.index')">Inventory</x-sidebar-sub-link>@endcanany
                        @can('distribute stock')<x-sidebar-sub-link :href="route('stocks.distribute')">Distribute Stock</x-sidebar-sub-link>@endcan
                    </x-sidebar-dropdown>
                @endcanany

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'" class="sidebar-section">Purchasing</p>

                @canany(['manage purchases', 'view purchases', 'manage purchase returns', 'view purchase returns'])
                <x-sidebar-dropdown label="Purchase Management" :active="request()->routeIs('purchases.*') || request()->routeIs('purchase-returns.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg></x-slot>
                    @canany(['manage purchases', 'view purchases'])<x-sidebar-sub-link :href="route('purchases.index')">Purchase List</x-sidebar-sub-link>@endcanany
                    @canany(['manage purchase returns', 'view purchase returns'])<x-sidebar-sub-link :href="route('purchase-returns.index')">Return Purchase</x-sidebar-sub-link>@endcanany
                </x-sidebar-dropdown>
                @endcanany

                @canany(['manage sales', 'view sales', 'manage sale returns', 'view sale returns'])
                <x-sidebar-dropdown label="Sales Management" :active="request()->routeIs('sales.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></x-slot>
                    @canany(['manage sales', 'view sales'])<x-sidebar-sub-link :href="route('sales.index')">Sales Orders</x-sidebar-sub-link>@endcanany
                    @canany(['manage sale returns', 'view sale returns'])<x-sidebar-sub-link :href="route('sale-returns.index')">Return Sales</x-sidebar-sub-link>@endcanany
                </x-sidebar-dropdown>
                @endcanany

                @canany(['manage expenses', 'view expenses', 'manage cash', 'view cash'])
                <x-sidebar-dropdown label="Accounts" :active="request()->routeIs('expenses.*') || request()->routeIs('cash-transactions.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></x-slot>
                    @canany(['manage expenses', 'view expenses'])<x-sidebar-sub-link :href="route('expenses.index')">Expenses</x-sidebar-sub-link>@endcanany
                    @canany(['manage cash', 'view cash'])<x-sidebar-sub-link :href="route('cash-transactions.index')">Cash Management</x-sidebar-sub-link>@endcanany
                </x-sidebar-dropdown>
                @endcanany

                @canany(['manage dues', 'view dues'])
                <x-sidebar-dropdown label="Due Management" :active="request()->routeIs('dues.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h7"/><path d="M15 16l2 2 4-4"/></svg></x-slot>
                    <x-sidebar-sub-link :href="route('dues.customer')" :active="request()->routeIs('dues.customer')">Customer Wise Due</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dues.supplier')" :active="request()->routeIs('dues.supplier')">Supplier Wise Due</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dues.sale')" :active="request()->routeIs('dues.sale')">Sale Wise Due</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dues.purchase')" :active="request()->routeIs('dues.purchase')">Purchase Wise Due</x-sidebar-sub-link>
                    <x-sidebar-sub-link :href="route('dues.manual')" :active="request()->routeIs('dues.manual') || request()->routeIs('dues.edit')">Manual Due</x-sidebar-sub-link>
                </x-sidebar-dropdown>
                @endcanany

                <p :class="drawerOpen ? 'block' : 'hidden xl:block'" class="sidebar-section">Reports & Settings</p>

                <x-sidebar-link :href="route('dashboard')" :active="false">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></x-slot>
                    Reports
                </x-sidebar-link>

                <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    <x-slot name="icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></x-slot>
                    Settings
                </x-sidebar-link>

            </nav>

            {{-- User footer --}}
            <div class="sidebar-footer">
                <div class="sidebar-user-card">
                    <div class="sidebar-avatar">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div :class="drawerOpen ? 'opacity-100 flex-1' : 'opacity-0 w-0 xl:opacity-100 xl:flex-1'"
                         class="sidebar-user-info overflow-hidden transition-all duration-200 min-w-0">
                        <div class="sidebar-user-name">{{ Auth::user()->name }}</div>
                        <div class="sidebar-user-email">{{ Auth::user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}"
                          :class="drawerOpen ? 'block' : 'hidden xl:block'" class="shrink-0">
                        @csrf
                        <button type="submit" class="sidebar-logout-btn" title="Log Out">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ===== MAIN AREA ===== --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            {{-- Top Header --}}
            <header class="flex items-center justify-between h-14 px-4 sm:px-5 bg-white border-b border-gray-200 shrink-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="drawerOpen = !drawerOpen"
                        class="p-1.5 -ml-1 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition xl:hidden"
                        aria-label="Toggle menu">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path x-show="!drawerOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="drawerOpen" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    @isset($header)
                        <h1 class="text-sm font-medium text-gray-800">{{ $header }}</h1>
                    @endisset
                </div>

                <div class="flex items-center gap-2">
                    <button class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white text-xs font-medium shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <span class="hidden sm:block text-sm text-gray-700 max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400 hidden sm:block" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <div x-show="open" @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"   x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Profile
                            </a>
                            <div class="my-1 border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-5 pb-20 md:pb-5 app-main-bg">
                {{ $slot }}
            </main>

            {{-- ===== BOTTOM NAV (mobile only) ===== --}}
            <nav class="md:hidden fixed bottom-0 inset-x-0 z-30 flex items-center justify-around h-14 bg-[#1a1360] border-t border-white/10">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('dashboard') ? 'text-violet-400' : 'text-white/40' }}">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span class="text-[9px] font-medium">Home</span>
                </a>
                <a href="{{ route('purchases.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('purchases.*') ? 'text-violet-400' : 'text-white/40' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span class="text-[9px] font-medium">Purchase</span>
                </a>
                <a href="{{ route('sales.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1 {{ request()->routeIs('sales.*') ? 'text-violet-400' : 'text-white/40' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="text-[9px] font-medium">Sales</span>
                </a>
                <button @click="drawerOpen = !drawerOpen" class="flex flex-col items-center gap-0.5 px-3 py-1 text-white/40">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M4 6h16M4 12h10"/></svg>
                    <span class="text-[9px] font-medium">More</span>
                </button>
            </nav>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.TomSelect) return;

            document.querySelectorAll('select[name*="customer_id"], select[name*="supplier_id"], select[name*="product_id"], select.tom-select').forEach((select) => {
                if (select.tomselect || select.dataset.noTomSelect === '1') return;

                new TomSelect(select, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 500,
                    plugins: select.multiple ? ['remove_button'] : [],
                    render: {
                        no_results: () => '<div class="no-results">No results found</div>',
                    },
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
