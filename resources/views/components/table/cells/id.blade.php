@php
    // $column['prefix'] (default "#")
    $prefix = $column['prefix'] ?? '#';

    // route: either Closure or ['name', paramKey]
    $routeConfig = $column['route'] ?? null;

    $label = $prefix . ($row->id ?? $value ?? '');
    $href  = null;

    if ($routeConfig instanceof \Closure) {
        $href = $routeConfig($row);
    } elseif (is_array($routeConfig) && !empty($routeConfig[0])) {
        $paramKey = $routeConfig[1] ?? null;
        $paramVal = $paramKey ? data_get($row, $paramKey) : $row;
        $href     = route($routeConfig[0], $paramVal);
    }
@endphp

@if($href)
    <a href="{{ $href }}"
       class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
        {{ $label }}
    </a>
@else
    <span class="text-gray-800 dark:text-gray-100 font-medium">
        {{ $label }}
    </span>
@endif
