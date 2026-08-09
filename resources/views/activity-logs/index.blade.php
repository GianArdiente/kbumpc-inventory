@extends('layouts.app')

@section('page-title', 'Activity Logs')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-slate-950">
                    Activity Logs
                </h3>

                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    Review system activity, inventory changes, request actions,
                    returns, and other recorded audit events.
                </p>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Total Logs
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($statistics['total_logs']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-sky-800">
                    Activity Today
                </p>

                <p class="mt-3 text-3xl font-semibold text-sky-950">
                    {{ number_format($statistics['today_logs']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-800">
                    Active Users in Logs
                </p>

                <p class="mt-3 text-3xl font-semibold text-indigo-950">
                    {{ number_format($statistics['users_with_activity']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">
                    Action Types
                </p>

                <p class="mt-3 text-3xl font-semibold text-emerald-950">
                    {{ number_format($statistics['actions_recorded']) }}
                </p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5">
                <div>
                    <h3 class="font-semibold text-slate-950">
                        Search & Filter
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Narrow the audit trail by keyword, action, user, or date range.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('activity-logs.index') }}"
                    class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-6"
                >
                    <div class="md:col-span-2">
                        <label
                            for="search"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Search
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Action, description, or user..."
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                    </div>

                    <div>
                        <label
                            for="action"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Action
                        </label>

                        <select
                            id="action"
                            name="action"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">
                                All actions
                            </option>

                            @foreach ($actions as $action)
                                <option
                                    value="{{ $action }}"
                                    @selected(
                                        ($filters['action'] ?? '')
                                        === $action
                                    )
                                >
                                    {{ ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $action
                                        )
                                    ) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="user_id"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            User
                        </label>

                        <select
                            id="user_id"
                            name="user_id"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">
                                All users
                            </option>

                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected(
                                        (string) ($filters['user_id'] ?? '')
                                        === (string) $user->id
                                    )
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="date_from"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            From
                        </label>

                        <input
                            id="date_from"
                            name="date_from"
                            type="date"
                            value="{{ $filters['date_from'] ?? '' }}"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                    </div>

                    <div>
                        <label
                            for="date_to"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            To
                        </label>

                        <input
                            id="date_to"
                            name="date_to"
                            type="date"
                            value="{{ $filters['date_to'] ?? '' }}"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                    </div>

                    <div class="flex gap-3 md:col-span-2 xl:col-span-6">
                        <button
                            type="submit"
                            class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('activity-logs.index') }}"
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
                                Date
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                User
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Action
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Subject
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Description
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Details
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activityLogs as $activity)
                            @php
                                $subjectType = $activity->subject_type;

                                if ($subjectType) {
                                    $parts = explode('\\', $subjectType);
                                    $subjectLabel = end($parts);
                                } else {
                                    $subjectLabel = '—';
                                }

                                $properties = $activity->properties ?? [];
                            @endphp

                            <tr class="align-top hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $activity->created_at->format(
                                        'M d, Y h:i A'
                                    ) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $activity->user?->name ?? 'System' }}
                                    </p>

                                    @if ($activity->user)
                                        <p class="mt-1 text-xs capitalize text-slate-500">
                                            {{ str_replace(
                                                '_',
                                                ' ',
                                                $activity->user->role
                                            ) }}
                                        </p>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ ucwords(
                                            str_replace(
                                                '_',
                                                ' ',
                                                $activity->action
                                            )
                                        ) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p class="font-medium text-slate-800">
                                        {{ $subjectLabel }}
                                    </p>

                                    @if ($activity->subject_id)
                                        <p class="mt-1 text-xs text-slate-400">
                                            ID #{{ $activity->subject_id }}
                                        </p>
                                    @endif
                                </td>

                                <td class="min-w-[280px] px-5 py-4 text-sm leading-6 text-slate-700">
                                    {{ $activity->description }}
                                </td>

                                <td class="min-w-[280px] px-5 py-4">
                                    @if (! empty($properties))
                                        <details>
                                            <summary class="cursor-pointer text-sm font-semibold text-indigo-700 hover:text-indigo-900">
                                                View details
                                            </summary>

                                            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                                                <pre class="whitespace-pre-wrap break-words text-xs leading-5 text-slate-600">{{ json_encode(
                                                    $properties,
                                                    JSON_PRETTY_PRINT
                                                    | JSON_UNESCAPED_SLASHES
                                                ) }}</pre>
                                            </div>
                                        </details>
                                    @else
                                        <span class="text-sm text-slate-400">
                                            No extra details
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-14 text-center"
                                >
                                    <p class="font-semibold text-slate-800">
                                        No activity logs found.
                                    </p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Try changing the selected filters.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($activityLogs->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $activityLogs->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
