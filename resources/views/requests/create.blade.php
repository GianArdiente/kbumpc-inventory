@extends('layouts.app')

@section('page-title', 'Create Request')

@section('content')
    <div class="mx-auto max-w-4xl">
        <form
            method="POST"
            action="{{ route('requests.store') }}"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            @csrf

            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="text-lg font-semibold text-slate-950">
                    Request Information
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Encode the foreman, project, outage, and work-area information.
                    Requested items will be added after saving.
                </p>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <div>
                    <label
                        for="foreman_name"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Foreman Name
                    </label>

                    <input
                        id="foreman_name"
                        name="foreman_name"
                        type="text"
                        value="{{ old('foreman_name') }}"
                        required
                        placeholder="Enter foreman name"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('foreman_name')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="requested_at"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Requested Date and Time
                    </label>

                    <input
                        id="requested_at"
                        name="requested_at"
                        type="datetime-local"
                        value="{{ old(
                            'requested_at',
                            now()->format('Y-m-d\TH:i')
                        ) }}"
                        required
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('requested_at')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="project_name"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Project or Outage
                    </label>

                    <input
                        id="project_name"
                        name="project_name"
                        type="text"
                        value="{{ old('project_name') }}"
                        required
                        placeholder="Example: Boiler Unit 2 Outage"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('project_name')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="work_area"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Work Area
                    </label>

                    <input
                        id="work_area"
                        name="work_area"
                        type="text"
                        value="{{ old('work_area') }}"
                        required
                        placeholder="Example: Boiler Area"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('work_area')
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
                        placeholder="Optional notes or instructions"
                        class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >{{ old('remarks') }}</textarea>

                    @error('remarks')
                        <p class="mt-2 text-sm text-rose-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="md:col-span-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3">
                    <p class="text-sm font-semibold text-sky-900">
                        The request will initially be Pending.
                    </p>

                    <p class="mt-1 text-sm text-sky-800">
                        After saving, the Tool Keeper Workspace will open so requested
                        tools, equipment, materials, and consumables can be added.
                    </p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('requests.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                >
                    Create Request
                </button>
            </div>
        </form>
    </div>
@endsection