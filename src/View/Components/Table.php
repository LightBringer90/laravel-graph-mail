<?php

namespace ProgressiveStudios\GraphMail\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection */
    public $data;

    /** @var array<int, array<string, mixed>> */
    public array $columns;

    public ?string $title;
    public ?string $subtitle;

    /**
     * @param  mixed  $data
     * @param  array<int, array<string, mixed>>  $columns
     */
    public function __construct($data, array $columns = [], string $title = null, string $subtitle = null)
    {
        $this->data     = $data;
        $this->columns  = $columns;
        $this->title    = $title;
        $this->subtitle = $subtitle;
    }

    public function render(): View|Closure|string
    {
        return view('graph-mail::components.table.table', [
            'data'     => $this->data,
            'columns'  => $this->columns,
            'title'    => $this->title,
            'subtitle' => $this->subtitle,
        ]);
    }
}
