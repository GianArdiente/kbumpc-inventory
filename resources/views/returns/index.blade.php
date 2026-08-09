@extends('layouts.app')

@section('page-title', 'Returns')

@section('content')
    <div class="space-y-6">
        <div>
            <h3 class="text-2xl font-semibold text-slate-950">
                Returns
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                Track released non-consumable items that still need to be returned,
                reported damaged, or marked missing.
            </p>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Active Return Work
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Mobilized and in-use requests with released inventory.
                </p>
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
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a
                                            href="{{ route(
                                                'requests.show',
                                                $inventoryRequest
                                            ) }}"
                                            class="text-lg font-semibold text-slate-950 hover:text-indigo-700"
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
                                        ·
                                        {{ $inventoryRequest->foreman_name }}
                                        ·
                                        {{ $inventoryRequest->work_area }}
                                    </p>
                                </div>

                                <div class="text-sm text-slate-500">
                                    Mobilized:
                                    <span class="font-medium text-slate-800">
                                        {{ $inventoryRequest->mobilized_at?->format(
                                            'M d, Y h:i A'
                                        ) ?? 'Not recorded' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Item
                                            </th>

                                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Released
                                            </th>

                                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Accounted
                                            </th>

                                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                Remaining
                                            </th>

                                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
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

                                            <tr>
                                                <td class="px-5 py-4">
                                                    <p class="font-semibold text-slate-900">
                                                        {{ $requestItem->item->name }}
                                                    </p>

                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ $requestItem->item->item_code }}
                                                        ·
                                                        {{ ucfirst(
                                                            $requestItem->item->unit
                                                        ) }}
                                                    </p>
                                                </td>

                                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                                    <span class="font-semibold text-sky-700">
                                                        {{ number_format(
                                                            $requestItem->released_quantity
                                                        ) }}
                                                    </span>

                                                    <span class="ml-1 text-xs text-slate-500">
                                                        {{ $requestItem->item->unit }}
                                                    </span>
                                                </td>

                                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                                    <span class="font-semibold text-slate-700">
                                                        {{ number_format(
                                                            $accountedTotal
                                                        ) }}
                                                    </span>

                                                    <span class="ml-1 text-xs text-slate-500">
                                                        {{ $requestItem->item->unit }}
                                                    </span>

                                                    @if ($accountedTotal > 0)
                                                        <p class="mt-1 text-xs text-slate-400">
                                                            Good {{ $goodTotal }}
                                                            · Damaged {{ $damagedTotal }}
                                                            · Missing {{ $missingTotal }}
                                                        </p>
                                                    @endif
                                                </td>

                                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                                    <span class="font-semibold text-amber-700">
                                                        {{ number_format(
                                                            $remaining
                                                        ) }}
                                                    </span>

                                                    <span class="ml-1 text-xs text-slate-500">
                                                        {{ $requestItem->item->unit }}
                                                    </span>
                                                </td>

                                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                                    <a
                                                        href="{{ route(
                                                            'returns.create',
                                                            $requestItem
                                                        ) }}"
                                                        class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
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
                        <p class="font-semibold text-slate-800">
                            No active returns.
                        </p>

                        <p class="mt-2 text-sm text-slate-500">
                            Mobilized non-consumable items will appear here until
                            every released unit is accounted for.
                        </p>
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