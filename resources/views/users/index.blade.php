@extends('layouts.app')

@section('page-title', 'Users')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <h3 class="text-2xl font-semibold text-slate-950">
                    User Management
                </h3>

                <p class="mt-2 max-w-3xl text-sm text-slate-500">
                    Manage warehouse system accounts, roles, and account status.
                </p>
            </div>

            <a
                href="{{ route('users.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
                Create User
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">
                    Total Users
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($statistics['total_users']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">
                    Active
                </p>

                <p class="mt-3 text-3xl font-semibold text-emerald-950">
                    {{ number_format($statistics['active_users']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-600">
                    Inactive
                </p>

                <p class="mt-3 text-3xl font-semibold text-slate-950">
                    {{ number_format($statistics['inactive_users']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-800">
                    Admins
                </p>

                <p class="mt-3 text-3xl font-semibold text-indigo-950">
                    {{ number_format($statistics['admin_users']) }}
                </p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-sky-800">
                    Staff
                </p>

                <p class="mt-3 text-3xl font-semibold text-sky-950">
                    {{ number_format($statistics['staff_users']) }}
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
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
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
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">
                                All roles
                            </option>

                            <option
                                value="admin"
                                @selected(($filters['role'] ?? '') === 'admin')
                            >
                                Admin
                            </option>

                            <option
                                value="staff"
                                @selected(($filters['role'] ?? '') === 'staff')
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
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="">
                                All statuses
                            </option>

                            <option
                                value="active"
                                @selected(($filters['status'] ?? '') === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(($filters['status'] ?? '') === 'inactive')
                            >
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="flex gap-3 md:col-span-2 xl:col-span-4">
                        <button
                            type="submit"
                            class="rounded-xl bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('users.index') }}"
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
                                User
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Email
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Role
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Status
                            </th>

                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Created
                            </th>

                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">
                                            {{ strtoupper(
                                                substr(
                                                    $user->name,
                                                    0,
                                                    1
                                                )
                                            ) }}
                                        </div>

                                        <div>
                                            <p class="font-semibold text-slate-900">
                                                {{ $user->name }}
                                            </p>

                                            @if ($user->id === auth()->id())
                                                <p class="mt-1 text-xs font-medium text-indigo-600">
                                                    Your account
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $user->email }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @if ($user->role === 'admin')
                                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                                            Admin
                                        </span>
                                    @else
                                        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-800">
                                            Staff
                                        </span>
                                    @endif
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
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

                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                    {{ $user->created_at?->format(
                                        'M d, Y'
                                    ) ?? '—' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <a
                                        href="{{ route(
                                            'users.edit',
                                            $user
                                        ) }}"
                                        class="text-sm font-semibold text-indigo-700 hover:text-indigo-900"
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
                                    <p class="font-semibold text-slate-800">
                                        No users found.
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

            @if ($users->hasPages())
                <div class="border-t border-slate-200 px-5 py-4">
                    {{ $users->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection