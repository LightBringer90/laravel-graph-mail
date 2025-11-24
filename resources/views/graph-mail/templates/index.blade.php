@extends('graph-mail::graph-mail.layouts.app')

@section('content')
    {{-- Page header --}}
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">
                Email templates
            </h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Manage templates used for sending emails.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            @if($total ?? false)
                <div
                        class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 shadow-sm">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    <span class="font-medium tabular-nums">{{ number_format($total) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">total templates</span>
                </div>
            @endif

            <a href="{{ route('graphmail.templates.create') }}"
               class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-xs sm:text-sm font-medium
                      bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm
                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20"
                     fill="currentColor">
                    <path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1Z"/>
                </svg>
                Add template
            </a>
        </div>
    </header>

    {{-- Status cards --}}
    <section class="grid gap-6 lg:grid-cols-2 mb-6">
        {{-- Active --}}
        <div>
            <div
                    class="relative overflow-hidden rounded-2xl px-4 py-4 sm:px-5 sm:py-5 shadow-sm
                       bg-gradient-to-br from-white/90 via-white/70 to-white/60
                       dark:from-gray-950/90 dark:via-gray-950/60 dark:to-gray-900/60
                       border border-gray-100/70 dark:border-gray-800/80">
                <div
                        class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-30 blur-xl
                           bg-emerald-300/60 dark:bg-emerald-500/40">
                </div>

                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Active
                        </p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums">
                            {{ $activeCount }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Active and usable templates.
                        </p>
                    </div>

                    <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl
                               bg-emerald-50/80 border border-emerald-100 text-emerald-900
                               dark:bg-emerald-950/50 dark:border-emerald-900 dark:text-emerald-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path
                                    d="M16.704 5.29a1 1 0 0 0-1.408-1.42L7.5 11.67 4.707 8.875A1 1 0 0 0 3.293 10.29l3.5 3.5a1 1 0 0 0 1.414 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inactive --}}
        <div>
            <div
                    class="relative overflow-hidden rounded-2xl px-4 py-4 sm:px-5 sm:py-5 shadow-sm
                       bg-gradient-to-br from-white/90 via-white/70 to-white/60
                       dark:from-gray-950/90 dark:via-gray-950/60 dark:to-gray-900/60
                       border border-gray-100/70 dark:border-gray-800/80">
                <div
                        class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-30 blur-xl
                           bg-rose-300/60 dark:bg-rose-500/40">
                </div>

                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            Inactive
                        </p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums">
                            {{ $inactiveCount }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Deactivated templates, they will not be used when sending.
                        </p>
                    </div>

                    <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl
                               bg-rose-50/80 border border-rose-100 text-rose-900
                               dark:bg-rose-950/50 dark:border-rose-900 dark:text-rose-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path d="M10 2a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm0 16a7 7 0 0 1-6.938-6.059A1 1 0 0 1 4.05 11.9 5 5 0 1 0 10 5a1 1 0 0 1 0-2 7 7 0 1 1 0 14Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Flash messages --}}
    @if(session('success'))
        <div
                class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm
                   text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-100">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div
                class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm
                   text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/60 dark:text-amber-100">
            {{ session('warning') }}
        </div>
    @endif

    {{-- Dynamic filters --}}
    <x-graph-mail::table.table-filters
            :columns="$templateTableColumns"
            reset-route="{{ route('graphmail.templates.index') }}"
    />

    {{-- Dynamic table --}}
    <section class="mt-4">
        <x-graph-mail::table.table
                :data="$templates"
                :columns="$templateTableColumns"
                title="Email templates"
                subtitle="Manage templates used for sending emails."
        />
    </section>
@endsection
