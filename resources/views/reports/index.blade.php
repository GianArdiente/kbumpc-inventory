@extends('layouts.app')

@section('page-title', 'Reports')

@section('content')
    <div class="space-y-6">
        {{-- Header + Date Range --}}
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-slate-950">
                    Reports
                </h3>

                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    Review warehouse stock, request activity, material usage,
                    releases, returns, losses, and inventory values for a selected period.
                </p>
            </div>

        <div class="flex flex-col gap-3 xl:items-end">
            <form
                method="GET"
                action="{{ route('reports.index') }}"
                class="grid gap-3 sm:grid-cols-3"
            >
                <div>
                    <label
                        for="date_from"
                        class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        From
                    </label>

                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        value="{{ $dateFrom }}"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label
                        for="date_to"
                        class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                    >
                        To
                    </label>

                    <input
                        id="date_to"
                        name="date_to"
                        type="date"
                        value="{{ $dateTo }}"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Apply Range
                    </button>
                </div>
            </form>

            <a
                href="{{ route('reports.export', [
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ]) }}"
                class="inline-flex items-center justify-center rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100"
            >
                Export Excel
            </a>
        </div>
    </div>      
    
        {{-- Current Stock Summary --}}
        <section>
            <div class="mb-4">
                <h3 class="font-semibold text-slate-950">
                    Current Warehouse Position
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Current inventory position as of now. These values are not limited by the selected reporting period.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Active Items
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format($inventorySummary['active_items']) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Physical Units
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-slate-950">
                        {{ number_format($inventorySummary['physical_units']) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-indigo-800">
                        Reserved Units
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-indigo-950">
                        {{ number_format($inventorySummary['reserved_units']) }}
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-800">
                        Free Units
                    </p>

                    <p class="mt-3 text-3xl font-semibold text-emerald-950">
                        {{ number_format($inventorySummary['free_units']) }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Stock Alerts --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-800">
                    Low-Stock Items
                </p>

                <p class="mt-3 text-2xl font-semibold text-amber-950">
                    {{ number_format($inventorySummary['low_stock_items']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-rose-800">
                    Out-of-Stock Items
                </p>

                <p class="mt-3 text-2xl font-semibold text-rose-950">
                    {{ number_format($inventorySummary['out_of_stock_items']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-800">
                    Fully Reserved
                </p>

                <p class="mt-3 text-2xl font-semibold text-indigo-950">
                    {{ number_format($inventorySummary['fully_reserved_items']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-orange-200 bg-orange-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-orange-800">
                    Overcommitted
                </p>

                <p class="mt-3 text-2xl font-semibold text-orange-950">
                    {{ number_format($inventorySummary['overcommitted_items']) }}
                </p>
            </div>
        </section>

        {{-- Current Inventory Valuation --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <h3 class="font-semibold text-slate-950">
                    Current Inventory Valuation
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Estimated using each item's current reference unit cost.
                </p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Overall Inventory Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-slate-950">
                        ₱{{ number_format(
                            $inventorySummary['overall_inventory_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Total recorded company-owned inventory value
                    </p>
                </div>

                <div class="rounded-xl bg-sky-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">
                        Physical Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-sky-950">
                        ₱{{ number_format(
                            $inventorySummary['physical_inventory_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-sky-700">
                        Value currently inside the warehouse
                    </p>
                </div>

                <div class="rounded-xl bg-indigo-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                        Reserved Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-indigo-950">
                        ₱{{ number_format(
                            $inventorySummary['reserved_inventory_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-indigo-700">
                        Value committed to approved requests
                    </p>
                </div>

                <div class="rounded-xl bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                        Free Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-emerald-950">
                        ₱{{ number_format(
                            $inventorySummary['free_inventory_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-emerald-700">
                        Unreserved stock value
                    </p>
                </div>
            </div>

            @if ($inventorySummary['items_without_cost'] > 0)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <p class="text-sm font-semibold text-amber-900">
                        {{ number_format($inventorySummary['items_without_cost']) }}
                        active item(s) do not have a reference unit cost yet.
                    </p>

                    <p class="mt-1 text-sm text-amber-800">
                        Current warehouse valuation may therefore be incomplete.
                    </p>
                </div>
            @endif
        </section>

        {{-- Request + Unit Movement Summary --}}
        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="font-semibold text-slate-950">
                        Request Summary
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Current request workload across the warehouse.
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3">
                        <span class="text-sm font-medium text-amber-900">
                            Pending
                        </span>

                        <span class="text-lg font-semibold text-amber-950">
                            {{ number_format($requestSummary['pending']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-indigo-50 px-4 py-3">
                        <span class="text-sm font-medium text-indigo-900">
                            Approved
                        </span>

                        <span class="text-lg font-semibold text-indigo-950">
                            {{ number_format($requestSummary['approved']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-sky-50 px-4 py-3">
                        <span class="text-sm font-medium text-sky-900">
                            Mobilized
                        </span>

                        <span class="text-lg font-semibold text-sky-950">
                            {{ number_format($requestSummary['mobilized']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-violet-50 px-4 py-3">
                        <span class="text-sm font-medium text-violet-900">
                            In Use
                        </span>

                        <span class="text-lg font-semibold text-violet-950">
                            {{ number_format($requestSummary['in_use']) }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 sm:col-span-2">
                        <span class="text-sm font-medium text-emerald-900">
                            Completed
                        </span>

                        <span class="text-lg font-semibold text-emerald-950">
                            {{ number_format($requestSummary['completed']) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div>
                    <h3 class="font-semibold text-slate-950">
                        Movement Summary
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Unit movement recorded between {{ $dateFrom }} and {{ $dateTo }}.
                    </p>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-xl bg-sky-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">
                            Released Units
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-sky-950">
                            {{ number_format($usageSummary['released_units']) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-emerald-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                            Good Returns
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-emerald-950">
                            {{ number_format($usageSummary['good_returns']) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-amber-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                            Damaged
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-amber-950">
                            {{ number_format($usageSummary['damaged_units']) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-rose-50 px-4 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">
                            Missing
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-rose-950">
                            {{ number_format($usageSummary['missing_units']) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Cost & Loss Summary --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <h3 class="font-semibold text-slate-950">
                    Cost & Loss Summary
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Historical transaction values recorded between
                    {{ $dateFrom }} and {{ $dateTo }}.
                </p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-sky-200 bg-sky-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">
                        Materials Consumed
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-sky-950">
                        ₱{{ number_format(
                            $usageSummary['materials_consumed_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-sky-700">
                        Consumable materials released during the period
                    </p>
                </div>

                <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">
                        Tools Deployed
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-indigo-950">
                        ₱{{ number_format(
                            $usageSummary['tools_deployed_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-indigo-700">
                        Reusable asset value deployed — not an expense
                    </p>
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                        Good Return Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-emerald-950">
                        ₱{{ number_format(
                            $usageSummary['good_return_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-emerald-700">
                        Reusable asset value returned in good condition
                    </p>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                        Damaged Loss
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-amber-950">
                        ₱{{ number_format(
                            $usageSummary['damaged_loss_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-amber-700">
                        Value of damaged reusable inventory
                    </p>
                </div>

                <div class="rounded-xl border border-rose-200 bg-rose-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">
                        Missing Loss
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-rose-950">
                        ₱{{ number_format(
                            $usageSummary['missing_loss_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-rose-700">
                        Value of inventory reported missing
                    </p>
                </div>

                <div class="rounded-xl border border-slate-300 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Stock Received Value
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-slate-950">
                        ₱{{ number_format(
                            $usageSummary['stock_received_value'],
                            2
                        ) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Value of normal Stock In movements during the period
                    </p>
                </div>
            </div>

            <div class="mt-5 rounded-xl border border-slate-950 bg-slate-950 p-5 text-white">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold">
                            Total Recorded Cost / Loss
                        </p>

                        <p class="mt-1 text-xs text-slate-300">
                            Materials consumed + damaged loss + missing loss.
                            Deployed tools and good returns are excluded.
                        </p>
                    </div>

                    <p class="text-3xl font-semibold">
                        ₱{{ number_format(
                            $usageSummary['total_recorded_cost'],
                            2
                        ) }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Cost Coverage Warning --}}
        @if ($costCoverage['transactions_without_cost'] > 0)
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-semibold text-amber-950">
                            Historical Cost Coverage
                        </h3>

                        <p class="mt-1 text-sm text-amber-800">
                            Some older transactions were created before historical cost tracking was introduced.
                            Their values are intentionally left unknown instead of being estimated.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <div class="rounded-xl bg-white/70 px-4 py-3 text-center">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                                With Cost
                            </p>

                            <p class="mt-1 text-xl font-semibold text-emerald-950">
                                {{ number_format($costCoverage['transactions_with_cost']) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-white/70 px-4 py-3 text-center">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                                Without Cost
                            </p>

                            <p class="mt-1 text-xl font-semibold text-amber-950">
                                {{ number_format($costCoverage['transactions_without_cost']) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- Stock Movements --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Stock Movements
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Most recent inventory transactions in the selected period,
                    including historical transaction values when available.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Date
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Item
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Type
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Unit Cost
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Transaction Value
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Overall Change
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Physical Change
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Recorded By
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($recentTransactions as $transaction)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $transaction->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $transaction->item?->name ?? 'Unknown Item' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $transaction->item?->item_code ?? '—' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm capitalize text-slate-700">
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        $transaction->type
                                    ) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                    @if ($transaction->unit_cost_snapshot !== null)
                                        <span class="font-medium text-slate-800">
                                            ₱{{ number_format(
                                                (float) $transaction->unit_cost_snapshot,
                                                2
                                            ) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                    @if ($transaction->total_cost !== null)
                                        <span class="font-semibold text-slate-950">
                                            ₱{{ number_format(
                                                abs((float) $transaction->total_cost),
                                                2
                                            ) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                    {{ $transaction->overall_stock_change > 0 ? '+' : '' }}
                                    {{ $transaction->overall_stock_change }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                    {{ $transaction->available_quantity_change > 0 ? '+' : '' }}
                                    {{ $transaction->available_quantity_change }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $transaction->user?->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="px-5 py-12 text-center text-sm text-slate-500"
                                >
                                    No inventory transactions found for this date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Completed Requests --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Completed Requests
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Requests completed within the selected reporting period.
                </p>
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
                                Project
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Work Area
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Completed
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($recentCompletedRequests as $inventoryRequest)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <a
                                        href="{{ route(
                                            'requests.show',
                                            $inventoryRequest
                                        ) }}"
                                        class="font-semibold text-slate-900 hover:text-indigo-700"
                                    >
                                        {{ $inventoryRequest->request_number }}
                                    </a>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->foreman_name }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->project_name }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->work_area }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->completed_at?->format(
                                        'M d, Y h:i A'
                                    ) ?? 'Not recorded' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-5 py-12 text-center text-sm text-slate-500"
                                >
                                    No completed requests found for this date range.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection