@extends('layouts.app')

@section('page-title', 'Returns')

@section('content')
    <div class="space-y-6 kbumpc-page-enter">

        {{-- Page Introduction --}}
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

                <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                Return Management
                            </p>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            Active Returns
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Track released non-consumable items that still need
                            to be returned, reported damaged, or marked missing.
                        </p>
                    </div>

                    <a
                        href="{{ route('requests.index') }}"
                        class="kbumpc-btn-outline"
                    >
                        View Requests
                    </a>
                </div>
            </div>
        </section>

        {{-- Active Return Work --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <div>
                    <h3 class="font-semibold text-slate-950">
                        Active Return Work
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Mobilized and in-use requests with released inventory
                        that has not yet been fully accounted for.
                    </p>
                </div>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($requests as $inventoryRequest)
                    @php
                        $returnableItems = $inventoryRequest
                            ->items
                            ->filter(function ($requestItem) {
                                if (! $requestItem->item) {
                                    return false;
                                }

                                if (
                                    $requestItem->item->consumption_type
                                    !== 'non-consumable'
                                ) {
                                    return false;
                                }

                                if ($requestItem->released_quantity < 1) {
                                    return false;
                                }

                                $accounted = $requestItem
                                    ->returnRecords
                                    ->sum(function ($record) {
                                        return
                                            $record->good_quantity
                                            + $record->damaged_quantity
                                            + $record->missing_quantity;
                                    });

                                return $accounted
                                    < $requestItem->released_quantity;
                            });
                    @endphp

                    @if ($returnableItems->isNotEmpty())
                        <div class="p-6">

                            {{-- Request Header --}}
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a
                                            href="{{ route(
                                                'requests.show',
                                                $inventoryRequest
                                            ) }}"
                                            class="text-lg font-semibold text-slate-950 transition hover:text-emerald-700"
                                        >
                                            {{ $inventoryRequest->request_number }}
                                        </a>

                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-semibold capitalize
                                                {{ $inventoryRequest->status === 'in_use'
                                                    ? 'bg-violet-100 text-violet-800'
                                                    : 'bg-sky-100 text-sky-800' }}"
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

                                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-400">
                                        <span>
                                            Foreman:
                                            <span class="font-medium text-slate-600">
                                                {{ $inventoryRequest->foreman_name }}
                                            </span>
                                        </span>

                                        <span>
                                            Work Area:
                                            <span class="font-medium text-slate-600">
                                                {{ $inventoryRequest->work_area }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Mobilized
                                    </p>

                                    <p class="mt-1 font-medium text-slate-800">
                                        {{ $inventoryRequest->mobilized_at?->format(
                                            'M d, Y h:i A'
                                        ) ?? 'Not recorded' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Returnable Items --}}
                            <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="kbumpc-table-head">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Item
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Released
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Accounted
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Remaining
                                            </th>

                                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach ($returnableItems as $requestItem)
                                            @php
                                                $goodTotal = $requestItem
                                                    ->returnRecords
                                                    ->sum('good_quantity');

                                                $damagedTotal = $requestItem
                                                    ->returnRecords
                                                    ->sum('damaged_quantity');

                                                $missingTotal = $requestItem
                                                    ->returnRecords
                                                    ->sum('missing_quantity');

                                                $accountedTotal =
                                                    $goodTotal
                                                    + $damagedTotal
                                                    + $missingTotal;

                                                $remaining = max(
                                                    0,
                                                    $requestItem->released_quantity
                                                    - $accountedTotal
                                                );
                                            @endphp

                                            <tr class="kbumpc-table-row">

                                                {{-- Item --}}
                                                <td class="w-56 px-4 py-3">
                                                    <p class="font-semibold leading-5 text-slate-900">
                                                        {{ $requestItem->item->name }}
                                                    </p>

                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ $requestItem->item->item_code }}
                                                    </p>

                                                    <p class="mt-1 text-xs capitalize text-slate-400">
                                                        Unit:
                                                        {{ $requestItem->item->unit }}
                                                    </p>
                                                </td>

                                                {{-- Released --}}
                                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                                    <span class="text-lg font-semibold text-sky-700">
                                                        {{ number_format(
                                                            $requestItem->released_quantity
                                                        ) }}
                                                    </span>
                                                </td>

                                                {{-- Accounted --}}
                                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                                    <span class="text-lg font-semibold text-slate-700">
                                                        {{ number_format(
                                                            $accountedTotal
                                                        ) }}
                                                    </span>

                                                    @if ($accountedTotal > 0)
                                                        <div class="mt-2 flex flex-wrap justify-end gap-1.5">
                                                            @if ($goodTotal > 0)
                                                                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700">
                                                                    Good {{ $goodTotal }}
                                                                </span>
                                                            @endif

                                                            @if ($damagedTotal > 0)
                                                                <span class="rounded-full bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-700">
                                                                    Damaged {{ $damagedTotal }}
                                                                </span>
                                                            @endif

                                                            @if ($missingTotal > 0)
                                                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700">
                                                                    Missing {{ $missingTotal }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- Remaining --}}
                                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                                    <span
                                                        class="text-lg font-semibold
                                                            {{ $remaining > 0
                                                                ? 'text-amber-700'
                                                                : 'text-emerald-700' }}"
                                                    >
                                                        {{ number_format(
                                                            $remaining
                                                        ) }}
                                                    </span>
                                                </td>

                                                {{-- Action --}}
                                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                                    <a
                                                        href="{{ route(
                                                            'returns.create',
                                                            $requestItem
                                                        ) }}"
                                                        class="kbumpc-btn-primary"
                                                    >
                                                        Record Return
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="px-6 py-14 text-center">
                        <div class="mx-auto max-w-md">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-sm font-bold text-emerald-700">
                                OK
                            </div>

                            <p class="mt-4 font-semibold text-slate-800">
                                No active returns.
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Mobilized non-consumable items will appear here
                                until every released unit is accounted for.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($requests->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection