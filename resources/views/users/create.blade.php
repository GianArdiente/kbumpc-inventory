@extends('layouts.app')

@section('page-title', 'Create User')

@section('content')
    <div class="space-y-6">
        <div>
            <a
                href="{{ route('users.index') }}"
                class="text-sm font-semibold text-slate-500 hover:text-slate-800"
            >
                ← Back to Users
            </a>

            <h3 class="mt-3 text-2xl font-semibold text-slate-950">
                Create User
            </h3>

            <p class="mt-2 max-w-2xl text-sm text-slate-500">
                Create a new system account and assign its access role and account status.
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
                    Enter the user's login information and access level.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('users.store') }}"
                class="p-6"
            >
                @csrf

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
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Example: Juan Dela Cruz"
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
                            value="{{ old('email') }}"
                            required
                            autocomplete="email"
                            placeholder="user@example.com"
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
                            <option value="">
                                Select role
                            </option>

                            <option
                                value="staff"
                                @selected(old('role') === 'staff')
                            >
                                Staff
                            </option>

                            <option
                                value="admin"
                                @selected(old('role') === 'admin')
                            >
                                Admin
                            </option>
                        </select>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Admin users can manage inventory, requests, reports,
                            audit logs, and user accounts. Staff access remains
                            limited to permitted warehouse operations.
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
                                @selected(old('status', 'active') === 'active')
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(old('status') === 'inactive')
                            >
                                Inactive
                            </option>
                        </select>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Inactive accounts should not be allowed to perform
                            protected warehouse actions.
                        </p>

                        @error('status')
                            <p class="mt-2 text-sm text-rose-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="password"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Password
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Minimum 8 characters"
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
                            Confirm Password
                        </label>

                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Repeat password"
                            class="mt-2 w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                        >
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm font-semibold text-slate-800">
                        Audit Notice
                    </p>

                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        Creating this account will be recorded in the Activity Logs,
                        including the new user's name, email, role, and status.
                        Passwords are never stored in the audit trail.
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
                        Create User
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection