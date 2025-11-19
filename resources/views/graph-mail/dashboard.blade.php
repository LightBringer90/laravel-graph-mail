@extends('graph-mail::graph-mail.layouts.app')

@section('content')
    @php
        $statusCards = [
            [
                'label' => 'Queued',
                'key'   => 'queued',
                'classes' => 'bg-amber-50/80 border border-amber-100 text-amber-900 dark:bg-amber-950/50 dark:border-amber-900 dark:text-amber-100'
            ],
            [
                'label' => 'Sent',
                'key'   => 'sent',
                'classes' => 'bg-emerald-50/80 border border-emerald-100 text-emerald-900 dark:bg-emerald-950/50 dark:border-emerald-900 dark:text-emerald-100'
            ],
            [
                'label' => 'Failed',
                'key'   => 'failed',
                'classes' => 'bg-rose-50/80 border border-rose-100 text-rose-900 dark:bg-rose-950/50 dark:border-rose-900 dark:text-rose-100'
            ],
        ];

        $statusBadge = [
            'queued' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-100',
            'sent'   => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100',
            'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-100',
        ];
    @endphp

    {{-- Top stats --}}
    <section class="grid gap-4 md:grid-cols-3">
        @foreach($statusCards as $card)
            <div
                    class="relative overflow-hidden rounded-2xl px-4 py-4 sm:px-5 sm:py-5 shadow-sm bg-gradient-to-br from-white/90 via-white/70 to-white/60 dark:from-gray-950/90 dark:via-gray-950/60 dark:to-gray-900/60 border border-gray-100/70 dark:border-gray-800/80"
                    data-status-card="{{ $card['key'] }}"
            >
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-30 blur-xl
                    @if($card['key']==='queued') bg-amber-300/60 dark:bg-amber-500/40
                    @elseif($card['key']==='sent') bg-emerald-300/60 dark:bg-emerald-500/40
                    @else bg-rose-300/60 dark:bg-rose-500/40 @endif"></div>

                <div class="relative flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ $card['label'] }}
                        </p>
                        <p class="mt-1 text-3xl font-semibold tabular-nums js-count">
                            {{ $counts[$card['key']] ?? 0 }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Last known total
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl {{ $card['classes'] }}">
                        @if($card['key'] === 'queued')
                            {{-- Clock icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 2a8 8 0 1 0 8 8 8.009 8.009 0 0 0-8-8Zm.75 4.5a.75.75 0 0 0-1.5 0v3.25c0 .199.079.39.22.53l2.25 2.25a.75.75 0 1 0 1.06-1.06L10.75 9.44Z"/>
                            </svg>
                        @elseif($card['key'] === 'sent')
                            {{-- Check icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M16.704 5.29a1 1 0 0 0-1.408-1.42L7.5 11.67 4.707 8.875A1 1 0 0 0 3.293 10.29l3.5 3.5a1 1 0 0 0 1.414 0Z"/>
                            </svg>
                        @else
                            {{-- X icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 1 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 1 0-1.06-1.06L10 8.94Z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </section>

    {{-- Last 24 hours chart --}}
    <section class="mt-8">
        <div class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm p-4 sm:p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="font-semibold text-base sm:text-lg">Last 24 hours</h2>
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1"></span>
                    Live snapshot
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Aggregated counts by status over the past day.
            </p>

            <div class="mt-4 h-64">
                <canvas id="last24" class="w-full h-full"></canvas>
            </div>
        </div>
    </section>

    {{-- Recent activity --}}
    <section class="mt-8">
        <div
                class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70
               dark:border-gray-800/80 shadow-sm p-4 sm:p-5">

            <h2 class="font-semibold text-base sm:text-lg mb-1">Recent activity</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Latest mails processed by Graph Mail.
            </p>

            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr
                            class="border-b border-gray-100 dark:border-gray-800 text-left text-[11px]
                           uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-4 sm:px-2 py-2">ID</th>
                        <th class="px-4 sm:px-2 py-2">Subject</th>
                        <th class="px-4 sm:px-2 py-2">Status</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">Created</th>
                        <th class="px-4 sm:px-2 py-2 whitespace-nowrap">Sent At</th>
                    </tr>
                    </thead>
                    <tbody id="recent-tbody">
                    @forelse($recent as $m)
                        <tr
                                class="border-b border-gray-50 dark:border-gray-900/60 hover:bg-gray-50/80
                               dark:hover:bg-gray-900/60 transition">
                            <td class="px-4 sm:px-2 py-2 align-top whitespace-nowrap">
                                <a class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                                   href="{{ route('graphmail.mails.show',$m) }}">#{{ $m->id }}</a>
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-top">
                                <div class="max-w-xs md:max-w-sm truncate text-gray-800 dark:text-gray-100">
                                    {{ $m->subject }}
                                </div>
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-top">
                                @php $badgeClass = $statusBadge[$m->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100'; @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
                                    {{ ucfirst($m->status) }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-2 py-2 align-top text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $m->created_human }}
                            </td>

                            {{-- NEW Sent At column --}}
                            <td class="px-4 sm:px-2 py-2 align-top text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $m->sent_at ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-4 sm:px-2 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                No recent mails recorded yet.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Chart + auto-refresh config --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('last24');
            if (!ctx || typeof Chart === 'undefined') return;

            const last24Chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Queued', 'Sent', 'Failed'],
                    datasets: [{
                        label: 'Count (last 24h)',
                        data: [
                            {{ (int)($last24['queued'] ?? 0) }},
                            {{ (int)($last24['sent'] ?? 0) }},
                            {{ (int)($last24['failed'] ?? 0) }}
                        ],
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            grid: {display: false},
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {precision: 0},
                        }
                    },
                    plugins: {
                        legend: { display: false },
                    }
                }
            });

            async function fetchDashboardData() {
                try {
                    const response = await fetch('{{ route('graphmail.dashboard.data') }}', {
                        headers: { 'Accept': 'application/json' }
                    });

                    if (!response.ok) {
                        console.error('Failed to fetch dashboard data', response.status);
                        return;
                    }

                    const data = await response.json();
                    updateCounts(data.counts || {});
                    updateChart(data.last24 || {});
                    updateRecentTable(data.recent || []);
                } catch (e) {
                    console.error('Error fetching dashboard data', e);
                }
            }

            function updateCounts(counts) {
                Object.keys(counts).forEach(function (status) {
                    const el = document.querySelector('[data-status-card="' + status + '"] .js-count');
                    if (el) el.textContent = counts[status];
                });
            }

            function updateChart(last24) {
                last24Chart.data.datasets[0].data = [
                    parseInt(last24.queued || 0, 10),
                    parseInt(last24.sent || 0, 10),
                    parseInt(last24.failed || 0, 10)
                ];
                last24Chart.update();
            }

            function updateRecentTable(recent) {
                const tbody = document.getElementById('recent-tbody');
                if (!tbody) return;

                if (!recent.length) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5"
                                class="px-4 sm:px-2 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                No recent mails recorded yet.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = recent.map(function (m) {
                    const statusBadgeClass = {
                        queued: 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-100',
                        sent:   'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100',
                        failed: 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-100'
                    }[m.status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';

                    return `
                        <tr class="border-b border-gray-50 dark:border-gray-900/60 hover:bg-gray-50/80 dark:hover:bg-gray-900/60 transition">
                            <td class="px-4 sm:px-2 py-2 align-top whitespace-nowrap">
                                <a class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                                   href="${m.show_url}">#${m.id}</a>
                            </td>

                            <td class="px-4 sm:px-2 py-2 align-top">
                                <div class="max-w-xs md:max-w-sm truncate text-gray-800 dark:text-gray-100">
                                    ${escapeHtml(m.subject ?? '')}
                                </div>
                            </td>

                            <td class="px-4 sm:px-2 py-2 align-top">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${statusBadgeClass}">
                                    ${m.status_label}
                                </span>
                            </td>

                            <td class="px-4 sm:px-2 py-2 align-top text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                ${m.created_human}
                            </td>

                            <td class="px-4 sm:px-2 py-2 align-top text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                ${m.sent_at ?? '—'}
                            </td>
                        </tr>
                    `;
                }).join('');
            }

            function escapeHtml(string) {
                if (string === null || string === undefined) return '';
                return String(string)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            setInterval(fetchDashboardData, 10000);
            fetchDashboardData();
        });
    </script>
@endsection
