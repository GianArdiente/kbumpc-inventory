@extends('layouts.app')

@section('page-title', 'Inventory Item')

@section('content')
    @php
        $currentUnitCost = $item->unit_cost !== null
            ? (float) $item->unit_cost
            : null;

        $currentOverallValue = $currentUnitCost !== null
            ? $currentUnitCost * $stockSnapshot['overall']
            : null;
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a
                    href="{{ route('inventory.index') }}"
                    class="text-sm font-semibold text-slate-500 hover:text-slate-800"
                >
                    ← Back to Inventory
                </a>

                <h3 class="mt-3 text-2xl font-semibold text-slate-950">
                    {{ $item->name }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $item->item_code }}
                </p>
            </div>

            @if (auth()->user()->role === 'admin')
                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('inventory.edit', $item) }}"
                        class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-white"
                    >
                        Edit Item
                    </a>

                    <a
                        href="{{ route('inventory.adjust', $item) }}"
                        class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Adjust Stock
                    </a>

                    <form
                        method="POST"
                        action="{{ route('inventory.archive', $item) }}"
                        onsubmit="return confirm('Archive {{ $item->name }}? This item will be hidden from the active inventory list.');"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="rounded-xl border border-rose-300 px-5 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50"
                        >
                            Archive Item
                        </button>
                    </form>
                </div>
            @endif
        </div>

        @if ($errors->has('archive'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first('archive') }}
            </div>
        @endif

        {{-- Stock Summary --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Overall Stock
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($stockSnapshot['overall']) }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    Total quantity owned or recorded
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Physical in Warehouse
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($stockSnapshot['physical']) }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    {{ ucfirst($item->unit) }} currently inside the warehouse
                </p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-800">
                    Reserved
                </p>

                <p class="mt-3 text-3xl font-semibold text-indigo-950">
                    {{ number_format($stockSnapshot['reserved']) }}
                </p>

                <p class="mt-2 text-xs text-indigo-700">
                    Committed to approved requests
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">
                    Free to Request
                </p>

                <p class="mt-3 text-3xl font-semibold text-emerald-950">
                    {{ number_format($stockSnapshot['free']) }}
                </p>

                <p class="mt-2 text-xs text-emerald-700">
                    Unreserved physical stock
                </p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-sky-800">
                    Deployed / In Use
                </p>

                <p class="mt-3 text-3xl font-semibold text-sky-950">
                    {{ number_format($stockSnapshot['in_use']) }}
                </p>

                <p class="mt-2 text-xs text-sky-700">
                    Reusable items currently outside the warehouse
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Stock Status
                </p>

                <div class="mt-4">
                    @switch($stockSnapshot['status'])
                        @case('archived')
                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">
                                Archived
                            </span>
                            @break

                        @case('overcommitted')
                            <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">
                                Overcommitted
                            </span>
                            @break

                        @case('out_of_stock')
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">
                                Out of Stock
                            </span>
                            @break

                        @case('fully_reserved')
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                                Fully Reserved
                            </span>
                            @break

                        @case('partially_reserved')
                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                                Partially Reserved
                            </span>
                            @break

                        @case('low_stock')
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                Low Stock
                            </span>
                            @break

                        @default
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                Available
                            </span>
                    @endswitch
                </div>
            </div>
        </section>

        {{-- Financial Summary --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div>
                <h3 class="font-semibold text-slate-950">
                    Cost & Inventory Value
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Current reference cost and estimated value of recorded stock.
                </p>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Current Unit Cost
                    </p>

                    @if ($currentUnitCost !== null)
                        <p class="mt-2 text-2xl font-semibold text-slate-950">
                            ₱{{ number_format($currentUnitCost, 2) }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Per {{ strtolower($item->unit) }}
                        </p>
                    @else
                        <p class="mt-2 text-lg font-semibold text-slate-400">
                            Not encoded
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Add a reference cost through Edit Item.
                        </p>
                    @endif
                </div>

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                        Current Overall Value
                    </p>

                    @if ($currentOverallValue !== null)
                        <p class="mt-2 text-2xl font-semibold text-emerald-950">
                            ₱{{ number_format($currentOverallValue, 2) }}
                        </p>

                        <p class="mt-1 text-xs text-emerald-700">
                            {{ number_format($stockSnapshot['overall']) }}
                            {{ $item->unit }}
                            ×
                            ₱{{ number_format($currentUnitCost, 2) }}
                        </p>
                    @else
                        <p class="mt-2 text-lg font-semibold text-emerald-800/50">
                            —
                        </p>

                        <p class="mt-1 text-xs text-emerald-700">
                            Cannot calculate until a unit cost is encoded.
                        </p>
                    @endif
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <p class="text-xs leading-5 text-amber-800">
                    Current overall value uses the item's current reference cost.
                    Historical transaction values below use the cost recorded at the
                    time of each transaction when available.
                </p>
            </div>
        </section>

        {{-- Stock Breakdown --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-950">
                        Stock Breakdown
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        A quick explanation of where the recorded stock currently is.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-xl bg-slate-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Physical
                        </p>

                        <p class="mt-1 text-lg font-semibold text-slate-950">
                            {{ number_format($stockSnapshot['physical']) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-indigo-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                            Reserved
                        </p>

                        <p class="mt-1 text-lg font-semibold text-indigo-950">
                            {{ number_format($stockSnapshot['reserved']) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-emerald-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                            Free
                        </p>

                        <p class="mt-1 text-lg font-semibold text-emerald-950">
                            {{ number_format($stockSnapshot['free']) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-sky-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">
                            Deployed
                        </p>

                        <p class="mt-1 text-lg font-semibold text-sky-950">
                            {{ number_format($stockSnapshot['in_use']) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-800">
                        Physical = Reserved + Free
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ number_format($stockSnapshot['physical']) }}
                        =
                        {{ number_format($stockSnapshot['reserved']) }}
                        +
                        {{ number_format($stockSnapshot['free']) }}
                    </p>
                </div>

                @if ($item->consumption_type === 'non-consumable')
                    <div class="rounded-xl border border-slate-200 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-800">
                            Overall = Physical + Deployed
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ number_format($stockSnapshot['overall']) }}
                            =
                            {{ number_format($stockSnapshot['physical']) }}
                            +
                            {{ number_format($stockSnapshot['in_use']) }}
                        </p>
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-800">
                            Consumable Item
                        </p>

                        <p class="mt-1 text-sm text-slate-500">
                            Consumables decrease from overall stock when they are released.
                        </p>
                    </div>
                @endif
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="xl:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-950">
                            Inventory Transactions
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Opening stock, releases, returns, adjustments, and recorded transaction values.
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
                                        Type
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Overall Change
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Physical Change
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Unit Cost
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Transaction Value
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Recorded By
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($item->inventoryTransactions as $transaction)
                                    <tr>
                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                            {{ $transaction->created_at->format('M d, Y h:i A') }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm font-medium capitalize text-slate-900">
                                            {{ str_replace(
                                                '_',
                                                ' ',
                                                $transaction->type
                                            ) }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                            {{ $transaction->overall_stock_change > 0 ? '+' : '' }}
                                            {{ $transaction->overall_stock_change }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                            {{ $transaction->available_quantity_change > 0 ? '+' : '' }}
                                            {{ $transaction->available_quantity_change }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                            @if ($transaction->unit_cost_snapshot !== null)
                                                ₱{{ number_format(
                                                    (float) $transaction->unit_cost_snapshot,
                                                    2
                                                ) }}
                                            @else
                                                <span class="text-slate-400">
                                                    —
                                                </span>
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                            @if ($transaction->total_cost !== null)
                                                <span class="font-semibold text-slate-900">
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

                                        <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                            {{ $transaction->user?->name ?? 'System' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="7"
                                            class="px-5 py-12 text-center text-sm text-slate-500"
                                        >
                                            No stock transactions recorded yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-950">
                        Item Information
                    </h3>

                    <dl class="mt-5 space-y-4">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Category
                            </dt>

                            <dd class="mt-1 text-sm capitalize text-slate-800">
                                {{ $item->category }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Consumption Type
                            </dt>

                            <dd class="mt-1 text-sm capitalize text-slate-800">
                                {{ str_replace(
                                    '-',
                                    ' ',
                                    $item->consumption_type
                                ) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Unit of Measurement
                            </dt>

                            <dd class="mt-1 text-sm text-slate-800">
                                {{ $item->unit }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Current Unit Cost
                            </dt>

                            <dd class="mt-1 text-sm font-semibold text-slate-800">
                                @if ($currentUnitCost !== null)
                                    ₱{{ number_format($currentUnitCost, 2) }}
                                @else
                                    <span class="font-normal text-slate-400">
                                        Not encoded
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Minimum Stock
                            </dt>

                            <dd class="mt-1 text-sm text-slate-800">
                                {{ number_format(
                                    $item->minimum_stock_level
                                ) }}
                                {{ $item->unit }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Storage Location
                            </dt>

                            <dd class="mt-1 text-sm text-slate-800">
                                {{ $item->storage_location ?? 'Not assigned' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Remarks
                            </dt>

                            <dd class="mt-1 text-sm leading-6 text-slate-800">
                                {{ $item->remarks ?? 'No remarks recorded.' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-slate-950">
                        Recent Activity
                    </h3>

                    <div class="mt-5 space-y-4">
                        @forelse ($item->activityLogs as $activity)
                            <div class="border-l-2 border-slate-200 pl-4">
                                <p class="text-sm text-slate-800">
                                    {{ $activity->description }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $activity->created_at->format(
                                        'M d, Y h:i A'
                                    ) }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">
                                No activity recorded yet.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection