@extends('layouts.app')

@section('page-title', 'Request Workspace')

@section('content')
    @php
        $requestStatusClasses = match (
            $inventoryRequest->status
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

        $originalRequestItems = $inventoryRequest
            ->items
            ->where('request_type', 'original');

        $requestReservedTotal =
            $inventoryRequest->status === 'approved'
                ? $originalRequestItems->sum(
                    'requested_quantity'
                )
                : 0;

        $requestReleasedTotal =
            $originalRequestItems->sum(
                'released_quantity'
            );
    @endphp

    <div class="space-y-6">

        {{-- Request Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a
                    href="{{ route('requests.index') }}"
                    class="text-sm font-semibold text-slate-500 hover:text-slate-800"
                >
                    ← Back to Requests
                </a>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-semibold text-slate-950">
                        {{ $inventoryRequest->request_number }}
                    </h3>

                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $requestStatusClasses }}"
                    >
                        {{ str_replace(
                            '_',
                            ' ',
                            $inventoryRequest->status
                        ) }}
                    </span>
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    {{ $inventoryRequest->project_name }}
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if (
                    auth()->user()->role === 'admin' &&
                    $inventoryRequest->status === 'pending'
                )
                    <form
                        method="POST"
                        action="{{ route(
                            'requests.approve',
                            $inventoryRequest
                        ) }}"
                        onsubmit="return confirm('Approve request {{ $inventoryRequest->request_number }}? Its requested quantities will be reserved and items can no longer be edited.');"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                        >
                            Approve and Reserve
                        </button>
                    </form>
                @endif

                @if (
                    auth()->user()->role === 'admin' &&
                    $inventoryRequest->status === 'approved'
                )
                    <form
                        method="POST"
                        action="{{ route(
                            'requests.mobilize',
                            $inventoryRequest
                        ) }}"
                        onsubmit="return confirm('Mobilize {{ $inventoryRequest->request_number }}? This will physically release all reserved items from the warehouse.');"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700"
                        >
                            Mobilize Request
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Action Errors --}}
        @if ($errors->has('request_action'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first('request_action') }}
            </div>
        @endif

        {{-- Approved State --}}
        @if ($inventoryRequest->status === 'approved')
            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-indigo-950">
                            Inventory Reserved
                        </p>

                        <p class="mt-1 text-sm leading-6 text-indigo-800">
                            These quantities are committed to this request but are
                            still physically inside the warehouse. Mobilization will
                            release them and remove this request's reservation.
                        </p>
                    </div>

                    <div class="shrink-0 rounded-xl bg-white px-4 py-3 text-center shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                            Reserved for this request
                        </p>

                        <p class="mt-1 text-2xl font-semibold text-indigo-950">
                            {{ number_format(
                                $requestReservedTotal
                            ) }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Mobilized State --}}
        @if (
            in_array(
                $inventoryRequest->status,
                ['mobilized', 'in_use', 'completed'],
                true
            )
        )
            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold text-sky-950">
                            Inventory Released
                        </p>

                        <p class="mt-1 text-sm leading-6 text-sky-800">
                            This request has already been released from the warehouse.
                            Any reservation shown in the item table now belongs to
                            other approved requests, not this mobilized request.
                        </p>
                    </div>

                    <div class="shrink-0 rounded-xl bg-white px-4 py-3 text-center shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">
                            Released for this request
                        </p>

                        <p class="mt-1 text-2xl font-semibold text-sky-950">
                            {{ number_format(
                                $requestReleasedTotal
                            ) }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Request Summary --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Foreman
                </p>

                <p class="mt-2 font-semibold text-slate-950">
                    {{ $inventoryRequest->foreman_name }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Work Area
                </p>

                <p class="mt-2 font-semibold text-slate-950">
                    {{ $inventoryRequest->work_area }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Requested
                </p>

                <p class="mt-2 font-semibold text-slate-950">
                    {{ $inventoryRequest->requested_at?->format(
                        'M d, Y h:i A'
                    ) ?? 'Not recorded' }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Encoded By
                </p>

                <p class="mt-2 font-semibold text-slate-950">
                    {{ $inventoryRequest->encoder?->name ?? 'Unknown' }}
                </p>
            </div>
        </section>

        {{-- Pending Item Entry + Request Items --}}
        <section
            class="grid gap-6
                {{ $inventoryRequest->status === 'pending'
                    ? 'xl:grid-cols-3'
                    : '' }}"
        >
            @if ($inventoryRequest->status === 'pending')
                <div class="xl:col-span-1">
                    <form
                        method="POST"
                        action="{{ route(
                            'requests.items.store',
                            $inventoryRequest
                        ) }}"
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                    >
                        @csrf

                        <div class="border-b border-slate-200 px-6 py-5">
                            <h3 class="font-semibold text-slate-950">
                                Add Requested Item
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-slate-500">
                                Only inventory with unreserved stock is available
                                for selection.
                            </p>
                        </div>

                        <div class="space-y-5 p-6">
                            @if ($errors->has('request_item'))
                                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                                    {{ $errors->first('request_item') }}
                                </div>
                            @endif

                            <div>
                                <label
                                    for="item_id"
                                    class="block text-sm font-semibold text-slate-700"
                                >
                                    Inventory Item
                                </label>

                                <select
                                    id="item_id"
                                    name="item_id"
                                    required
                                    class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                >
                                    <option value="">
                                        Select an item
                                    </option>

                                    @foreach ($activeItems as $item)
                                        @php
                                            $freeToRequest = max(
                                                0,
                                                $item->available_quantity
                                                - $item->reserved_quantity
                                            );
                                        @endphp

                                        <option
                                            value="{{ $item->id }}"
                                            @selected(
                                                old('item_id')
                                                == $item->id
                                            )
                                        >
                                            {{ $item->item_code }}
                                            — {{ $item->name }}
                                            — {{ number_format(
                                                $freeToRequest
                                            ) }}
                                            {{ $item->unit }} free
                                        </option>
                                    @endforeach
                                </select>

                                @error('item_id')
                                    <p class="mt-2 text-sm text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                                @if ($activeItems->isEmpty())
                                    <p class="mt-2 text-sm text-amber-700">
                                        No inventory currently has unreserved stock available.
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label
                                    for="requested_quantity"
                                    class="block text-sm font-semibold text-slate-700"
                                >
                                    Requested Quantity
                                </label>

                                <input
                                    id="requested_quantity"
                                    name="requested_quantity"
                                    type="number"
                                    min="1"
                                    value="{{ old(
                                        'requested_quantity',
                                        1
                                    ) }}"
                                    required
                                    class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                >

                                @error('requested_quantity')
                                    <p class="mt-2 text-sm text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="remarks"
                                    class="block text-sm font-semibold text-slate-700"
                                >
                                    Remarks
                                </label>

                                <textarea
                                    id="remarks"
                                    name="remarks"
                                    rows="3"
                                    placeholder="Optional notes for this item"
                                    class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                                >{{ old('remarks') }}</textarea>

                                @error('remarks')
                                    <p class="mt-2 text-sm text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                            <button
                                type="submit"
                                @disabled($activeItems->isEmpty())
                                class="w-full rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition
                                    {{ $activeItems->isEmpty()
                                        ? 'cursor-not-allowed bg-slate-400'
                                        : 'bg-slate-950 hover:bg-slate-800' }}"
                            >
                                Add to Request
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div
                class="{{ $inventoryRequest->status === 'pending'
                    ? 'xl:col-span-2'
                    : 'w-full' }}"
            >
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <h3 class="font-semibold text-slate-950">
                            Requested Items
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-500">
                            @if (
                                in_array(
                                    $inventoryRequest->status,
                                    ['mobilized', 'in_use', 'completed'],
                                    true
                                )
                            )
                                Released quantities are shown separately from
                                warehouse stock still reserved for other requests.
                            @else
                                Physical availability, reservations, and free stock
                                are shown separately.
                            @endif
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Item
                                    </th>

                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Type
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Requested
                                    </th>

                                    @if (
                                        in_array(
                                            $inventoryRequest->status,
                                            ['mobilized', 'in_use', 'completed'],
                                            true
                                        )
                                    )
                                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Released
                                        </th>
                                    @endif

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Physical
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        @if (
                                            in_array(
                                                $inventoryRequest->status,
                                                ['mobilized', 'in_use', 'completed'],
                                                true
                                            )
                                        )
                                            Reserved Elsewhere
                                        @else
                                            Reserved
                                        @endif
                                    </th>

                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Free
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse (
                                    $originalRequestItems
                                    as $requestItem
                                )
                                    @php
                                        $physicalAvailable =
                                            $requestItem
                                                ->item
                                                ->available_quantity;

                                        $reservedQuantity =
                                            $requestItem
                                                ->item
                                                ->reserved_quantity;

                                        $freeToRequest = max(
                                            0,
                                            $physicalAvailable
                                            - $reservedQuantity
                                        );

                                        $isReleasedState =
                                            in_array(
                                                $inventoryRequest->status,
                                                [
                                                    'mobilized',
                                                    'in_use',
                                                    'completed',
                                                ],
                                                true
                                            );
                                    @endphp

                                    <tr>
                                        <td class="px-5 py-4">
                                            <p class="font-semibold text-slate-900">
                                                {{ $requestItem->item->name }}
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $requestItem->item->item_code }}
                                                ·
                                                {{ ucfirst(
                                                    $requestItem
                                                        ->item
                                                        ->category
                                                ) }}
                                            </p>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-sm capitalize text-slate-600">
                                            {{ str_replace(
                                                '-',
                                                ' ',
                                                $requestItem
                                                    ->item
                                                    ->consumption_type
                                            ) }}
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-slate-950">
                                            {{ number_format(
                                                $requestItem
                                                    ->requested_quantity
                                            ) }}

                                            <span class="ml-1 text-xs font-normal text-slate-500">
                                                {{ $requestItem->item->unit }}
                                            </span>
                                        </td>

                                        @if ($isReleasedState)
                                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-sky-700">
                                                {{ number_format(
                                                    $requestItem
                                                        ->released_quantity
                                                ) }}

                                                <span class="ml-1 text-xs font-normal text-sky-500">
                                                    {{ $requestItem->item->unit }}
                                                </span>
                                            </td>
                                        @endif

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-slate-700">
                                            {{ number_format(
                                                $physicalAvailable
                                            ) }}

                                            <span class="ml-1 text-xs text-slate-500">
                                                {{ $requestItem->item->unit }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-indigo-700">
                                            {{ number_format(
                                                $reservedQuantity
                                            ) }}

                                            <span class="ml-1 text-xs font-normal text-indigo-500">
                                                {{ $requestItem->item->unit }}
                                            </span>
                                        </td>

                                        <td
                                            class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold
                                                {{ $freeToRequest > 0
                                                    ? 'text-emerald-700'
                                                    : 'text-rose-700' }}"
                                        >
                                            {{ number_format(
                                                $freeToRequest
                                            ) }}

                                            <span class="ml-1 text-xs font-normal">
                                                {{ $requestItem->item->unit }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="7"
                                            class="px-5 py-14 text-center"
                                        >
                                            <p class="font-semibold text-slate-800">
                                                No requested items yet.
                                            </p>

                                            <p class="mt-2 text-sm text-slate-500">
                                                Add at least one item before approving this request.
                                            </p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection