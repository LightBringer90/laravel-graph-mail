@php
    $val   = (bool) ($value ?? data_get($row, $column['key'] ?? null));
    $onLbl = $column['true_label']  ?? 'Yes';
    $offLbl= $column['false_label'] ?? 'No';

    $onCls  = $column['true_class']  ?? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100';
    $offCls = $column['false_class'] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800/80 dark:text-gray-300';
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $val ? $onCls : $offCls }}">
    {{ $val ? $onLbl : $offLbl }}
</span>
