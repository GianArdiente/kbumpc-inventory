@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <section>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Total Items
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format($statistics['total_items']) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Available Stock
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format($statistics['available_stock']) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Pending Requests
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format($statistics['pending_requests']) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Active Requests
                    </p>
                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format($statistics['active_requests']) }}
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
                                Latest warehouse requests encoded in the system
                            </p>
                        </div>
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
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-slate-900">
                                            {{ $request->request_number }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                            {{ $request->foreman_name }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-700">
                                                {{ str_replace('_', ' ', $request->status) }}
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
                        Inventory Alerts
                    </h3>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3">
                            <span class="text-sm font-medium text-amber-900">
                                Low-stock items
                            </span>
                            <span class="text-lg font-semibold text-amber-950">
                                {{ number_format($statistics['low_stock_items']) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-rose-50 px-4 py-3">
                            <span class="text-sm font-medium text-rose-900">
                                Damaged items
                            </span>
                            <span class="text-lg font-semibold text-rose-950">
                                {{ number_format($statistics['damaged_items']) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-slate-100 px-4 py-3">
                            <span class="text-sm font-medium text-slate-700">
                                Missing items
                            </span>
                            <span class="text-lg font-semibold text-slate-950">
                                {{ number_format($statistics['missing_items']) }}
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
                            href="#"
                            class="block rounded-xl bg-slate-950 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Create Request
                        </a>

                        <a
                            href="#"
                            class="block rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Add Inventory Item
                        </a>

                        <a
                            href="#"
                            class="block rounded-xl border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            View Reports
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection