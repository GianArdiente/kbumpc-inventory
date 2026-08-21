<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>{{ config('app.name', 'KBUMPC Inventory') }}</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body class="min-h-screen text-slate-900">
    <x-toast />

    <div
        x-data="{ sidebarOpen: false }"
        @keydown.escape.window="sidebarOpen = false"
        class="min-h-screen"
    >
        {{-- Mobile / Tablet Backdrop --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            x-cloak
            class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm xl:hidden"
            @click="sidebarOpen = false"
        ></div>

        {{-- Sidebar --}}
        <aside
            class="kbumpc-sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col text-white shadow-2xl transition-transform duration-200 ease-out xl:translate-x-0 xl:shadow-none"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            {{-- Brand --}}
            <div class="border-b border-white/10 px-5 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white p-1 shadow-sm">
                        <img
                            src="{{ asset('images/kbumpc-logo.png') }}"
                            alt="KBUMPC Logo"
                            class="h-full w-full object-contain"
                        >
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.20em] text-emerald-300">
                            KBUMPC
                        </p>

                        <h1 class="mt-1 text-sm font-semibold leading-5 text-white">
                            Inventory Management
                        </h1>

                        <p class="text-xs text-slate-400">
                            Warehouse System
                        </p>
                    </div>

                    {{-- Drawer Close --}}
                    <button
                        type="button"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/10 text-xl text-slate-300 transition hover:bg-white/10 hover:text-white xl:hidden"
                        @click="sidebarOpen = false"
                        aria-label="Close sidebar"
                    >
                        ×
                    </button>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-4 py-5">
                <p class="mb-2 px-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                    Main Menu
                </p>

                <div class="space-y-1">
                    <a
                        href="{{ route('dashboard') }}"
                        @click="sidebarOpen = false"
                        class="kbumpc-nav-link
                            {{ request()->routeIs('dashboard')
                                ? 'kbumpc-nav-link-active'
                                : '' }}"
                    >
                        Dashboard
                    </a>

                    <a
                        href="{{ route('inventory.index') }}"
                        @click="sidebarOpen = false"
                        class="kbumpc-nav-link justify-between
                            {{ request()->routeIs('inventory.*')
                                ? 'kbumpc-nav-link-active'
                                : '' }}"
                    >
                        <span>
                            Inventory
                        </span>

                        @if (($sidebarNotifications['inventory'] ?? 0) > 0)
                            <span
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white shadow-sm"
                            >
                                {{ $sidebarNotifications['inventory'] > 99
                                    ? '99+'
                                    : $sidebarNotifications['inventory'] }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('requests.index') }}"
                        @click="sidebarOpen = false"
                        class="kbumpc-nav-link justify-between
                            {{ request()->routeIs('requests.*')
                                ? 'kbumpc-nav-link-active'
                                : '' }}"
                    >
                        <span>
                            Requests
                        </span>

                        @if (($sidebarNotifications['requests'] ?? 0) > 0)
                            <span
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white shadow-sm"
                            >
                                {{ $sidebarNotifications['requests'] > 99
                                        ? '99+'
                                    : $sidebarNotifications['requests'] }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('returns.index') }}"
                        @click="sidebarOpen = false"
                        class="kbumpc-nav-link justify-between
                            {{ request()->routeIs('returns.*')
                                ? 'kbumpc-nav-link-active'
                                    : '' }}"
                    >
                        <span>
                            Returns
                       </span>

                        @if (($sidebarNotifications['returns'] ?? 0) > 0)
                            <span
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white shadow-sm"
                            >
                                {{ $sidebarNotifications['returns'] > 99
                                    ? '99+'
                                    : $sidebarNotifications['returns'] }}
                            </span>
                        @endif
                    </a>

                    <a
                        href="{{ route('reports.index') }}"
                        @click="sidebarOpen = false"
                        class="kbumpc-nav-link
                            {{ request()->routeIs('reports.*')
                                ? 'kbumpc-nav-link-active'
                                : '' }}"
                    >
                        Reports
                    </a>
                </div>

                @if (
                    auth()->user()->role === 'admin'
                    && auth()->user()->status === 'active'
                )
                    <div class="my-5 border-t border-white/10"></div>

                    <p class="mb-2 px-4 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                        Administration
                    </p>

                    <div class="space-y-1">
                        <a
                            href="{{ route('activity-logs.index') }}"
                            @click="sidebarOpen = false"
                            class="kbumpc-nav-link justify-between
                                {{ request()->routeIs('activity-logs.*')
                                    ? 'kbumpc-nav-link-active'
                                    : '' }}"
                        >
                            <span>
                                Activity Logs
                            </span>

                            @if (($sidebarNotifications['activity_logs'] ?? 0) > 0)
                                <span
                                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white shadow-sm"
                                >
                                    {{ $sidebarNotifications['activity_logs'] > 99
                                        ? '99+'
                                        : $sidebarNotifications['activity_logs'] }}
                                </span>
                            @endif
                        </a>

                        <a
                            href="{{ route('users.index') }}"
                            @click="sidebarOpen = false"
                            class="kbumpc-nav-link justify-between
                                {{ request()->routeIs('users.*')
                                    ? 'kbumpc-nav-link-active'
                                    : '' }}"
                        >
                            <span>
                                Users
                            </span>

                            @if (($sidebarNotifications['users'] ?? 0) > 0)
                                <span
                                    class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[11px] font-bold leading-none text-white shadow-sm"
                                >
                                    {{ $sidebarNotifications['users'] > 99
                                        ? '99+'
                                        : $sidebarNotifications['users'] }}
                                </span>
                            @endif
                        </a>
                    </div>
                @endif
            </nav>

            {{-- Sidebar Footer --}}
            @php
                $licenseService = app(\App\Services\LicenseService::class);
                $licenseValid = $licenseService->isValid();
            @endphp

            <div class="border-t border-white/10 px-5 py-4">
                <div class="flex items-center gap-2">
                    <span
                        class="h-2 w-2 rounded-full
                            {{ $licenseValid
                                ? 'bg-emerald-400'
                                : 'bg-rose-400' }}"
                    ></span>

                    <p class="text-xs font-medium text-slate-300">
                        {{ $licenseValid
                            ? 'Licensed to KBUMPC'
                            : 'License Invalid' }}
                    </p>
                </div>

                <p class="mt-1 pl-4 text-[11px] text-slate-500">
                    {{ config(
                        'product.license.license_id',
                        'No License ID'
                    ) }}
                </p>
            </div>
        </aside>

        {{-- Application Area --}}
        <div class="min-w-0 xl:pl-72">

            {{-- Header --}}
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 shadow-sm backdrop-blur">
                <div class="flex min-h-[72px] items-center justify-between gap-3 px-3 py-3 sm:px-5 lg:px-6 xl:px-8">

                    {{-- Left Header --}}
                    <div class="flex min-w-0 items-center gap-3 sm:gap-4">
                        <button
                            type="button"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg text-slate-600 shadow-sm transition hover:bg-slate-50 xl:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Open sidebar"
                        >
                            ☰
                        </button>

                        <div class="min-w-0">
                            <div class="hidden items-center gap-2 sm:flex">
                                <span class="h-2 w-2 shrink-0 rounded-full bg-emerald-600"></span>

                                <p class="truncate text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400 sm:text-xs sm:tracking-[0.18em]">
                                    KBUMPC Warehouse Operations
                                </p>
                            </div>

                            <h2 class="truncate text-lg font-semibold tracking-tight text-slate-950 sm:mt-1 sm:text-xl">
                                @yield('page-title', 'Dashboard')
                            </h2>
                        </div>
                    </div>

                    {{-- Account Dropdown --}}
                    <div
                        x-data="{ accountOpen: false }"
                        class="relative shrink-0"
                    >
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white p-1.5 text-left shadow-sm transition hover:border-slate-300 hover:bg-slate-50 sm:gap-3 sm:px-3 sm:py-2"
                            @click="accountOpen = ! accountOpen"
                            @click.outside="accountOpen = false"
                        >
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-800">
                                {{ strtoupper(
                                    substr(
                                        auth()->user()->name,
                                        0,
                                        1
                                    )
                                ) }}
                            </div>

                            <div class="hidden min-w-0 md:block">
                                <p class="max-w-40 truncate text-sm font-semibold text-slate-900">
                                    {{ auth()->user()->name }}
                                </p>

                                <p class="text-xs capitalize text-slate-500">
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        auth()->user()->role
                                    ) }}
                                </p>
                            </div>

                            <svg
                                class="hidden h-4 w-4 text-slate-400 transition md:block"
                                :class="{ 'rotate-180': accountOpen }"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.51a.75.75 0 0 1-1.08 0l-4.25-4.51a.75.75 0 0 1 .02-1.06Z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <div
                            x-show="accountOpen"
                            x-transition.origin.top.right
                            x-cloak
                            class="absolute right-0 z-50 mt-2 w-[min(18rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                        >
                            <div class="border-b border-slate-100 px-4 py-4">
                                <p class="truncate text-sm font-semibold text-slate-900">
                                    {{ auth()->user()->name }}
                                </p>

                                <p class="mt-1 truncate text-xs text-slate-500">
                                    {{ auth()->user()->email }}
                                </p>

                                <span class="mt-2 inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold capitalize text-emerald-700">
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        auth()->user()->role
                                    ) }}
                                </span>
                            </div>

                            <div class="p-2">
                                <a
                                    href="{{ route('profile.edit') }}"
                                    class="flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950"
                                >
                                    My Profile
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route('logout') }}"
                                    class="mt-1"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700"
                                    >
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="min-w-0 overflow-x-hidden px-3 py-4 sm:px-5 sm:py-5 lg:px-6 lg:py-6 xl:px-8">
                <div class="mx-auto min-w-0 max-w-[1800px]">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>