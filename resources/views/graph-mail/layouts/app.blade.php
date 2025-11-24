<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Graph Mail</title>

    {{-- Early theme bootstrap to avoid flicker --}}
    <script>
        (function () {
            const storageKey = 'graphmail-theme';

            function getInitialTheme() {
                try {
                    const stored = localStorage.getItem(storageKey);
                    if (stored === 'light' || stored === 'dark') {
                        return stored;
                    }
                } catch (e) {
                    // ignore storage issues
                }

                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return 'dark';
                }

                return 'light';
            }

            const theme = getInitialTheme();
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // expose for later scripts (optional but handy)
            window.__graphmailTheme = theme;
            window.__graphmailThemeStorageKey = storageKey;
        })();
    </script>

    {{-- Main stylesheet (Tailwind / compiled CSS) --}}
    <link rel="stylesheet" href="{{ asset('vendor/graph-mail/css/graph-mail.css') }}"/>

    {{-- Alpine.js  --}}
    <script defer src="{{ asset('vendor/graph-mail/js/alpine.js') }}"></script>

    {{-- Chart.js --}}
    <script defer src="{{ asset('vendor/graph-mail/js/chart.js') }}"></script>

    <style>
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
    <style>[x-cloak]{ display:none !important; }</style>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased transition-colors duration-300 dark:bg-gray-950 dark:text-gray-100">

<nav class="bg-white/80 backdrop-blur border-b border-gray-200/70 dark:bg-gray-950/70 dark:border-gray-800 sticky top-0 z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4 py-3">
            <div class="flex items-center gap-8">
                <a href="{{ route('graphmail.dashboard') }}" class="inline-flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-sm">
                        GM
                    </span>
                    <span class="font-semibold tracking-tight text-base sm:text-lg">
                        Graph Mail
                    </span>
                </a>

                <div class="hidden sm:flex items-center gap-4 text-sm">
                    <a href="{{ route('graphmail.mails.index') }}"
                       class="px-2 py-1 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition">
                        Mails
                    </a>
                    <a href="{{ route('graphmail.templates.index') }}"
                       class="px-2 py-1 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition">
                        Templates
                    </a>
                    <a href="{{ url(config('graph-mail.ui.path','graph-mail').'/pro/message-trace') }}"
                       class="px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:hover:bg-indigo-500/25 transition">
                        Pro tools
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Mobile nav (minimal) --}}
                <div class="sm:hidden">
                    <a href="{{ route('graphmail.mails.index') }}" class="text-xs text-gray-500 dark:text-gray-400">
                        Mails
                    </a>
                </div>

                {{-- Theme toggle --}}
                <button
                        id="theme-toggle"
                        type="button"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-100 hover:border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800 transition"
                        aria-pressed="false"
                        aria-label="Toggle dark mode"
                >
                    <span id="theme-toggle-light" class="hidden" aria-hidden="true">
                        {{-- Sun icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 3.5a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 .75.75v1.5a.75.75 0 0 1-.75.75h-.01A.75.75 0 0 1 10 5V3.5Zm0 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Zm5.657-6.157a.75.75 0 0 1 0-1.06l1.06-1.06a.75.75 0 1 1 1.061 1.06l-1.06 1.06a.75.75 0 0 1-1.061 0ZM3.283 15.243a.75.75 0 0 1 0-1.06l1.06-1.061a.75.75 0 1 1 1.061 1.06l-1.06 1.061a.75.75 0 0 1-1.061 0ZM3.5 10a.75.75 0 0 1-.75-.75v-.01A.75.75 0 0 1 3.5 8.5H5a.75.75 0 0 1 .75.75v.01A.75.75 0 0 1 5 10H3.5Zm10 0a.75.75 0 0 1-.75-.75v-.01a.75.75 0 0 1 .75-.74h1.5a.75.75 0 0 1 .75.75v.01a.75.75 0 0 1-.75.74H13.5Zm-8.096-4.657a.75.75 0 0 1 0-1.06l1.06-1.06a.75.75 0 1 1 1.061 1.06l-1.06 1.06a.75.75 0 0 1-1.061 0Zm8.486 8.486a.75.75 0 0 1 0-1.061l1.06-1.06a.75.75 0 1 1 1.061 1.06l-1.06 1.061a.75.75 0 0 1-1.061 0ZM10 15a.75.75 0 0 1 .75.75V17.5a.75.75 0 0 1-1.5 0v-1.75A.75.75 0 0 1 10 15Z"/>
                        </svg>
                    </span>
                    <span id="theme-toggle-dark" class="hidden" aria-hidden="true">
                        {{-- Moon icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M17.293 12.093a.75.75 0 0 0-.884-.256 5.5 5.5 0 0 1-7.246-7.245.75.75 0 0 0-.926-.99A7 7 0 1 0 17.55 13.02a.75.75 0 0 0-.257-.927Z"/>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
    @yield('content')
</main>

<script>
    (function () {
        const storageKey = window.__graphmailThemeStorageKey || 'graphmail-theme';

        function setTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        function getCurrentTheme() {
            return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        }

        window.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('theme-toggle');
            const lightIcon = document.getElementById('theme-toggle-light');
            const darkIcon = document.getElementById('theme-toggle-dark');

            if (!toggle || !lightIcon || !darkIcon) return;

            function syncUI(theme) {
                toggle.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');

                if (theme === 'dark') {
                    darkIcon.classList.remove('hidden');
                    lightIcon.classList.add('hidden');
                } else {
                    lightIcon.classList.remove('hidden');
                    darkIcon.classList.add('hidden');
                }
            }

            let currentTheme = window.__graphmailTheme || getCurrentTheme();
            syncUI(currentTheme);

            toggle.addEventListener('click', function () {
                currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                setTheme(currentTheme);
                try {
                    localStorage.setItem(storageKey, currentTheme);
                } catch (e) {
                    // ignore storage issues
                }
                syncUI(currentTheme);
            });
        });
    })();
</script>

@stack('scripts')
</body>
</html>
