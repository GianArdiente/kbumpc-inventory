@extends('layouts.app')

@section('page-title', 'Requests')

@section('content')
    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Total Requests
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($requestCounts['all']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-800">
                    Pending
                </p>

                <p class="mt-3 text-3xl font-semibold text-amber-950">
                    {{ number_format($requestCounts['pending']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-sky-800">
                    Mobilized / In Use
                </p>

                <p class="mt-3 text-3xl font-semibold text-sky-950">
                    {{ number_format(
                        $requestCounts['mobilized']
                        + $requestCounts['in_use']
                    ) }}
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">
                    Completed
                </p>

                <p class="mt-3 text-3xl font-semibold text-emerald-950">
                    {{ number_format($requestCounts['completed']) }}
                </p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">
                            Warehouse Requests
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Monitor foreman requests, approvals, mobilization, and completion.
                        </p>
                    </div>

                    <a
                        href="{{ route('requests.create') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Create Request
                    </a>
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
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
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
                            class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">All statuses</option>

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
                                    {{ ucfirst(str_replace('_', ' ', $requestStatus)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-3 md:col-span-3">
                        <button
                            type="submit"
                            class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('requests.index') }}"
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
                                Request
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Foreman
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Project
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Work Area
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Requested
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($requests as $inventoryRequest)
                            <tr class="hover:bg-slate-50/80">
                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $inventoryRequest->request_number }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->foreman_name }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->project_name }}
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->work_area }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $statusClasses = match (
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
                                    @endphp

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

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $inventoryRequest->requested_at?->format(
                                        'M d, Y h:i A'
                                    ) ?? 'Not recorded' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a
                                        href="{{ route('requests.show', $inventoryRequest) }}"
                                        class="text-sm font-semibold text-slate-700 hover:text-slate-950"
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
                                    <p class="font-semibold text-slate-800">
                                        No requests found.
                                    </p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Try changing the filters or create a new request.
                                    </p>
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