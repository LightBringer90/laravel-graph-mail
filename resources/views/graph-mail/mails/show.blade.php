@extends('graph-mail::graph-mail.layouts.app')

@section('content')
    {{-- Header --}}
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold tracking-tight flex items-center flex-wrap gap-2">
                <span>Mail #{{ $mail->id }}</span>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium {{ $badgeClass }}">
                    {{ ucfirst($mail->status) }}
                </span>
            </h1>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Detailed view of this Graph Mail message, including HTML body, recipients, attachments and metadata.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <div class="inline-flex items-center rounded-full border border-gray-200 bg-white px-3 py-1 dark:border-gray-800 dark:bg-gray-950 shadow-sm">
                <span class="font-medium tabular-nums">
                    {{ $mail->created_at->format('Y-m-d H:i') }}
                </span>
                <span class="mx-1 text-gray-300 dark:text-gray-600">•</span>
                <span>Created</span>
            </div>
            <a
                    href="{{ route('graphmail.mails.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-[11px] font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                ← Back to list
            </a>
        </div>
    </header>

    <div class="grid md:grid-cols-3 gap-4 lg:gap-6">
        {{-- Left: subject + HTML body preview --}}
        <section
                class="md:col-span-2 rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm p-4 sm:p-5">
            <div class="mb-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                            Subject
                        </div>
                        <div class="mt-1 text-sm sm:text-base font-semibold text-gray-900 dark:text-gray-100 break-words">
                            {{ $mail->subject ?: '— no subject —' }}
                        </div>
                    </div>

                    <div class="hidden sm:flex flex-col items-end text-[11px] text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 dark:bg-gray-800">
                            ID: {{ $mail->id }}
                        </span>
                        @if($mail->template_key)
                            <span class="mt-1 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-100">
                                Template: {{ $mail->template_key }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-2 mb-2">
                <div>
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Body (HTML)
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Rendered in a sandboxed frame. The actual HTML comes from your template / payload.
                    </p>
                </div>
                <span class="hidden sm:inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    Preview only
                </span>
            </div>

            <div class="mt-3 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-gray-50 dark:bg-gray-50">
                <iframe
                        class="w-full h-96 md:h-[28rem] border-0"
                        srcdoc="{{ htmlspecialchars($mail->html_body ?? '') }}"
                ></iframe>
            </div>
        </section>

        {{-- Right: meta + recipients + attachments + NDR --}}
        {{-- Right: meta + recipients + attachments + NDR --}}
        <aside
                class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm p-4 sm:p-5 text-sm space-y-6">

            {{-- 1) STATUS --}}
            <section>
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                            Status
                        </div>
                        <span class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                    {{ ucfirst($mail->status) }}
                </span>
                    </div>

                    <div class="text-right text-[11px] text-gray-500 dark:text-gray-400 space-y-0.5">
                        <div class="uppercase tracking-wide">
                            Sent at
                        </div>
                        <div class="font-mono">
                            {{ optional($mail->sent_at)->format('Y-m-d H:i') ?? 'n/a' }}
                        </div>
                    </div>
                </div>
            </section>

            {{-- 2) SENDER / TEMPLATE --}}
            <section class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                <div>
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Sender
                    </div>
                    <div
                            class="mt-1 inline-flex max-w-full items-center rounded-lg bg-gray-100/80 px-2.5 py-1.5 text-xs sm:text-sm font-medium text-gray-900
                       dark:bg-gray-800 dark:text-gray-100 break-all">
                        {{ $mail->sender_upn }}
                    </div>
                </div>

                <div>
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Template key
                    </div>
                    <div
                            class="mt-1 inline-flex max-w-full items-center rounded-lg bg-indigo-500/10 px-2.5 py-1.5 text-[11px] sm:text-xs font-medium text-indigo-700
                       dark:bg-indigo-500/20 dark:text-indigo-100 font-mono break-all">
                        {{ $mail->template_key ?? '—' }}
                    </div>
                </div>
            </section>

            {{-- 3) RECIPIENTS --}}
            <section class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Recipients
                    </div>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">
                To / Cc / Bcc
            </span>
                </div>

                {{-- this controls vertical space between TO / CC / BCC --}}
                <div class="space-y-3">
                    @foreach($recipientGroups as $label => $list)
                        <div
                                class="rounded-xl border border-gray-100 bg-gray-50/80 px-3 py-3
                           dark:border-gray-800 dark:bg-gray-900/80">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="text-[11px] font-semibold text-gray-600 dark:text-gray-300 tracking-wide">
                                    {{ strtoupper($label) }}
                                </div>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">
                            {{ is_array($list) ? count($list) : 0 }} recipient(s)
                        </span>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                @forelse($list as $r)
                                    <span
                                            class="inline-flex items-center rounded-full border border-gray-200 bg-white px-2 py-0.5 text-[11px] text-gray-800
                                       dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                                {{ $r }}
                            </span>
                                @empty
                                    <span class="text-[11px] text-gray-400 italic">
                                none
                            </span>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- 4) ATTACHMENTS --}}
            <section class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wide">
                        Attachments
                    </div>
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">
                {{ count($attachments) }} file(s)
            </span>
                </div>

                @if(count($attachments))
                    <ul class="space-y-2 text-[11px]">
                        @foreach($attachments as $attachment)
                            <li
                                    class="flex items-start gap-2 rounded-lg border border-gray-100 bg-gray-50/80 px-2.5 py-1.5
                               dark:border-gray-800 dark:bg-gray-900/80">
                                <div class="mt-0.5 text-xs">📎</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="truncate font-medium text-gray-800 dark:text-gray-100">
                                            {{ $attachment['filename'] }}
                                        </div>
                                        <div class="shrink-0 text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ $attachment['size_human'] }}
                                        </div>
                                    </div>

                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[10px] text-gray-400 dark:text-gray-500">
                                        @if(!empty($attachment['mime']))
                                            <span class="uppercase">{{ $attachment['mime'] }}</span>
                                        @endif

                                        @if(!empty($attachment['path']))
                                            <span class="hidden sm:inline text-gray-300 dark:text-gray-700">•</span>
                                            <span class="hidden sm:inline break-all">
                                        {{ $attachment['path'] }}
                                    </span>
                                        @endif
                                    </div>

                                    @if(!empty($attachment['path']))
                                        <div class="mt-1">
                                            <a
                                                    href="{{ $attachment['url'] }}"
                                                    class="inline-flex items-center text-[10px] font-medium text-indigo-600 hover:text-indigo-700 hover:underline dark:text-indigo-300 dark:hover:text-indigo-200"
                                                    target="_blank" rel="noopener noreferrer"
                                            >
                                                Download
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-[11px] text-gray-400 italic">
                        No attachments.
                    </p>
                @endif
            </section>

            @if($mail->status === 'failed')
                <section class="pt-4 border-t border-gray-100 dark:border-gray-800 space-y-2">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <div class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wide">
                            NDR (Non-delivery report)
                        </div>
                        <span
                                class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700
                           dark:bg-rose-900/40 dark:text-rose-100">
                    Failed
                </span>
                    </div>

                    <p class="text-[11px] text-gray-500 dark:text-gray-400">
                        Delivery failed. Surface your NDR / diagnostic info here if you store it
                        (e.g. Graph API error, SMTP code, etc.).
                    </p>
                </section>
            @endif
        </aside>

    </div>
@endsection
