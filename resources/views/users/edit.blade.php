@extends('layouts.app')

@section('page-title', 'Edit User')

@section('content')
    <div class="space-y-6">
        <div>
            <a
                href="{{ route('users.index') }}"
                class="text-sm font-semibold text-slate-500 hover:text-slate-800"
            >
                ← Back to Users
            </a>

            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                <h3 class="text-2xl font-semibold text-slate-950">
                    Edit {{ $user->name }}
                </h3>

                @if ($user->id === auth()->id())
                    <span class="w-fit rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                        Your Account
                    </span>
                @endif
            </div>

            <p class="mt-2 max-w-2xl text-sm text-slate-500">
                Update account information, system role, status, or password.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
                <p class="font-semibold text-rose-900">
                    Please correct the following:
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-rose-800">
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Account Information
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Changes to role and status affect the user's system access.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('users.update', $user) }}"
                class="p-6"
            >
                @csrf
                @method('PUT')

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            for="name"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Full Name
                        </label>

                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
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
                            for="email"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Email Address
                        </label>

                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >

                        @error('email')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="role"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Role
                        </label>

                        <select
                            id="role"
                            name="role"
                            required
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option
                                value="staff"
                                @selected(old('role', $user->role) === 'staff')
                            >
                                Staff
                            </option>

                            <option
                                value="admin"
                                @selected(old('role', $user->role) === 'admin')
                            >
                                Admin
                            </option>
                        </select>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Admin accounts may access administration,
                            audit logs, reports, inventory controls,
                            and user management.
                        </p>

                        @error('role')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="status"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Account Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option
                                value="active"
                                @selected(old('status', $user->status) === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(old('status', $user->status) === 'inactive')
                            >
                                Inactive
                            </option>
                        </select>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Inactive users remain in the audit history but
                            should no longer perform warehouse operations.
                        </p>

                        @error('status')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="my-7 border-t border-slate-200"></div>

                <div>
                    <div>
                        <h3 class="font-semibold text-slate-950">
                            Change Password
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Leave both fields blank if the current password
                            should remain unchanged.
                        </p>
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label
                                for="password"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                New Password
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Leave blank to keep current password"
                                class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                            >

                            @error('password')
                                <p class="mt-2 text-sm text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="password_confirmation"
                                class="block text-sm font-semibold text-slate-700"
                            >
                                Confirm New Password
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                placeholder="Repeat new password"
                                class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                            >
                        </div>
                    </div>
                </div>

                @if ($user->id === auth()->id())
                    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-semibold text-amber-900">
                            Your Administrator Account
                        </p>

                        <p class="mt-1 text-sm leading-6 text-amber-800">
                            The system protects you from deactivating this
                            account or removing your own Admin role while
                            you are signed in.
                        </p>
                    </div>
                @endif

                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-800">
                        Audit Notice
                    </p>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Account changes are recorded in Activity Logs with
                        their previous and updated values. Password contents
                        are never recorded.
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                    <a
                        href="{{ route('users.index') }}"
                        class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
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
        </section>
    </div>
@endsection