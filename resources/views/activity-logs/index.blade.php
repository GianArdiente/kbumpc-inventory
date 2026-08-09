@extends('layouts.app')

@section('page-title', 'Activity Logs')

@section('content')
    <div
        x-data="{
            detailOpen: false,
            selected: null,

            openDetails(data) {
                this.selected = data;
                this.detailOpen = true;
                document.body.classList.add('overflow-hidden');
            },

            closeDetails() {
                this.detailOpen = false;
                document.body.classList.remove('overflow-hidden');

                setTimeout(() => {
                    this.selected = null;
                }, 150);
            },

            label(value) {
                if (! value) {
                    return 'Details';
                }

                return value
                    .replaceAll('_', ' ')
                    .replace(/\b\w/g, character =>
                        character.toUpperCase()
                    );
            },

            displayValue(value) {
                if (
                    value === null
                    || value === undefined
                    || value === ''
                ) {
                    return '—';
                }

                if (typeof value === 'boolean') {
                    return value ? 'Yes' : 'No';
                }

                return value;
            },

            isObject(value) {
                return (
                    value !== null
                    && typeof value === 'object'
                    && ! Array.isArray(value)
                );
            },

            isArray(value) {
                return Array.isArray(value);
            }
        }"
        @keydown.escape.window="
            detailOpen && closeDetails()
        "
    >
        <div class="space-y-6 kbumpc-page-enter">

            {{-- Page Header --}}
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

                    <div class="relative">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">
                                System Audit
                            </p>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            Activity Logs
                        </h3>

                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                            Review inventory changes, request activity,
                            returns, account changes, and other recorded
                            system actions.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Statistics --}}
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Total Logs
                    </p>

                    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                        {{ number_format(
                            $statistics['total_logs']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Recorded system activities
                    </p>
                </div>

                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-sky-800">
                        Activity Today
                    </p>

                    <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-950">
                        {{ number_format(
                            $statistics['today_logs']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-sky-700">
                        Actions recorded today
                    </p>
                </div>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-blue-800">
                        Users in Logs
                    </p>

                    <p class="mt-3 text-3xl font-semibold tracking-tight text-blue-950">
                        {{ number_format(
                            $statistics['users_with_activity']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-blue-700">
                        Users with recorded activity
                    </p>
                </div>

                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <p class="text-sm font-medium text-emerald-800">
                        Action Types
                    </p>

                    <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-950">
                        {{ number_format(
                            $statistics['actions_recorded']
                        ) }}
                    </p>

                    <p class="mt-2 text-xs text-emerald-700">
                        Different recorded actions
                    </p>
                </div>
            </section>

            {{-- Logs --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                {{-- Filters --}}
                <div class="border-b border-slate-200 p-5 sm:p-6">
                    <div>
                        <h3 class="font-semibold text-slate-950">
                            Search & Filter
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Narrow the audit trail by keyword, action,
                            user, or date range.
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
                                class="kbumpc-input mt-2"
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
                                class="kbumpc-input mt-2"
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
                                class="kbumpc-input mt-2"
                            >
                                <option value="">
                                    All users
                                </option>

                                @foreach ($users as $user)
                                    <option
                                        value="{{ $user->id }}"
                                        @selected(
                                            (string) (
                                                $filters['user_id']
                                                ?? ''
                                            )
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
                                class="kbumpc-input mt-2"
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
                                class="kbumpc-input mt-2"
                            >
                        </div>

                        <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-6">
                            <button
                                type="submit"
                                class="kbumpc-btn-primary"
                            >
                                Apply Filters
                            </button>

                            <a
                                href="{{ route(
                                    'activity-logs.index'
                                ) }}"
                                class="kbumpc-btn-outline"
                            >
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="kbumpc-table-head">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Date
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    User
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Action
                                </th>

                                <th class="hidden xl:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Subject
                                </th>

                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Description
                                </th>

                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Details
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($activityLogs as $activity)
                                @php
                                    $subjectType =
                                        $activity->subject_type;

                                    if ($subjectType) {
                                        $parts = explode(
                                            '\\',
                                            $subjectType
                                        );

                                        $subjectLabel =
                                            end($parts);
                                    } else {
                                        $subjectLabel = '—';
                                    }

                                    $properties =
                                        $activity->properties
                                        ?? [];

                                    $modalData = [
                                        'id' =>
                                            $activity->id,

                                        'date' =>
                                            $activity
                                                ->created_at
                                                ->format(
                                                    'M d, Y h:i A'
                                                ),

                                        'user' =>
                                            $activity
                                                ->user?->name
                                            ?? 'System',

                                        'role' =>
                                            $activity->user
                                                ? ucwords(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $activity
                                                            ->user
                                                            ->role
                                                    )
                                                )
                                                : 'System',

                                        'action' =>
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $activity
                                                        ->action
                                                )
                                            ),

                                        'subject' =>
                                            $subjectLabel,

                                        'subject_id' =>
                                            $activity
                                                ->subject_id,

                                        'description' =>
                                            $activity
                                                ->description,

                                        'properties' =>
                                            $properties,
                                    ];
                                @endphp

                                <tr class="kbumpc-table-row align-top">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                        <p class="font-medium text-slate-700">
                                            {{ $activity
                                                ->created_at
                                                ->format(
                                                    'M d, Y'
                                                ) }}
                                        </p>

                                        <p class="mt-1 text-xs text-slate-400">
                                            {{ $activity
                                                ->created_at
                                                ->format(
                                                    'h:i A'
                                                ) }}
                                        </p>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3">
                                        <p class="font-semibold text-slate-900">
                                            {{ $activity
                                                ->user?->name
                                                ?? 'System' }}
                                        </p>

                                        @if ($activity->user)
                                            <p class="mt-1 text-xs capitalize text-slate-500">
                                                {{ str_replace(
                                                    '_',
                                                    ' ',
                                                    $activity
                                                        ->user
                                                        ->role
                                                ) }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                            {{ ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $activity
                                                        ->action
                                                )
                                            ) }}
                                        </span>
                                    </td>

                                    <td class="hidden xl:table-cell px-4 py-3 text-sm text-slate-600">
                                        <p class="font-medium text-slate-800">
                                            {{ $subjectLabel }}
                                        </p>

                                        @if (
                                            $activity->subject_id
                                        )
                                            <p class="mt-1 text-xs text-slate-400">
                                                ID #{{ $activity
                                                    ->subject_id }}
                                            </p>
                                        @endif
                                    </td>

                                    <td class="max-w-xl px-4 py-3 text-sm leading-6 text-slate-700">
                                        {{ $activity
                                            ->description }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right">
                                        @if (! empty($properties))
                                            <button
                                                type="button"
                                                class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900"
                                                @click='openDetails(
                                                    @json($modalData)
                                                )'
                                            >
                                                View Details
                                            </button>
                                        @else
                                            <span class="text-sm text-slate-400">
                                                —
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

        {{-- IMPORTANT:
             Teleport modal outside transformed page content --}}
        <template x-teleport="body">
            <div
                x-show="detailOpen"
                x-cloak
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
            >
                {{-- Backdrop --}}
                <div
                    x-show="detailOpen"
                    x-transition.opacity
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    @click="closeDetails()"
                ></div>

                {{-- Modal --}}
                <div
                    x-show="detailOpen"
                    x-transition:enter="
                        transition
                        ease-out
                        duration-200
                    "
                    x-transition:enter-start="
                        opacity-0
                        scale-95
                    "
                    x-transition:enter-end="
                        opacity-100
                        scale-100
                    "
                    x-transition:leave="
                        transition
                        ease-in
                        duration-150
                    "
                    x-transition:leave-start="
                        opacity-100
                        scale-100
                    "
                    x-transition:leave-end="
                        opacity-0
                        scale-95
                    "
                    class="relative z-10 flex max-h-[calc(100vh-3rem)] w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl"
                    @click.stop
                >
                    {{-- Header --}}
                    <div class="flex shrink-0 items-start justify-between border-b border-slate-200 px-6 py-5">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>

                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-700">
                                    Activity Details
                                </p>
                            </div>

                            <h3
                                class="mt-2 text-2xl font-semibold tracking-tight text-slate-950"
                                x-text="
                                    selected?.action
                                    ?? 'Activity Details'
                                "
                            ></h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                                x-text="
                                    selected?.description
                                    ?? ''
                                "
                            ></p>
                        </div>

                        <button
                            type="button"
                            class="ml-5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-xl text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                            @click="closeDetails()"
                            aria-label="Close activity details"
                        >
                            ×
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="overflow-y-auto p-6">

                        {{-- Main Metadata --}}
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                    Performed By
                                </p>

                                <p
                                    class="mt-2 font-semibold text-slate-900"
                                    x-text="
                                        selected?.user
                                        ?? 'System'
                                    "
                                ></p>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                    x-text="
                                        selected?.role
                                        ?? ''
                                    "
                                ></p>
                            </div>

                            <div class="rounded-2xl bg-blue-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">
                                    Action
                                </p>

                                <p
                                    class="mt-2 font-semibold text-blue-950"
                                    x-text="
                                        selected?.action
                                        ?? '—'
                                    "
                                ></p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">
                                    Subject
                                </p>

                                <p
                                    class="mt-2 font-semibold text-emerald-950"
                                    x-text="
                                        selected?.subject
                                        ?? '—'
                                    "
                                ></p>

                                <p
                                    x-show="
                                        selected?.subject_id
                                    "
                                    class="mt-1 text-xs text-emerald-700"
                                >
                                    ID #
                                    <span
                                        x-text="
                                            selected
                                                ?.subject_id
                                        "
                                    ></span>
                                </p>
                            </div>

                            <div class="rounded-2xl bg-amber-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">
                                    Date & Time
                                </p>

                                <p
                                    class="mt-2 font-semibold text-amber-950"
                                    x-text="
                                        selected?.date
                                        ?? '—'
                                    "
                                ></p>
                            </div>
                        </div>

                        {{-- Before / After --}}
                        <template
                            x-if="
                                selected
                                && selected.properties
                                && (
                                    selected
                                        .properties
                                        .before
                                    || selected
                                        .properties
                                        .after
                                )
                            "
                        >
                            <section class="mt-6">
                                <div class="mb-3">
                                    <h4 class="font-semibold text-slate-950">
                                        Changes
                                    </h4>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Values before and after
                                        this activity.
                                    </p>
                                </div>

                                <div class="grid gap-4 lg:grid-cols-2">

                                    {{-- Before --}}
                                    <div class="overflow-hidden rounded-2xl border border-slate-200">
                                        <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                                Before
                                            </p>
                                        </div>

                                        <div class="grid gap-px bg-slate-200 sm:grid-cols-2">
                                            <template
                                                x-for="
                                                    [key, value]
                                                    in Object.entries(
                                                        selected
                                                            ?.properties
                                                            ?.before
                                                        ?? {}
                                                    )
                                                "
                                                :key="
                                                    'before-'
                                                    + key
                                                "
                                            >
                                                <div class="bg-white p-4">
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                                        x-text="
                                                            label(
                                                                key
                                                            )
                                                        "
                                                    ></p>

                                                    <p
                                                        class="mt-2 break-words text-sm font-medium text-slate-800"
                                                        x-text="
                                                            displayValue(
                                                                value
                                                            )
                                                        "
                                                    ></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- After --}}
                                    <div class="overflow-hidden rounded-2xl border border-emerald-200">
                                        <div class="border-b border-emerald-200 bg-emerald-50 px-4 py-3">
                                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-700">
                                                After
                                            </p>
                                        </div>

                                        <div class="grid gap-px bg-emerald-200 sm:grid-cols-2">
                                            <template
                                                x-for="
                                                    [key, value]
                                                    in Object.entries(
                                                        selected
                                                            ?.properties
                                                            ?.after
                                                        ?? {}
                                                    )
                                                "
                                                :key="
                                                    'after-'
                                                    + key
                                                "
                                            >
                                                <div class="bg-white p-4">
                                                    <p
                                                        class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                                        x-text="
                                                            label(
                                                                key
                                                            )
                                                        "
                                                    ></p>

                                                    <p
                                                        class="mt-2 break-words text-sm font-semibold text-slate-900"
                                                        x-text="
                                                            displayValue(
                                                                value
                                                            )
                                                        "
                                                    ></p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </template>

                        {{-- Additional Details --}}
                        <template
                            x-if="
                                selected
                                && selected.properties
                                && Object.keys(
                                    selected.properties
                                ).filter(
                                    key =>
                                        ! [
                                            'before',
                                            'after'
                                        ].includes(
                                            key
                                        )
                                ).length > 0
                            "
                        >
                            <section class="mt-6">
                                <div class="mb-3">
                                    <h4 class="font-semibold text-slate-950">
                                        Additional Details
                                    </h4>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Information recorded together
                                        with this activity.
                                    </p>
                                </div>

                                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    <template
                                        x-for="
                                            [key, value]
                                            in Object.entries(
                                                selected
                                                    ?.properties
                                                ?? {}
                                            ).filter(
                                                ([key]) =>
                                                    ! [
                                                        'before',
                                                        'after'
                                                    ].includes(
                                                        key
                                                    )
                                            )
                                        "
                                        :key="
                                            'detail-'
                                            + key
                                        "
                                    >
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                            <p
                                                class="text-xs font-semibold uppercase tracking-wide text-slate-400"
                                                x-text="
                                                    label(
                                                        key
                                                    )
                                                "
                                            ></p>

                                            {{-- Scalar --}}
                                            <template
                                                x-if="
                                                    ! isObject(
                                                        value
                                                    )
                                                    && ! isArray(
                                                        value
                                                    )
                                                "
                                            >
                                                <p
                                                    class="mt-2 break-words text-sm font-semibold text-slate-800"
                                                    x-text="
                                                        displayValue(
                                                            value
                                                        )
                                                    "
                                                ></p>
                                            </template>

                                            {{-- Object --}}
                                            <template
                                                x-if="
                                                    isObject(
                                                        value
                                                    )
                                                "
                                            >
                                                <div class="mt-3 space-y-2">
                                                    <template
                                                        x-for="
                                                            [
                                                                childKey,
                                                                childValue
                                                            ]
                                                            in Object.entries(
                                                                value
                                                            )
                                                        "
                                                        :key="
                                                            key
                                                            + '-'
                                                            + childKey
                                                        "
                                                    >
                                                        <div class="flex items-start justify-between gap-4 rounded-xl bg-white px-3 py-2">
                                                            <span
                                                                class="text-xs text-slate-500"
                                                                x-text="
                                                                    label(
                                                                        childKey
                                                                    )
                                                                "
                                                            ></span>

                                                            <span
                                                                class="text-right text-xs font-semibold text-slate-800"
                                                                x-text="
                                                                    isObject(
                                                                        childValue
                                                                    )
                                                                    || isArray(
                                                                        childValue
                                                                    )
                                                                        ? 'Recorded data'
                                                                        : displayValue(
                                                                            childValue
                                                                        )
                                                                "
                                                            ></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- Array --}}
                                            <template
                                                x-if="
                                                    isArray(
                                                        value
                                                    )
                                                "
                                            >
                                                <div class="mt-3 space-y-2">
                                                    <template
                                                        x-for="
                                                            (
                                                                entry,
                                                                index
                                                            )
                                                            in value
                                                        "
                                                        :key="
                                                            key
                                                            + '-'
                                                            + index
                                                        "
                                                    >
                                                        <div class="rounded-xl bg-white px-3 py-2">
                                                            <template
                                                                x-if="
                                                                    ! isObject(
                                                                        entry
                                                                    )
                                                                    && ! isArray(
                                                                        entry
                                                                    )
                                                                "
                                                            >
                                                                <p
                                                                    class="text-xs font-medium text-slate-700"
                                                                    x-text="
                                                                        displayValue(
                                                                            entry
                                                                        )
                                                                    "
                                                                ></p>
                                                            </template>

                                                            <template
                                                                x-if="
                                                                    isObject(
                                                                        entry
                                                                    )
                                                                "
                                                            >
                                                                <div class="grid gap-2 sm:grid-cols-2">
                                                                    <template
                                                                        x-for="
                                                                            [
                                                                                entryKey,
                                                                                entryValue
                                                                            ]
                                                                            in Object.entries(
                                                                                entry
                                                                            )
                                                                        "
                                                                        :key="
                                                                            entryKey
                                                                        "
                                                                    >
                                                                        <div>
                                                                            <p
                                                                                class="text-[11px] font-semibold uppercase tracking-wide text-slate-400"
                                                                                x-text="
                                                                                    label(
                                                                                        entryKey
                                                                                    )
                                                                                "
                                                                            ></p>

                                                                            <p
                                                                                class="mt-1 text-xs font-medium text-slate-700"
                                                                                x-text="
                                                                                    isObject(
                                                                                        entryValue
                                                                                    )
                                                                                    || isArray(
                                                                                        entryValue
                                                                                    )
                                                                                        ? 'Recorded data'
                                                                                        : displayValue(
                                                                                            entryValue
                                                                                        )
                                                                                "
                                                                            ></p>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </section>
                        </template>
                    </div>

                    {{-- Footer --}}
                    <div class="flex shrink-0 items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <p class="text-xs text-slate-500">
                            Activity log records are maintained
                            for audit history.
                        </p>

                        <button
                            type="button"
                            class="kbumpc-btn-primary"
                            @click="closeDetails()"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection