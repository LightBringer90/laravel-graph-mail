@php
    $filterable = collect($columns ?? [])->filter(fn($c) => !empty($c['filter']))->values();

    $hasFilters = $filterable->contains(function ($col) {
        $f = $col['filter'];
        $names = (array)($f['name'] ?? $col['key'] ?? null);

        foreach ($names as $name) {
            if ($name && filled(request($name))) {
                return true;
            }
        }

        return false;
    });
@endphp

<section
        class="mb-6 rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm"
>
    <div class="flex items-center justify-between border-b border-gray-100/80 dark:border-gray-800/80 px-6 py-5">
        <div>
            <div class="text-[11px] font-semibold tracking-wide uppercase text-gray-500 dark:text-gray-400">
                Filters
            </div>
            <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                Refine the results using the fields below.
            </p>
        </div>

        @if($hasFilters)
            <a
                    href="{{ $resetRoute }}"
                    class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-white px-3 py-1 text-[11px] font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
            >
                <span>Clear filters</span>
                <span class="text-gray-400 dark:text-gray-500">&times;</span>
            </a>
        @endif
    </div>

    <form method="GET" class="p-5 sm:p-6 space-y-3">
        <div class="grid gap-3 md:grid-cols-6">
            @foreach($filterable as $column)
                @php
                    $f        = $column['filter'];
                    $type     = $f['type'] ?? 'text';
                    $label    = $f['label'] ?? $column['label'] ?? $column['key'] ?? 'Filter';
                    $name     = $f['name'] ?? $column['key'] ?? null;
                    $placeholder = $f['placeholder'] ?? '';
                    $colSpan  = $f['col_span'] ?? 1;
                    $classes  = "md:col-span-{$colSpan}";
                @endphp

                @if(!$name)
                    @continue
                @endif

                @if($type === 'text')
                    <div class="{{ $classes }}">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ $label }}
                        </label>
                        <input
                                class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                                name="{{ $name }}"
                                placeholder="{{ $placeholder }}"
                                value="{{ request($name) }}"
                        />
                    </div>
                @elseif($type === 'select')
                    <div class="{{ $classes }}">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ $label }}
                        </label>
                        <select
                                class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                                name="{{ $name }}"
                        >
                            @if(!empty($f['placeholder']))
                                <option value="">{{ $f['placeholder'] }}</option>
                            @endif

                            @foreach($f['options'] ?? [] as $opt)
                                @php
                                    $val = is_array($opt) ? $opt['value'] : $opt;
                                    $lbl = is_array($opt) ? ($opt['label'] ?? $opt['value']) : $opt;
                                @endphp
                                <option value="{{ $val }}" @selected(request($name) === (string)$val)>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif($type === 'date')
                    <div class="{{ $classes }}">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">
                            {{ $label }}
                        </label>
                        <input
                                type="date"
                                name="{{ $name }}"
                                class="block w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-950"
                                value="{{ request($name) }}"
                        />
                    </div>
                @endif
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
            <div class="text-[11px] text-gray-400 dark:text-gray-500">
                @if($hasFilters)
                    Showing results for current filters.
                @else
                    No filters applied – showing recent records.
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
                        href="{{ $resetRoute }}"
                        class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800"
                >
                    Reset
                </a>
            </div>
        </div>
    </form>
</section>
