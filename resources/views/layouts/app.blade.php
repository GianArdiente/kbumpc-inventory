<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'KBUMPC Inventory') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <x-toast />

    <div
        x-data="{ sidebarOpen: false }"
        class="min-h-screen"
    >
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-slate-950 text-white transition-transform duration-200 lg:translate-x-0"
            :class="{ 'translate-x-0': sidebarOpen }"
        >
            <div class="flex h-20 items-center border-b border-slate-800 px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-400">
                        KBUMPC
                    </p>
                    <h1 class="mt-1 text-lg font-semibold">
                        Inventory System
                    </h1>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
                <a
                    href="{{ route('dashboard') }}"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium transition
                        {{ request()->routeIs('dashboard')
                            ? 'bg-white text-slate-950'
                            : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}"
                >
                    Dashboard
                </a>

                <a
                    href="{{ route('inventory.index') }}"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium transition
                        {{ request()->routeIs('inventory.*')
                            ? 'bg-white text-slate-950'
                            : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}"
                >
                    Inventory
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-900 hover:text-white"
                >
                    Requests
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-900 hover:text-white"
                >
                    Returns
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-900 hover:text-white"
                >
                    Reports
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-900 hover:text-white"
                >
                    Activity Logs
                </a>

                @if (auth()->user()->role === 'admin')
                    <a
                        href="#"
                        class="flex items-center rounded-lg px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-slate-900 hover:text-white"
                    >
                        Users
                    </a>
                @endif
            </nav>

            <div class="border-t border-slate-800 p-4">
                <div class="rounded-xl bg-slate-900 p-4">
                    <p class="text-sm font-semibold">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="mt-1 text-xs capitalize text-slate-400">
                        {{ str_replace('_', ' ', auth()->user()->role) }}
                    </p>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="mt-4"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-lg border border-slate-700 px-3 py-2 text-left text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white"
                        >
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 p-2 text-slate-600 lg:hidden"
                            @click="sidebarOpen = true"
                        >
                            <span class="sr-only">Open sidebar</span>
                            ☰
                        </button>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                Warehouse Operations
                            </p>

                            <h2 class="mt-1 text-xl font-semibold text-slate-950">
                                @yield('page-title', 'Dashboard')
                            </h2>
                        </div>
                    </div>

                    <a
                        href="{{ route('profile.edit') }}"
                        class="hidden rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 sm:block"
                    >
                        Profile
                    </a>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>