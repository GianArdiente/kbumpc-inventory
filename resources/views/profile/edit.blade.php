@extends('layouts.app')

@section('page-title', 'My Profile')

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
                                #075f2f,
                                transparent 70%
                            );
                    "
                ></div>

                <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-600"></span>

                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                Account Settings
                            </p>
                        </div>

                        <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            My Profile
                        </h3>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                            Update your personal account information or change your password.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                            Signed In As
                        </p>

                        <p class="mt-1 font-semibold text-emerald-950">
                            {{ auth()->user()->name }}
                        </p>

                        <p class="mt-1 text-xs capitalize text-emerald-700">
                            {{ str_replace(
                                '_',
                                ' ',
                                auth()->user()->role
                            ) }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Profile Information --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Profile Information
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Update your name and email address.
                </p>
            </div>

            <div class="p-6">
                <div class="max-w-2xl">
                    @include(
                        'profile.partials.update-profile-information-form'
                    )
                </div>
            </div>
        </section>

        {{-- Password --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <h3 class="font-semibold text-slate-950">
                    Change Password
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Use a strong password that is not shared with other accounts.
                </p>
            </div>

            <div class="p-6">
                <div class="max-w-2xl">
                    @include(
                        'profile.partials.update-password-form'
                    )
                </div>
            </div>
        </section>

        {{-- Account Management Note --}}
        <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-sm font-bold text-blue-700">
                    i
                </div>

                <div>
                    <p class="text-sm font-semibold text-blue-950">
                        Account Management
                    </p>

                    <p class="mt-1 max-w-3xl text-sm leading-6 text-blue-800">
                        Account roles and account status are managed by an administrator.
                        User accounts are deactivated instead of permanently deleted so
                        system and activity records remain available.
                    </p>
                </div>
            </div>
        </section>
    </div>
@endsection