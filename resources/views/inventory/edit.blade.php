@extends('layouts.app')

@section('page-title', 'Edit Inventory Item')

@section('content')
    <div class="mx-auto max-w-4xl">
        <form
            method="POST"
            action="{{ route('inventory.update', $item) }}"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            @csrf
            @method('PUT')

            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-950">
                    Edit {{ $item->name }}
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Update descriptive information and the current reference cost without directly changing stock quantities.
                </p>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label
                        for="item_code"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Item Code
                    </label>

                    <input
                        id="item_code"
                        name="item_code"
                        type="text"
                        value="{{ old('item_code', $item->item_code) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('item_code')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="name"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Item Name
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name', $item->name) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('name')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="category"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Category
                    </label>

                    <select
                        id="category"
                        name="category"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                        @foreach (
                            ['tool', 'equipment', 'material', 'consumable']
                            as $category
                        )
                            <option
                                value="{{ $category }}"
                                @selected(
                                    old('category', $item->category)
                                    === $category
                                )
                            >
                                {{ ucfirst($category) }}
                            </option>
                        @endforeach
                    </select>

                    @error('category')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="consumption_type"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Consumption Type
                    </label>

                    <select
                        id="consumption_type"
                        name="consumption_type"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                        <option
                            value="consumable"
                            @selected(
                                old(
                                    'consumption_type',
                                    $item->consumption_type
                                ) === 'consumable'
                            )
                        >
                            Consumable
                        </option>

                        <option
                            value="non-consumable"
                            @selected(
                                old(
                                    'consumption_type',
                                    $item->consumption_type
                                ) === 'non-consumable'
                            )
                        >
                            Non-consumable
                        </option>
                    </select>

                    @error('consumption_type')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="unit"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Unit of Measurement
                    </label>

                    <input
                        id="unit"
                        name="unit"
                        type="text"
                        value="{{ old('unit', $item->unit) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('unit')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="unit_cost"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Current Unit Cost (₱)
                    </label>

                    <input
                        id="unit_cost"
                        name="unit_cost"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ old('unit_cost', $item->unit_cost) }}"
                        placeholder="Example: 125.00"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        Updating this changes the reference cost for future transactions only.
                    </p>

                    @error('unit_cost')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="minimum_stock_level"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Minimum Stock Level
                    </label>

                    <input
                        id="minimum_stock_level"
                        name="minimum_stock_level"
                        type="number"
                        min="0"
                        value="{{ old(
                            'minimum_stock_level',
                            $item->minimum_stock_level
                        ) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('minimum_stock_level')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="storage_location"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Storage Location
                    </label>

                    <input
                        id="storage_location"
                        name="storage_location"
                        type="text"
                        value="{{ old(
                            'storage_location',
                            $item->storage_location
                        ) }}"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('storage_location')
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
                        Remarks
                    </label>

                    <textarea
                        id="remarks"
                        name="remarks"
                        rows="4"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >{{ old('remarks', $item->remarks) }}</textarea>

                    @error('remarks')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="md:col-span-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <p class="text-sm font-semibold text-amber-900">
                        Stock quantities cannot be edited here.
                    </p>

                    <p class="mt-1 text-sm text-amber-800">
                        Use the Adjust Stock action to preserve the inventory ledger.
                        Unit cost changes apply to future transactions and do not rewrite historical costs.
                    </p>
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
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection