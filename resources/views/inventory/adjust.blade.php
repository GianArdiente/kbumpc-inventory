@extends('layouts.app')

@section('page-title', 'Adjust Stock')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a
                href="{{ route('inventory.show', $item) }}"
                class="text-sm font-semibold text-slate-500 hover:text-slate-800"
            >
                ← Back to {{ $item->name }}
            </a>
        </div>

        <form
            method="POST"
            action="{{ route('inventory.adjust.store', $item) }}"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            @csrf

            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-950">
                    Adjust {{ $item->name }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Record newly received stock or correct an inventory discrepancy.
                </p>
            </div>

            <div class="grid gap-4 border-b border-slate-200 bg-slate-50 p-6 sm:grid-cols-2">
                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Current Overall Stock
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-slate-950">
                        {{ number_format($item->overall_stock) }}
                        <span class="text-sm font-medium text-slate-500">
                            {{ $item->unit }}
                        </span>
                    </p>
                </div>

                <div class="rounded-xl bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Current Available Quantity
                    </p>

                    <p class="mt-2 text-2xl font-semibold text-slate-950">
                        {{ number_format($item->available_quantity) }}
                        <span class="text-sm font-medium text-slate-500">
                            {{ $item->unit }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-2">
                @if ($errors->has('adjustment'))
                    <div class="md:col-span-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        {{ $errors->first('adjustment') }}
                    </div>
                @endif

                <div class="md:col-span-2">
                    <label
                        for="type"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Adjustment Type
                    </label>

                    <select
                        id="type"
                        name="type"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                        <option value="">Select adjustment type</option>

                        <option
                            value="stock_in"
                            @selected(old('type') === 'stock_in')
                        >
                            Stock In — newly received inventory
                        </option>

                        <option
                            value="correction"
                            @selected(old('type') === 'correction')
                        >
                            Correction — resolve a count discrepancy
                        </option>
                    </select>

                    @error('type')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="overall_change"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Overall Stock Change
                    </label>

                    <input
                        id="overall_change"
                        name="overall_change"
                        type="number"
                        value="{{ old('overall_change', 0) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Use a positive number to add stock or a negative number to remove it.
                    </p>

                    @error('overall_change')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="available_change"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Available Quantity Change
                    </label>

                    <input
                        id="available_change"
                        name="available_change"
                        type="number"
                        value="{{ old('available_change', 0) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Newly received stock normally increases both values by the same amount.
                    </p>

                    @error('available_change')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label
                        for="remarks"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Reason for Adjustment
                    </label>

                    <textarea
                        id="remarks"
                        name="remarks"
                        rows="4"
                        required
                        placeholder="Example: Received 10 new units from warehouse delivery."
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >{{ old('remarks') }}</textarea>

                    @error('remarks')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('inventory.show', $item) }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Save Adjustment
                </button>
            </div>
        </form>
    </div>
@endsection