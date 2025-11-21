@php
    // column['status_map'] = [ 'value' => 'bg-class text-class', ... ]
    $map       = $column['status_map'] ?? [];
    $raw       = $value ?? data_get($row, $column['key'] ?? null);
    $label     = $column['status_labels'][$raw] ?? ucfirst((string) $raw);
    $badgeClass= $map[$raw] ?? ($column['default_class'] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100');
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClass }}">
    {{ $label ?: '—' }}
</span>
