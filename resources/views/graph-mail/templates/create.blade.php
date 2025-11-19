@extends('graph-mail::graph-mail.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold tracking-tight">
                    {{ $isEdit ? 'Edit email template' : 'Create email template' }}
                </h1>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $isEdit
                        ? 'Modify the configuration of an existing template, including default data and its associated view.'
                        : 'Define a new template from scratch, with details, view, and default data.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 text-xs text-gray-500 dark:text-gray-400">
                @if($isEdit)
                    <div
                            class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1
                               dark:border-gray-800 dark:bg-gray-950 shadow-sm">
                        <span class="text-[10px] uppercase tracking-wide mr-1 text-gray-400 dark:text-gray-500">
                            Key
                        </span>
                        <span class="font-mono text-xs text-gray-800 dark:text-gray-100">
                            {{ $template->key }}
                        </span>
                    </div>
                @endif

                <a
                        href="{{ route('graphmail.templates.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-1.5
                           text-[11px] sm:text-xs font-medium text-gray-600 hover:bg-gray-100
                           dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    ← Back to list
                </a>
            </div>
        </header>

        {{-- Form body --}}
        <form
                method="POST"
                action="{{ $isEdit ? route('graphmail.templates.update', $template) : route('graphmail.templates.store') }}"
                class="space-y-6"
        >
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            @include('graph-mail::graph-mail.templates._form', ['template' => $template])
        </form>
    </div>
@endsection
