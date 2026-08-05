@extends('layouts.app')

@section('page-title', 'Add Inventory Item')

@section('content')
    <div class="mx-auto max-w-4xl">
        <form
            method="POST"
            action="{{ route('inventory.store') }}"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            @csrf

            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-950">
                    Item Information
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Add a warehouse tool, equipment, material, or consumable.
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
                        value="{{ old('item_code') }}"
                        placeholder="Example: TOOL-002"
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
                        value="{{ old('name') }}"
                        placeholder="Example: Ball Hammer"
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
                        <option value="">Select category</option>
                        <option value="tool" @selected(old('category') === 'tool')>
                            Tool
                        </option>
                        <option value="equipment" @selected(old('category') === 'equipment')>
                            Equipment
                        </option>
                        <option value="material" @selected(old('category') === 'material')>
                            Material
                        </option>
                        <option value="consumable" @selected(old('category') === 'consumable')>
                            Consumable
                        </option>
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
                        <option value="">Select item type</option>
                        <option
                            value="consumable"
                            @selected(old('consumption_type') === 'consumable')
                        >
                            Consumable
                        </option>
                        <option
                            value="non-consumable"
                            @selected(old('consumption_type') === 'non-consumable')
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
                        value="{{ old('unit') }}"
                        placeholder="Piece, pair, box, roll..."
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
                        for="storage_location"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Storage Location
                    </label>

                    <input
                        id="storage_location"
                        name="storage_location"
                        type="text"
                        value="{{ old('storage_location') }}"
                        placeholder="Rack, shelf, or warehouse section"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('storage_location')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="initial_stock"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Initial Stock
                    </label>

                    <input
                        id="initial_stock"
                        name="initial_stock"
                        type="number"
                        min="0"
                        value="{{ old('initial_stock', 0) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('initial_stock')
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
                        value="{{ old('minimum_stock_level', 0) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('minimum_stock_level')
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
                        placeholder="Optional inventory notes..."
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
                    href="{{ route('inventory.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Save Inventory Item
                </button>
            </div>
        </form>
    </div>
@endsection