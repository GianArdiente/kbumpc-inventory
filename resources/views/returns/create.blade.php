@extends('layouts.app')

@section('page-title', 'Record Return')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <a
                href="{{ route('returns.index') }}"
                class="text-sm font-semibold text-slate-500 hover:text-slate-800"
            >
                ← Back to Returns
            </a>

            <h3 class="mt-3 text-2xl font-semibold text-slate-950">
                Record Return
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                Account for reusable inventory released from the warehouse.
            </p>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Request
                </p>

                <p class="mt-2 font-semibold text-slate-950">
                    {{ $requestItem->inventoryRequest->request_number }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Item
                </p>

                <p class="mt-2 font-semibold text-slate-950">
                    {{ $requestItem->item->name }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $requestItem->item->item_code }}
                </p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-sky-600">
                    Released
                </p>

                <p class="mt-2 text-2xl font-semibold text-sky-950">
                    {{ number_format(
                        $requestItem->released_quantity
                    ) }}
                </p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                    Remaining
                </p>

                <p class="mt-2 text-2xl font-semibold text-amber-950">
                    {{ number_format($remainingQuantity) }}
                </p>
            </div>
        </section>

        @if ($accountedQuantity > 0)
            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
                <p class="font-semibold text-slate-900">
                    Previous Returns
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ number_format($accountedQuantity) }}
                    {{ $requestItem->item->unit }}
                    have already been accounted for on this request line.
                </p>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route(
                'returns.store',
                $requestItem
            ) }}"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            @csrf

            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Return Condition
                </h3>

                <p class="mt-1 text-sm leading-6 text-slate-500">
                    Good + Damaged + Missing must not exceed the remaining
                    {{ number_format($remainingQuantity) }}
                    {{ $requestItem->item->unit }}.
                </p>
            </div>

            <div class="space-y-6 p-6">
                @if ($errors->has('return'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ $errors->first('return') }}
                    </div>
                @endif

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label
                            for="good_quantity"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Good Returned
                        </label>

                        <input
                            id="good_quantity"
                            name="good_quantity"
                            type="number"
                            min="0"
                            max="{{ $remainingQuantity }}"
                            value="{{ old(
                                'good_quantity',
                                0
                            ) }}"
                            required
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >

                        @error('good_quantity')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="damaged_quantity"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Damaged
                        </label>

                        <input
                            id="damaged_quantity"
                            name="damaged_quantity"
                            type="number"
                            min="0"
                            max="{{ $remainingQuantity }}"
                            value="{{ old(
                                'damaged_quantity',
                                0
                            ) }}"
                            required
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                        >

                        @error('damaged_quantity')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="missing_quantity"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Missing
                        </label>

                        <input
                            id="missing_quantity"
                            name="missing_quantity"
                            type="number"
                            min="0"
                            max="{{ $remainingQuantity }}"
                            value="{{ old(
                                'missing_quantity',
                                0
                            ) }}"
                            required
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-rose-500 focus:ring-rose-500"
                        >

                        @error('missing_quantity')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
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
                        rows="4"
                        placeholder="Optional notes about the return, damage, or missing item..."
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >{{ old('remarks') }}</textarea>

                    @error('remarks')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="rounded-xl bg-slate-50 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-800">
                        What happens after submission?
                    </p>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Good units return to warehouse stock. Damaged and missing
                        units are permanently recorded and removed from active
                        overall stock. If some released units remain unaccounted for,
                        the request stays In Use.
                    </p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('returns.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Record Return
                </button>
            </div>
        </form>
    </div>
@endsection