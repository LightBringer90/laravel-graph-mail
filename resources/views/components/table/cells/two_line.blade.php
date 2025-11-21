@php
    // Primary text = the column value (e.g. "name")
    $primary = $value ?? data_get($row, $column['key'] ?? null);

    // Secondary text can be:
    // - a string key (e.g. 'default_subject')
    // - a closure
    // - a direct value
    $secondary = $column['secondary'] ?? null;

    if (is_string($secondary)) {
        $secondary = data_get($row, $secondary);
    } elseif ($secondary instanceof \Closure) {
        $secondary = $secondary($row);
    }
@endphp

<div class="text-gray-800 dark:text-gray-100">
    {{ $primary ?? '—' }}
</div>

@if(!empty($secondary))
    <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate max-w-xs">
        {{ $secondary }}
    </div>
@endif
