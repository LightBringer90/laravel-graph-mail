<?php

namespace ProgressiveStudios\GraphMail\View\Components\Table;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use function ProgressiveStudios\GraphMail\View\Components\url;
use function ProgressiveStudios\GraphMail\View\Components\view;

class TableFilters extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $columns;

    public string $resetRoute;

    /**
     * @param  array<int, array<string, mixed>>  $columns
     */
    public function __construct(array $columns = [], string $resetRoute = null)
    {
        $this->columns    = $columns;
        $this->resetRoute = $resetRoute ?: url()->current();
    }

    public function render(): View|Closure|string
    {
        return view('graph-mail::components.table.table_filters', [
            'columns'    => $this->columns,
            'resetRoute' => $this->resetRoute,
        ]);
    }
}
