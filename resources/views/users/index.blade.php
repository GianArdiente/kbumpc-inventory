@extends('layouts.app')

@section('page-title', 'Users')

@section('content')
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

                <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">
                                Administration
                            </p>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            User Management
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Manage warehouse system accounts, roles,
                            and account status.
                        </p>
                    </div>

                    <a
                        href="{{ route('users.create') }}"
                        class="kbumpc-btn-primary"
                    >
                        Create User
                    </a>
                </div>
            </div>
        </section>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- User Statistics --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

            {{-- Total --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-medium text-slate-500">
                    Total Users
                </p>

                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                    {{ number_format(
                        $statistics['total_users']
                    ) }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    All system accounts
                </p>
            </div>

            {{-- Active --}}
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-medium text-emerald-800">
                    Active
                </p>

                <p class="mt-3 text-3xl font-semibold tracking-tight text-emerald-950">
                    {{ number_format(
                        $statistics['active_users']
                    ) }}
                </p>

                <p class="mt-2 text-xs text-emerald-700">
                    Can access the system
                </p>
            </div>

            {{-- Inactive --}}
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-medium text-slate-600">
                    Inactive
                </p>

                <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                    {{ number_format(
                        $statistics['inactive_users']
                    ) }}
                </p>

                <p class="mt-2 text-xs text-slate-500">
                    Access currently blocked
                </p>
            </div>

            {{-- Admins --}}
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-medium text-blue-800">
                    Admins
                </p>

                <p class="mt-3 text-3xl font-semibold tracking-tight text-blue-950">
                    {{ number_format(
                        $statistics['admin_users']
                    ) }}
                </p>

                <p class="mt-2 text-xs text-blue-700">
                    Administrative accounts
                </p>
            </div>

            {{-- Staff --}}
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-medium text-sky-800">
                    Staff
                </p>

                <p class="mt-3 text-3xl font-semibold tracking-tight text-sky-950">
                    {{ number_format(
                        $statistics['staff_users']
                    ) }}
                </p>

                <p class="mt-2 text-xs text-sky-700">
                    Warehouse staff accounts
                </p>
            </div>
        </section>

        {{-- User List --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            {{-- Search & Filter --}}
            <div class="border-b border-slate-200 p-5 sm:p-6">
                <div>
                    <h3 class="font-semibold text-slate-950">
                        Search & Filter
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Find users by name or email, or filter by role and status.
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('users.index') }}"
                    class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4"
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
                            placeholder="Search name or email..."
                            class="kbumpc-input mt-2"
                        >
                    </div>

                    <div>
                        <label
                            for="role"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            class="kbumpc-input mt-2"
                        >
                            <option value="">
                                All roles
                            </option>

                            <option
                                value="admin"
                                @selected(
                                    ($filters['role'] ?? '')
                                    === 'admin'
                                )
                            >
                                Admin
                            </option>

                            <option
                                value="staff"
                                @selected(
                                    ($filters['role'] ?? '')
                                    === 'staff'
                                )
                            >
                                Staff
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="status"
                            class="block text-xs font-semibold uppercase tracking-wide text-slate-400"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="kbumpc-input mt-2"
                        >
                            <option value="">
                                All statuses
                            </option>

                            <option
                                value="active"
                                @selected(
                                    ($filters['status'] ?? '')
                                    === 'active'
                                )
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(
                                    ($filters['status'] ?? '')
                                    === 'inactive'
                                )
                            >
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-3 md:col-span-2 xl:col-span-4">
                        <button
                            type="submit"
                            class="kbumpc-btn-primary"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('users.index') }}"
                            class="kbumpc-btn-outline"
                        >
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Users Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="kbumpc-table-head">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                User
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Email
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Role
                            </th>

                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="hidden xl:table-cell px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Created
                            </th>

                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $user)
                            <tr class="kbumpc-table-row">

                                {{-- User --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="
                                                flex h-10 w-10 shrink-0 items-center
                                                justify-center rounded-full text-sm
                                                font-semibold
                                                {{ $user->status === 'active'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-slate-200 text-slate-600' }}
                                            "
                                        >
                                            {{ strtoupper(
                                                substr(
                                                    $user->name,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900">
                                                {{ $user->name }}
                                            </p>

                                            @if (
                                                $user->id
                                                === auth()->id()
                                            )
                                                <span class="mt-1 inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                                    Your account
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Email --}}
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                    {{ $user->email }}
                                </td>

                                {{-- Role --}}
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($user->role === 'admin')
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                                            Admin
                                        </span>
                                    @else
                                        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                                            Staff
                                        </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="whitespace-nowrap px-4 py-3">
                                    @if ($user->status === 'active')
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                {{-- Created --}}
                                <td class="hidden xl:table-cell whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                    {{ $user->created_at?->format(
                                        'M d, Y'
                                    ) ?? '—' }}
                                </td>

                                {{-- Action --}}
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    <a
                                        href="{{ route(
                                            'users.edit',
                                            $user
                                        ) }}"
                                        class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900"
                                    >
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-14 text-center"
                                >
                                    <div class="mx-auto max-w-sm">
                                        <p class="font-semibold text-slate-800">
                                            No users found.
                                        </p>

                                        <p class="mt-2 text-sm text-slate-500">
                                            Try changing the selected filters.
                                        </p>

                                        <a
                                            href="{{ route(
                                                'users.create'
                                            ) }}"
                                            class="kbumpc-btn-primary mt-5"
                                        >
                                            Create User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection