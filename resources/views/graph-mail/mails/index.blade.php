@extends('graph-mail::graph-mail.layouts.app')

@section('content')
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

    {{-- Dynamic filters based on columns --}}
    <x-graph-mail::table-filters
            :columns="$mailTableColumns"
            reset-route="{{ route('graphmail.mails.index') }}"
    />

    {{-- Dynamic table --}}
    <x-graph-mail::table
            :data="$mails"
            :columns="$mailTableColumns"
            title="Mail list"
            subtitle="Click an ID to view full mail details."
    />
@endsection
