@php
    $list    = is_array($value) ? $value : (is_array($row->to_recipients ?? null) ? $row->to_recipients : []);
    $visible = array_slice($list, 0, 2);
    $hidden  = array_slice($list, 2);
@endphp

<div
        class="flex flex-wrap gap-1 max-w-xs items-center"
        x-data="{ showAll: false }"
>
    @forelse($visible as $r)
        <span
                class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700
                   dark:bg-gray-800 dark:text-gray-200"
        >
            {{ $r }}
        </span>
    @empty
        <span class="text-[11px] text-gray-400 italic">
            none
        </span>
    @endforelse

    @foreach($hidden as $r)
        <span
                x-show="showAll"
                x-cloak
                class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-700
                   dark:bg-gray-800 dark:text-gray-200"
        >
            {{ $r }}
        </span>
    @endforeach

    @if(count($hidden))
        <button
                type="button"
                class="inline-flex items-center rounded-full border border-dashed border-gray-300 bg-transparent px-2 py-0.5 text-[10px] font-medium text-gray-500 hover:bg-gray-100
                   dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                x-on:click="showAll = !showAll"
        >
            <span x-show="!showAll">+ {{ count($hidden) }} more</span>
            <span x-show="showAll" x-cloak>Show less</span>
        </button>
    @endif
</div>
