@extends('layouts.app')

@section('page-title', 'Requests')

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
                                #123f8c,
                                transparent 70%
                            );
                    "
                ></div>

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">
                                Request Management
                            </p>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            Warehouse Requests
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Create and monitor material and tool requests from
                            submission through approval, release, and completion.
                        </p>
                    </div>

                    <a
                        href="{{ route('requests.create') }}"
                        class="kbumpc-btn-primary"
                    >
                        Create Request
                    </a>
                </div>
            </div>
        </section>

        {{-- Request Statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Total Requests --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500">
                            Total Requests
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                            {{ number_format($requestCounts['all']) }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-sm font-bold text-slate-700">
                        TR
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-500">
                    All warehouse requests
                </p>
            </div>

            {{-- Pending --}}
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-amber-800">
                            Pending
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight text-amber-950">
                            {{ number_format($requestCounts['pending']) }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm font-bold text-amber-700">
                        P
                    </div>
                </div>

                <p class="mt-3 text-xs text-amber-700">
                    Waiting for review
                </p>
            </div>

            {{-- Mobilized / In Use --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-blue-800">
                            Mobilized / In Use
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight text-blue-950">
                            {{ number_format(
                                $requestCounts['mobilized']
                                + $requestCounts['in_use']
                            ) }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm font-bold text-blue-700">
                        MU
                    </div>
                </div>

                <p class="mt-3 text-xs text-blue-700">
                    Active warehouse releases
                </p>
            </div>

            {{-- Completed --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-emerald-800">
                            Completed
                        </p>

                        <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-950">
                            {{ number_format($requestCounts['completed']) }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/70 text-sm font-bold text-emerald-700">
                        C
                    </div>
                </div>

                <p class="mt-3 text-xs text-emerald-700">
                    Finished requests
                </p>
            </div>
        </section>

        {{-- Request Records --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Search / Filter Area --}}
            <div class="border-b border-slate-200 p-5 sm:p-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">
                        Request Records
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Search requests or narrow the list by workflow status.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('requests.index') }}"
                    class="mt-5 grid gap-3 md:grid-cols-3"
                >
                    <div class="md:col-span-2">
                        <label
                            for="search"
                            class="sr-only"
                        >
                            Search requests
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            placeholder="Search request number, foreman, project, or work area..."
                            class="kbumpc-input"
                        >
                    </div>

                    <div>
                        <label
                            for="status"
                            class="sr-only"
                        >
                            Request status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="kbumpc-input"
                        >
                            <option value="">
                                All statuses
                            </option>

                            @foreach ([
                                'pending',
                                'approved',
                                'mobilized',
                                'in_use',
                                'completed',
                            ] as $requestStatus)
                                <option
                                    value="{{ $requestStatus }}"
                                    @selected($status === $requestStatus)
                                >
                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $requestStatus
                                        )
                                    ) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-3 md:col-span-3">
                        <button
                            type="submit"
                            class="kbumpc-btn-primary"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('requests.index') }}"
                            class="kbumpc-btn-outline"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Request Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="kbumpc-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Request
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Foreman
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Project
                            </th>

                            <th class="hidden xl:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Work Area
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="hidden 2xl:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Requested
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($requests as $inventoryRequest)
                            @php
                                $statusClasses = match (
                                    $inventoryRequest->status
                                ) {
                                    'pending' =>
                                        'bg-amber-100 text-amber-800',

                                    'approved' =>
                                        'bg-blue-100 text-blue-800',

                                    'mobilized' =>
                                        'bg-sky-100 text-sky-800',

                                    'in_use' =>
                                        'bg-violet-100 text-violet-800',

                                    'completed' =>
                                        'bg-emerald-100 text-emerald-800',

                                    default =>
                                        'bg-slate-100 text-slate-700',
                                };
                            @endphp

                            <tr class="kbumpc-table-row">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <a
                                        href="{{ route(
                                            'requests.show',
                                            $inventoryRequest
                                        ) }}"
                                        class="font-semibold text-slate-900 transition hover:text-emerald-700"
                                    >
                                        {{ $inventoryRequest->request_number }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-400 2xl:hidden">
                                        {{ $inventoryRequest->requested_at?->format(
                                            'M d, Y'
                                        ) ?? 'Date not recorded' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                    {{ $inventoryRequest->foreman_name }}
                                </td>

                                <td class="max-w-56 px-4 py-3 text-sm text-slate-600">
                                    {{ $inventoryRequest->project_name }}
                                </td>

                                <td class="hidden xl:table-cell max-w-48 px-4 py-3 text-sm text-slate-600">
                                    {{ $inventoryRequest->work_area }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3">
                                    <span
                                        class="rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $statusClasses }}"
                                    >
                                        {{ str_replace(
                                            '_',
                                            ' ',
                                            $inventoryRequest->status
                                        ) }}
                                    </span>
                                </td>

                                <td class="hidden 2xl:table-cell whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                    {{ $inventoryRequest->requested_at?->format(
                                        'M d, Y h:i A'
                                    ) ?? 'Not recorded' }}
                                </td>

                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a
                                        href="{{ route(
                                            'requests.show',
                                            $inventoryRequest
                                        ) }}"
                                        class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900"
                                    >
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-5 py-14 text-center"
                                >
                                    <div class="mx-auto max-w-sm">
                                        <p class="font-semibold text-slate-800">
                                            No requests found.
                                        </p>

                                        <p class="mt-2 text-sm leading-6 text-slate-500">
                                            Try changing the filters or create
                                            a new warehouse request.
                                        </p>

                                        <a
                                            href="{{ route('requests.create') }}"
                                            class="kbumpc-btn-primary mt-5"
                                        >
                                            Create Request
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection