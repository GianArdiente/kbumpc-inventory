@extends('layouts.app')

@section('page-title', 'Inventory')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Active Items
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($statistics['active_items']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">
                    Free Units
                </p>

                <p class="mt-3 text-3xl font-semibold text-emerald-950">
                    {{ number_format($statistics['free_units']) }}
                </p>

                <p class="mt-2 text-xs text-emerald-700">
                    Units still available for new requests
                </p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-800">
                    Reserved Units
                </p>

                <p class="mt-3 text-3xl font-semibold text-indigo-950">
                    {{ number_format($statistics['reserved_units']) }}
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
                    {{ number_format($statistics['physical_units']) }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    Units currently inside the warehouse
                </p>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-800">
                    Low-Stock Items
                </p>

                <p class="mt-3 text-2xl font-semibold text-amber-950">
                    {{ number_format($statistics['low_stock_items']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-rose-800">
                    Out-of-Stock Items
                </p>

                <p class="mt-3 text-2xl font-semibold text-rose-950">
                    {{ number_format($statistics['out_of_stock_items']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-800">
                    Fully Reserved Items
                </p>

                <p class="mt-3 text-2xl font-semibold text-indigo-950">
                    {{ number_format($statistics['fully_reserved_items']) }}
                </p>
            </div>
        </section>

        @if ($errors->has('restore'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first('restore') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">
                            Inventory Records
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Physical stock, reservations, free quantities, and current reference costs.
                        </p>
                    </div>

                    @if (auth()->user()->role === 'admin')
                        <a
                            href="{{ route('inventory.create') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Add Inventory Item
                        </a>
                    @endif
                </div>

                <form
                    method="GET"
                    action="{{ route('inventory.index') }}"
                    class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-5"
                >
                    <div class="md:col-span-2">
                        <label for="search" class="sr-only">
                            Search inventory
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Search item code, name, or location..."
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                    </div>

                    <div>
                        <label for="category" class="sr-only">
                            Category
                        </label>

                        <select
                            id="category"
                            name="category"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">All categories</option>

                            <option
                                value="tool"
                                @selected(($filters['category'] ?? '') === 'tool')
                            >
                                Tools
                            </option>

                            <option
                                value="equipment"
                                @selected(($filters['category'] ?? '') === 'equipment')
                            >
                                Equipment
                            </option>

                            <option
                                value="material"
                                @selected(($filters['category'] ?? '') === 'material')
                            >
                                Materials
                            </option>

                            <option
                                value="consumable"
                                @selected(($filters['category'] ?? '') === 'consumable')
                            >
                                Consumables
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="consumption_type" class="sr-only">
                            Consumption type
                        </label>

                        <select
                            id="consumption_type"
                            name="consumption_type"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">All item types</option>

                            <option
                                value="consumable"
                                @selected(
                                    ($filters['consumption_type'] ?? '')
                                    === 'consumable'
                                )
                            >
                                Consumable
                            </option>

                            <option
                                value="non-consumable"
                                @selected(
                                    ($filters['consumption_type'] ?? '')
                                    === 'non-consumable'
                                )
                            >
                                Non-consumable
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="stock_status" class="sr-only">
                            Stock status
                        </label>

                        <select
                            id="stock_status"
                            name="stock_status"
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">Active stock</option>

                            <option
                                value="available"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'available'
                                )
                            >
                                Available
                            </option>

                            <option
                                value="partially_reserved"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'partially_reserved'
                                )
                            >
                                Partially reserved
                            </option>

                            <option
                                value="fully_reserved"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'fully_reserved'
                                )
                            >
                                Fully reserved
                            </option>

                            <option
                                value="low_stock"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'low_stock'
                                )
                            >
                                Low stock
                            </option>

                            <option
                                value="out_of_stock"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'out_of_stock'
                                )
                            >
                                Out of stock
                            </option>

                            <option
                                value="overcommitted"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'overcommitted'
                                )
                            >
                                Overcommitted
                            </option>

                            <option
                                value="archived"
                                @selected(
                                    ($filters['stock_status'] ?? '')
                                    === 'archived'
                                )
                            >
                                Archived
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-3 md:col-span-2 xl:col-span-5">
                        <button
                            type="submit"
                            class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('inventory.index') }}"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Item
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Category
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Type
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Unit Cost
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Physical
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Reserved
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Free
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Overall
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Overall Value
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Location
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($items as $item)
                            @php
                                $stock = $item->stock_snapshot;

                                $overallValue =
                                    $item->unit_cost !== null
                                        ? (float) $item->unit_cost
                                            * $stock['overall']
                                        : null;
                            @endphp

                            <tr class="hover:bg-slate-50/80">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $item->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $item->item_code }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm capitalize text-slate-600">
                                    {{ $item->category }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm capitalize text-slate-600">
                                    {{ str_replace(
                                        '-',
                                        ' ',
                                        $item->consumption_type
                                    ) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if ($item->unit_cost !== null)
                                        <span class="font-semibold text-slate-950">
                                            ₱{{ number_format(
                                                (float) $item->unit_cost,
                                                2
                                            ) }}
                                        </span>

                                        <p class="mt-1 text-xs text-slate-400">
                                            per {{ strtolower($item->unit) }}
                                        </p>
                                    @else
                                        <span class="text-sm text-slate-400">
                                            Not encoded
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <span class="font-semibold text-slate-950">
                                        {{ number_format($stock['physical']) }}
                                    </span>

                                    <span class="ml-1 text-xs text-slate-500">
                                        {{ $item->unit }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <span class="font-semibold text-indigo-700">
                                        {{ number_format($stock['reserved']) }}
                                    </span>

                                    <span class="ml-1 text-xs text-indigo-500">
                                        {{ $item->unit }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <span
                                        class="font-semibold
                                            {{ $stock['free'] > 0
                                                ? 'text-emerald-700'
                                                : 'text-rose-700' }}"
                                    >
                                        {{ number_format($stock['free']) }}
                                    </span>

                                    <span class="ml-1 text-xs text-slate-500">
                                        {{ $item->unit }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <span class="font-semibold text-slate-700">
                                        {{ number_format($stock['overall']) }}
                                    </span>

                                    <span class="ml-1 text-xs text-slate-500">
                                        {{ $item->unit }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if ($overallValue !== null)
                                        <span class="font-semibold text-slate-950">
                                            ₱{{ number_format(
                                                $overallValue,
                                                2
                                            ) }}
                                        </span>
                                    @else
                                        <span class="text-sm text-slate-400">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @switch($stock['status'])
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
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $item->storage_location ?? 'Not assigned' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a
                                            href="{{ route(
                                                'inventory.show',
                                                $item
                                            ) }}"
                                            class="text-sm font-semibold text-slate-700 hover:text-slate-950"
                                        >
                                            View
                                        </a>

                                        @if (
                                            $item->archived_at !== null
                                            && auth()->user()->role === 'admin'
                                        )
                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'inventory.restore',
                                                    $item
                                                ) }}"
                                                onsubmit="return confirm('Restore {{ $item->name }} to the active inventory list?');"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="text-sm font-semibold text-emerald-700 hover:text-emerald-900"
                                                >
                                                    Restore
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="12"
                                    class="px-5 py-14 text-center"
                                >
                                    <p class="font-semibold text-slate-800">
                                        No inventory items found.
                                    </p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Try changing the filters or add a new item.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $items->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection