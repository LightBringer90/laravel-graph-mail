@extends('graph-mail::graph-mail.layouts.app')

@section('content')
    @php
        $statusBadge = [
            'queued' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-100',
            'sent'   => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100',
            'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-100',
        ];

        $hasFilters = filled(request('subject'))
            || filled(request('to'))
            || filled(request('sender'))
            || filled(request('status'))
            || filled(request('from_date'))
            || filled(request('to_date'));
    @endphp

    {{-- Page header --}}
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">Mails</h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Browse, filter and inspect mails processed by Graph Mail.
            </p>
        </div>

        @if(method_exists($mails, 'total'))
            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                <span class="font-medium tabular-nums">{{ number_format($mails->total()) }}</span>
                <span class="text-gray-400 dark:text-gray-500">total mails</span>
            </div>
        @endif
    </header>

    {{-- Filters --}}
    <section
            class="mb-6 rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100/80 dark:border-gray-800/80 px-4 sm:px-5 py-3">
            <div>
                <div class="text-[11px] font-semibold tracking-wide uppercase text-gray-500 dark:text-gray-400">
                    Filters
                </div>
                <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                    Narrow down mails by content, recipient, sender or date range.
                </p>
            </div>

            @if($hasFilters)
                <a
                        href="{{ route('graphmail.mails.index') }}"
                        class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    <span>Clear filters</span>
                    <span class="text-gray-400 dark:text-gray-500">&times;</span>
                </a>
            @endif
        </div>

        <form method="GET" class="p-4 sm:p-5 space-y-3">
            <div class="grid gap-3 md:grid-cols-6">
                {{-- Subject --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Subject
                    </label>
                    <input
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                            name="subject"
                            placeholder="Subject contains…"
                            value="{{ request('subject') }}"
                    />
                </div>

                {{-- Recipient --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Recipient
                    </label>
                    <input
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                            name="to"
                            placeholder="Recipient contains…"
                            value="{{ request('to') }}"
                    />
                </div>

                {{-- Sender --}}
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Sender UPN
                    </label>
                    <input
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                            name="sender"
                            placeholder="user@tenant…"
                            value="{{ request('sender') }}"
                    />
                </div>

                {{-- Status --}}
                <div class="md:col-span-1 flex flex-col gap-1">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        Status
                    </label>
                    <select
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                            name="status"
                    >
                        <option value="">Any status</option>
                        @foreach(['queued','sent','failed'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- From date --}}
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        From date
                    </label>
                    <input
                            type="date"
                            name="from_date"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                            value="{{ request('from_date') }}"
                    />
                </div>

                {{-- To date --}}
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                        To date
                    </label>
                    <input
                            type="date"
                            name="to_date"
                            class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                            value="{{ request('to_date') }}"
                    />
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="text-[11px] text-gray-400 dark:text-gray-500">
                    @if($hasFilters)
                        Showing results for current filters.
                    @else
                        No filters applied – showing recent mails.
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50 dark:focus:ring-offset-gray-950"
                    >
                        <span class="mr-1.5">🔍</span>
                        Filter
                    </button>

                    <a
                            href="{{ route('graphmail.mails.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    {{-- Table --}}
    <section
            class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm overflow-hidden">
        <div class="border-b border-gray-100/80 dark:border-gray-800/80 px-4 py-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2">
                <span class="font-medium text-gray-700 dark:text-gray-100">Mail list</span>
                @if(method_exists($mails, 'total'))
                    <span class="text-gray-400 dark:text-gray-500">
                        • {{ number_format($mails->total()) }} items
                    </span>
                @endif
            </div>
            <div class="hidden sm:block text-[11px] text-gray-400 dark:text-gray-500">
                Click an ID to view full mail details.
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b border-gray-100 bg-gray-50/80 text-left text-[11px] uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Subject</th>
                    <th class="px-4 py-2">Template</th>
                    <th class="px-4 py-2">Sender</th>
                    <th class="px-4 py-2">To</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 whitespace-nowrap">Created</th>
                </tr>
                </thead>
                <tbody>
                @forelse($mails as $m)
                    <tr class="border-b border-gray-50 dark:border-gray-900/70 hover:bg-gray-50/80 dark:hover:bg-gray-900/70 transition">
                        <td class="px-4 py-2 align-top whitespace-nowrap">
                            <a class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                               href="{{ route('graphmail.mails.show',$m) }}">#{{ $m->id }}</a>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <div class="max-w-xs md:max-w-sm truncate text-gray-800 dark:text-gray-100">
                                {{ $m->subject }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top text-[11px] font-mono text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $m->template_key ?? '—' }}
                        </td>
                        <td class="px-4 py-2 align-top text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap">
                            {{ $m->sender_upn }}
                        </td>
                        <td class="px-4 py-2 align-top">
                            @php
                                $toList   = is_array($m->to_recipients) ? $m->to_recipients : [];
                                $visible  = array_slice($toList, 0, 2);
                                $hidden   = array_slice($toList, 2);
                            @endphp

                            <div
                                    class="flex flex-wrap gap-1 max-w-xs items-center"
                                    x-data="{ showAll: false }"
                            >
                                {{-- first recipients --}}
                                @forelse($visible as $r)
                                    <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700
                                               dark:bg-gray-800 dark:text-gray-200"
                                    >
                                        {{ $r }}
                                    </span>
                                @empty
                                    <span class="text-[11px] text-gray-400 italic">
                                        none
                                    </span>
                                @endforelse

                                {{-- extra recipients --}}
                                @foreach($hidden as $r)
                                    <span
                                            x-show="showAll"
                                            x-cloak
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700
                                               dark:bg-gray-800 dark:text-gray-200"
                                    >
                                        {{ $r }}
                                    </span>
                                @endforeach

                                {{-- toggle button --}}
                                @if(count($hidden))
                                    <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-dashed border-gray-300 bg-transparent px-2 py-0.5 text-[10px] font-medium text-gray-500 hover:bg-gray-100
                                               dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                                            x-on:click="showAll = !showAll"
                                    >
                                        <span x-show="!showAll">+ {{ count($hidden) }} more</span>
                                        <span x-show="showAll" x-cloak>Show less</span>
                                    </button>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-2 align-top whitespace-nowrap">
                            @php
                                $badgeClass = $statusBadge[$m->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                {{ ucfirst($m->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 align-top text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $m->created_at->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No mails match the current filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination + per-page --}}
        <div class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900/70 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-2">
                @if(method_exists($mails, 'firstItem') && $mails->count())
                    <span class="tabular-nums">
                        Showing {{ $mails->firstItem() }}–{{ $mails->lastItem() }}
                        of {{ $mails->total() }}
                    </span>
                @else
                    <span>No results.</span>
                @endif>
            </div>

            <div class="flex flex-wrap items-center gap-3 sm:gap-4 justify-between sm:justify-end w-full sm:w-auto">
                {{-- Per page selector tied to table --}}
                <form method="GET" class="flex items-center gap-1 text-[11px]">
                    {{-- keep existing filters when changing per_page --}}
                    @foreach(request()->except('per_page', 'page') as $name => $value)
                        @if(is_array($value))
                            @foreach($value as $v)
                                <input type="hidden" name="{{ $name }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <span class="text-gray-400 dark:text-gray-500">Rows per page</span>
                    <select
                            name="per_page"
                            class="rounded-lg border border-gray-200 bg-white px-2 py-1 text-[11px] text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            onchange="this.form.submit()"
                    >
                        @foreach([10,20,50,100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>
                                {{ $size }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="text-right">
                    {{ $mails->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection
