@php
    /** @var array $column */
    $actions = $column['actions'] ?? [];
@endphp

<div class="flex items-center justify-center gap-2">
    @foreach($actions as $action)
        @php
            $route = $action['route'] ?? null;
            $href  = null;

            // If [$routeName, paramKey]
            if (is_array($route)) {
                [$rName, $param] = $route;

                $href = $param
                    ? route($rName, data_get($row, $param))
                    : route($rName, $row); // route-model binding
            }
        @endphp

        {{-- Link button --}}
        @if(($action['type'] ?? null) === 'link' && $href)
            <a href="{{ $href }}"
               class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold shadow-sm {{ $action['class'] ?? '' }}">
                {{ $action['label'] }}
            </a>
        @endif

        {{-- Delete action --}}
        @if(($action['type'] ?? null) === 'delete' && $href)
            <form method="POST"
                  action="{{ $href }}"
                  onsubmit="return confirm('{{ $action['confirm'] ?? 'Are you sure you want to delete this item?' }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold shadow-sm {{ $action['class'] ?? '' }}">
                    {{ $action['label'] }}
                </button>
            </form>
        @endif

    @endforeach
</div>
