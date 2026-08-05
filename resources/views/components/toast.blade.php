@if (session('status'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 3800)"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-6 opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="translate-x-6 opacity-0"
        class="fixed right-4 top-4 z-[100] w-[calc(100%-2rem)] max-w-md rounded-2xl border border-emerald-200 bg-white p-4 shadow-xl sm:right-6 sm:top-6"
        role="status"
    >
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                ✓
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-950">
                    Success
                </p>

                <p class="mt-1 text-sm leading-6 text-slate-600">
                    {{ session('status') }}
                </p>
            </div>

            <button
                type="button"
                @click="show = false"
                class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                aria-label="Close notification"
            >
                ×
            </button>
        </div>
    </div>
@endif