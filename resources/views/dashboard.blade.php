@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6 kbumpc-page-enter">

        {{-- Welcome / Overview --}}
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="relative overflow-hidden px-6 py-6 sm:px-8">
                <div
                    class="pointer-events-none absolute inset-y-0 right-0 w-72 opacity-[0.06]"
                    style="
                        background:
                            radial-gradient(
                                circle at center,
                                #075f2f,
                                transparent 70%
                            );
                    "
                ></div>

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                Warehouse Overview
                            </p>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            Welcome back, {{ auth()->user()->name }}
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Current inventory position, request activity,
                            and warehouse alerts.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('requests.create') }}"
                            class="kbumpc-btn-primary"
                        >
                            Create Request
                        </a>

                        <a
                            href="{{ route('inventory.index') }}"
                            class="kbumpc-btn-outline"
                        >
                            View Inventory
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Inventory Statistics --}}
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Active Items --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">
                                Active Items
                            </p>

                            <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                                {{ number_format(
                                    $statistics['total_items']
                                ) }}
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">
                            AI
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-slate-500">
                        Active inventory records
                    </p>
                </div>

                {{-- Free Units --}}
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-emerald-800">
                                Free Units
                            </p>

                            <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-950">
                                {{ number_format(
                                    $statistics['free_units']
                                ) }}
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm font-bold text-emerald-700">
                            FU
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-emerald-700">
                        Available for new requests
                    </p>
                </div>

                {{-- Reserved Units --}}
                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-blue-800">
                                Reserved Units
                            </p>

                            <p class="mt-3 text-3xl font-semibold tracking-tight text-blue-950">
                                {{ number_format(
                                    $statistics['reserved_units']
                                ) }}
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm font-bold text-blue-700">
                            RU
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-blue-700">
                        Committed to approved requests
                    </p>
                </div>

                {{-- Physical Units --}}
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-amber-800">
                                Physical Units
                            </p>

                            <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-950">
                                {{ number_format(
                                    $statistics['physical_units']
                                ) }}
                            </p>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm font-bold text-amber-700">
                            PU
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-amber-700">
                        Currently inside the warehouse
                    </p>
                </div>
            </div>
        </section>

        {{-- Main Dashboard Content --}}
        <section class="grid gap-6 xl:grid-cols-3">

            {{-- Recent Requests --}}
            <div class="xl:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-slate-950">
                                Recent Requests
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Latest warehouse requests and their current workflow status
                            </p>
                        </div>

                        <a
                            href="{{ route('requests.index') }}"
                            class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900"
                        >
                            View All
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Request
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Foreman
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Status
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Encoded By
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($recentRequests as $request)
                                    @php
                                        $statusClasses = match (
                                            $request->status
                                        ) {
                                            'pending' =>
                                                'bg-amber-100 text-amber-800',

                                            'approved' =>
                                                'bg-blue-100 text-blue-800',

                                            'mobilized' =>
                                                'bg-sky-100 text-sky-800',

                                            'in_use' =>
                                                'bg-violet-100 text-violet-800',

                                            'completed' =>
                                                'bg-emerald-100 text-emerald-800',

                                            default =>
                                                'bg-slate-100 text-slate-700',
                                        };
                                    @endphp

                                    <tr class="transition hover:bg-slate-50/80">
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <a
                                                href="{{ route(
                                                    'requests.show',
                                                    $request
                                                ) }}"
                                                class="text-sm font-semibold text-slate-900 transition hover:text-emerald-700"
                                            >
                                                {{ $request->request_number }}
                                            </a>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                            {{ $request->foreman_name }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm">
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $statusClasses }}"
                                            >
                                                {{ str_replace(
                                                    '_',
                                                    ' ',
                                                    $request->status
                                                ) }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                            {{ $request->encoder?->name ?? 'Unknown' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="4"
                                            class="px-5 py-10 text-center text-sm text-slate-500"
                                        >
                                            No requests available yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">

                {{-- Warehouse Alerts --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <h3 class="font-semibold text-slate-950">
                            Warehouse Alerts
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Items and requests requiring attention
                        </p>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3">
                            <span class="text-sm font-medium text-amber-900">
                                Pending Requests
                            </span>

                            <span class="text-lg font-semibold text-amber-950">
                                {{ number_format(
                                    $statistics['pending_requests']
                                ) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-blue-50 px-4 py-3">
                            <span class="text-sm font-medium text-blue-900">
                                Fully Reserved Items
                            </span>

                            <span class="text-lg font-semibold text-blue-950">
                                {{ number_format(
                                    $statistics['fully_reserved_items']
                                ) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3">
                            <span class="text-sm font-medium text-amber-900">
                                Low-Stock Items
                            </span>

                            <span class="text-lg font-semibold text-amber-950">
                                {{ number_format(
                                    $statistics['low_stock_items']
                                ) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-rose-50 px-4 py-3">
                            <span class="text-sm font-medium text-rose-900">
                                Damaged Items
                            </span>

                            <span class="text-lg font-semibold text-rose-950">
                                {{ number_format(
                                    $statistics['damaged_items']
                                ) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-slate-100 px-4 py-3">
                            <span class="text-sm font-medium text-slate-700">
                                Missing Items
                            </span>

                            <span class="text-lg font-semibold text-slate-950">
                                {{ number_format(
                                    $statistics['missing_items']
                                ) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Request Workflow --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <h3 class="font-semibold text-slate-950">
                            Request Workflow
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Current request processing status
                        </p>
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                            <span class="text-sm text-slate-600">
                                Approved / Reserved
                            </span>

                            <span class="font-semibold text-blue-700">
                                {{ number_format(
                                    $statistics['approved_requests']
                                ) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                            <span class="text-sm text-slate-600">
                                Active Workflow
                            </span>

                            <span class="font-semibold text-sky-700">
                                {{ number_format(
                                    $statistics['active_requests']
                                ) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                            <span class="text-sm text-slate-600">
                                Completed
                            </span>

                            <span class="font-semibold text-emerald-700">
                                {{ number_format(
                                    $statistics['completed_requests']
                                ) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div>
                        <h3 class="font-semibold text-slate-950">
                            Quick Actions
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Common warehouse operations
                        </p>
                    </div>

                    <div class="mt-5 space-y-3">
                        <a
                            href="{{ route('requests.create') }}"
                            class="kbumpc-btn-primary flex w-full"
                        >
                            Create Request
                        </a>

                        @if (auth()->user()->role === 'admin')
                            <a
                                href="{{ route('inventory.create') }}"
                                class="kbumpc-btn-secondary flex w-full"
                            >
                                Add Inventory Item
                            </a>
                        @endif

                        <a
                            href="{{ route('inventory.index') }}"
                            class="kbumpc-btn-outline flex w-full"
                        >
                            View Inventory
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection