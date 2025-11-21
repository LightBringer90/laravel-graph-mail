@if ($paginator->hasPages())
    <nav
            role="navigation"
            aria-label="Pagination Navigation"
            class="inline-flex items-center space-x-1 sm:space-x-2 text-xs"
    >
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                    class="px-2.5 sm:px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-full text-gray-300 dark:text-gray-600 cursor-not-allowed"
                    aria-disabled="true"
                    aria-label="@lang('pagination.previous')"
            >
                ‹
            </span>
        @else
            <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="px-2.5 sm:px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-full text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    aria-label="@lang('pagination.previous')"
            >
                ‹
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-2 py-1.5 text-gray-400 dark:text-gray-500">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span
                                class="px-2.5 sm:px-3 py-1.5 rounded-full bg-indigo-600 text-white shadow-sm tabular-nums"
                                aria-current="page"
                        >
                            {{ $page }}
                        </span>
                    @else
                        <a
                                href="{{ $url }}"
                                class="px-2.5 sm:px-3 py-1.5 rounded-full border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 tabular-nums transition"
                        >
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="px-2.5 sm:px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-full text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                    aria-label="@lang('pagination.next')"
            >
                ›
            </a>
        @else
            <span
                    class="px-2.5 sm:px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-full text-gray-300 dark:text-gray-600 cursor-not-allowed"
                    aria-disabled="true"
                    aria-label="@lang('pagination.next')"
            >
                ›
            </span>
        @endif
    </nav>
@endif