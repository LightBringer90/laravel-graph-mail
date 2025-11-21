@php
    // Visible columns = those not marked hidden
    $visibleColumns = collect($columns ?? [])->filter(fn($c) => empty($c['hidden']))->values();
@endphp

<section
        class="rounded-2xl bg-white/90 dark:bg-gray-950/80 border border-gray-100/70 dark:border-gray-800/80 shadow-sm overflow-hidden"
>
    {{-- Header --}}
    <div class="border-b border-gray-100/80 dark:border-gray-800/80 px-4 py-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <div class="border-b border-gray-100/80 dark:border-gray-800/80 px-5 py-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">

            <div class="flex flex-col gap-1">

                <!-- Title + total on same line -->
                <div class="flex items-center gap-2">
                    @if($title)
                        <span class="font-medium text-gray-700 dark:text-gray-100">
                    {{ $title }}
                </span>
                    @endif

                    @if(method_exists($data, 'total'))
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">
                    • {{ number_format($data->total()) }} items
                </span>
                    @endif
                </div>

                <!-- Subtitle -->
                @if($subtitle)
                    <span class="text-[11px] text-gray-400 dark:text-gray-500">
                {{ $subtitle }}
            </span>
                @endif
            </div>

            <!-- Right side message stays in SAME place -->
            <div class="hidden sm:block text-[11px] text-gray-400 dark:text-gray-500
                @if($subtitle) invisible @endif">
                Click a row or ID to view details.
            </div>

        </div>


        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                <tr class="border-b border-gray-100 bg-gray-50/80 text-left text-[11px] uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:bg-gray-900/80 dark:text-gray-400">
                    @foreach($visibleColumns as $column)
                        <th class="px-4 py-2 {{ $column['header_class'] ?? '' }}">
                            {{ $column['label'] ?? $column['key'] ?? '' }}
                        </th>
                    @endforeach
                </tr>
                </thead>

                <tbody>
                @forelse($data as $row)
                    <tr class="border-b border-gray-50 dark:border-gray-900/70 hover:bg-gray-50/80 dark:hover:bg-gray-900/70 transition">
                        @foreach($visibleColumns as $column)
                            @php
                                $key      = $column['key'] ?? null;
                                $cellView = $column['cell_view'] ?? null;
                                $value    = $key ? data_get($row, $key) : null;
                            @endphp

                            <td class="px-4 py-2 align-top {{ $column['cell_class'] ?? '' }}">
                                @if($cellView)
                                    @include($cellView, [
                                        'row'    => $row,
                                        'value'  => $value,
                                        'column' => $column,
                                    ])
                                @else
                                    @if($value instanceof \Carbon\CarbonInterface)
                                        {{ $value->format($column['date_format'] ?? 'Y-m-d H:i') }}
                                    @elseif(is_bool($value))
                                        {{ $value ? 'Yes' : 'No' }}
                                    @elseif(is_array($value))
                                        <div class="max-w-xs truncate text-gray-800 dark:text-gray-100">
                                            {{ implode(', ', $value) }}
                                        </div>
                                    @else
                                        <div class="max-w-xs truncate text-gray-800 dark:text-gray-100">
                                            {{ $value ?? '—' }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $visibleColumns->count() }}"
                            class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                            No records match the current filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination + per-page --}}
        @if(method_exists($data, 'links'))
            <div class="border-t border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-900/70 px-4 py-4 sm:py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-xs text-gray-500 dark:text-gray-400">
                {{-- Left: result summary --}}
                <div class="flex items-center gap-2">
                    @if(method_exists($data, 'firstItem') && $data->count())
                        <span class="tabular-nums">
                        Showing {{ $data->firstItem() }}–{{ $data->lastItem() }}
                        of {{ $data->total() }}
                    </span>
                    @else
                        <span>No results.</span>
                    @endif
                </div>

                {{-- Right: per-page + pagination --}}
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-8 justify-between sm:justify-end w-full sm:w-auto">
                    {{-- Per page selector --}}
                    <form method="GET" class="flex items-center gap-2 text-[11px]">
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

                    {{-- Pagination links --}}
                    <div class="flex justify-end">
                        <div class="inline-flex items-center">
                            {{ $data->onEachSide(1)->links('graph-mail::components.table.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
    @endif
</section>
