@extends('graph-mail::graph-mail.layouts.app')

@section('content')
    {{-- Top: status cards --}}
    <section class="grid gap-6 lg:grid-cols-2">
        {{-- Active templates --}}
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
                        {{-- Icon check --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path
                                    d="M16.704 5.29a1 1 0 0 0-1.408-1.42L7.5 11.67 4.707 8.875A1 1 0 0 0 3.293 10.29l3.5 3.5a1 1 0 0 0 1.414 0Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Inactive templates --}}
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
                        {{-- Icon off --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                             fill="currentColor">
                            <path d="M10 2a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm0 16a7 7 0 0 1-6.938-6.059A1 1 0 0 1 4.05 11.9 5 5 0 1 0 10 5a1 1 0 0 1 0-2 7 7 0 1 1 0 14Z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Templates table --}}
    <section class="mt-8">
        <div
                class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70
                   dark:border-gray-800/80 shadow-sm p-4 sm:p-5">

            <div class="flex items-center justify-between gap-2 mb-3">
                <div>
                    <h2 class="font-semibold text-base sm:text-lg mb-1">Email templates</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Manage templates used for sending emails.
                    </p>
                </div>

                <div>
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
            </div>

            {{-- Flash messages --}}
            @if(session('success'))
                <div
                        class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm
                           text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr
                            class="border-b border-gray-100 dark:border-gray-800 text-left text-[11px]
                               uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">Key</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">Name</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">Module</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">Mailable</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">View</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap text-center">Active</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($templates as $template)
                        <tr
                                class="border-b border-gray-50 dark:border-gray-900/60 hover:bg-gray-50/80
                                   dark:hover:bg-gray-900/60 transition">
                            <td class="px-4 sm:px-2 py-2 align-center">
                                <span class="font-mono text-xs bg-gray-100/80 dark:bg-gray-800/80 rounded px-1.5 py-0.5">
                                    {{ $template->key }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-top">
                                <div class="text-gray-800 dark:text-gray-100">
                                    {{ $template->name }}
                                </div>
                                @if($template->default_subject)
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-xs">
                                        {{ $template->default_subject }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-center text-xs text-gray-500 dark:text-gray-400">
                                {{ $template->module ?? '—' }}
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-center text-xs text-gray-500 dark:text-gray-400">
                                <div class="max-w-xs truncate">
                                    {{ $template->mailable_class ?? '—' }}
                                </div>
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-center text-xs text-gray-500 dark:text-gray-400">
                                <div class="max-w-xs truncate">
                                    {{ $template->view ?? '—' }}
                                </div>
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-center text-center">
                                @if($template->active)
                                    <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                               bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100">
                                        Active
                                    </span>
                                @else
                                    <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                               bg-gray-100 text-gray-700 dark:bg-gray-800/80 dark:text-gray-300">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a
                                            href="{{ route('graphmail.templates.edit', $template) }}"
                                            class="inline-flex items-center rounded-lg
                                               bg-sky-500 px-3 py-1.5
                                               text-xs font-semibold text-white
                                               shadow-sm
                                               hover:bg-sky-600
                                               focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-1
                                               dark:bg-sky-400 dark:hover:bg-sky-300 dark:focus:ring-sky-300"
                                    >
                                        Edit
                                    </a>

                                    <form
                                            method="POST"
                                            action="{{ route('graphmail.templates.destroy', $template) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this template?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg
                                                   bg-rose-600 px-3 py-1.5
                                                   text-xs font-semibold text-white
                                                   shadow-sm
                                                   hover:bg-rose-700
                                                   focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1
                                                   dark:bg-rose-500 dark:hover:bg-rose-400 dark:focus:ring-rose-400"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-4 sm:px-2 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                No templates have been defined yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $templates->links() }}
            </div>
        </div>
    </section>
@endsection
