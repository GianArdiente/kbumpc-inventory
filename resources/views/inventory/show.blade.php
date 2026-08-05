@extends('layouts.app')

@section('page-title', 'Inventory Item')

@section('content')
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
                <div class="flex gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-white"
                    >
                        Edit Item
                    </button>

                    <a
                        href="{{ route('inventory.adjust', $item) }}"
                        class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Adjust Stock
                    </a>
                </div>
            @endif
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Available Quantity
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($item->available_quantity) }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    {{ ucfirst($item->unit) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Overall Stock
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($item->overall_stock) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Currently In Use
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($inUseQuantity) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Stock Status
                </p>

                <div class="mt-4">
                    @if ($stockStatus === 'archived')
                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">
                            Archived
                        </span>
                    @elseif ($stockStatus === 'out_of_stock')
                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800">
                            Out of stock
                        </span>
                    @elseif ($stockStatus === 'low_stock')
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                            Low stock
                        </span>
                    @else
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                            Available
                        </span>
                    @endif
                </div>
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
                            Opening stock, releases, returns, and future adjustments.
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
                                        Available Change
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
                                            {{ str_replace('_', ' ', $transaction->type) }}
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
                                            colspan="5"
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
                                {{ str_replace('-', ' ', $item->consumption_type) }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                Minimum Stock
                            </dt>

                            <dd class="mt-1 text-sm text-slate-800">
                                {{ number_format($item->minimum_stock_level) }}
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
                                    {{ $activity->created_at->format('M d, Y h:i A') }}
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