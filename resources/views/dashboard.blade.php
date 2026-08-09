@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Active Items
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format(
                            $statistics['total_items']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Active inventory records
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-800">
                        Free Units
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-emerald-950">
                        {{ number_format(
                            $statistics['free_units']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-emerald-700">
                        Available for new requests
                    </p>
                </div>

                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-indigo-800">
                        Reserved Units
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-indigo-950">
                        {{ number_format(
                            $statistics['reserved_units']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-indigo-700">
                        Committed to approved requests
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Physical Units
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format(
                            $statistics['physical_units']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Currently inside the warehouse
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
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
                            class="text-sm font-semibold text-slate-600 hover:text-slate-950"
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
                                                'bg-indigo-100 text-indigo-800',

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

                                    <tr class="hover:bg-slate-50/80">
                                        <td class="whitespace-nowrap px-5 py-4">
                                            <a
                                                href="{{ route(
                                                    'requests.show',
                                                    $request
                                                ) }}"
                                                class="text-sm font-semibold text-slate-900 hover:text-indigo-700"
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

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-slate-950">
                        Warehouse Alerts
                    </h3>

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

                        <div class="flex items-center justify-between rounded-xl bg-indigo-50 px-4 py-3">
                            <span class="text-sm font-medium text-indigo-900">
                                Fully Reserved Items
                            </span>

                            <span class="text-lg font-semibold text-indigo-950">
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

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-slate-950">
                        Request Workflow
                    </h3>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                            <span class="text-sm text-slate-600">
                                Approved / Reserved
                            </span>

                            <span class="font-semibold text-indigo-700">
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

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-semibold text-slate-950">
                        Quick Actions
                    </h3>

                    <div class="mt-5 space-y-3">
                        <a
                            href="{{ route('requests.create') }}"
                            class="block rounded-xl bg-slate-950 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Create Request
                        </a>

                        @if (auth()->user()->role === 'admin')
                            <a
                                href="{{ route('inventory.create') }}"
                                class="block rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                            >
                                Add Inventory Item
                            </a>
                        @endif

                        <a
                            href="{{ route('inventory.index') }}"
                            class="block rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            View Inventory
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection