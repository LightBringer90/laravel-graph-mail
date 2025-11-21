<?php

namespace ProgressiveStudios\GraphMail\Support\Tables;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use ProgressiveStudios\GraphMail\Models\MailTemplate;

class MailTemplateTable
{
    /**
     * Build the base query for the index table, including filters.
     */
    public function query(Request $request): Builder
    {
        $q = MailTemplate::query();

        if ($name = $request->string('name')->toString()) {
            $q->where('name', 'like', "%{$name}%");
        }

        if ($module = $request->string('module')->toString()) {
            $q->where('module', 'like', "%{$module}%");
        }

        $active = $request->input('active', null);
        if ($active !== null && $active !== '') {
            $q->where('active', (bool) $active);
        }

        return $q;
    }

    /**
     * Paginated dataset for the table.
     */
    public function paginated(Request $request)
    {
        return $this->query($request)
            ->orderBy('module')
            ->orderBy('name')
            ->paginate($this->perPage($request))
            ->withQueryString();
    }

    /**
     * Determine per-page size based on request.
     */
    public function perPage(Request $request): int
    {
        $perPage = (int) $request->integer('per_page', 20);

        return $perPage > 0 ? $perPage : 20;
    }

    /**
     * Stats for header cards (total / active / inactive).
     */
    public function stats()
    {
        return MailTemplate::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_count')
            ->selectRaw('SUM(CASE WHEN active = 0 THEN 1 ELSE 0 END) as inactive_count')
            ->first();
    }

    /**
     * Column definitions for the dynamic table + filters.
     */
    public function columns(): array
    {
        return [
            [
                'key'        => 'key',
                'label'      => 'Key',
                'cell_class' => 'align-middle',
            ],
            [
                'key'       => 'name',
                'label'     => 'Name',
                // generic two-line cell: name + default_subject
                'cell_view' => 'graph-mail::components.table.cells.two_line',
                'secondary' => 'default_subject',
                'filter'    => [
                    'type'        => 'text',
                    'label'       => 'Name',
                    'placeholder' => 'Template name…',
                    'name'        => 'name',
                    'col_span'    => 2,
                ],
            ],
            [
                'key'        => 'module',
                'label'      => 'Module',
                'cell_class' => 'text-xs text-gray-500 dark:text-gray-400',
                'filter'     => [
                    'type'        => 'text',
                    'label'       => 'Module',
                    'placeholder' => 'Module contains…',
                    'name'        => 'module',
                ],
            ],
            [
                'key'        => 'mailable_class',
                'label'      => 'Mailable',
                'cell_class' => 'text-xs text-gray-500 dark:text-gray-400',
            ],
            [
                'key'        => 'view',
                'label'      => 'View',
                'cell_class' => 'text-xs text-gray-500 dark:text-gray-400',
            ],
            [
                'key'       => 'active',
                'label'     => 'Active',
                'cell_view' => 'graph-mail::components.table.cells.boolean_pill',
                'true_label'  => 'Active',
                'false_label' => 'Inactive',
                'true_class'  => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100',
                'false_class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800/80 dark:text-gray-300',
                'filter'    => [
                    'type'        => 'select',
                    'label'       => 'Active',
                    'name'        => 'active',
                    'placeholder' => 'Any',
                    'options'     => [
                        ['value' => '1', 'label' => 'Active'],
                        ['value' => '0', 'label' => 'Inactive'],
                    ],
                ],
            ],
            [
                'key'          => null,
                'label'        => 'Actions',
                'cell_view'    => 'graph-mail::components.table.cells.actions',
                'header_class' => 'text-center',
                'cell_class'   => 'text-center',
            ],
        ];
    }
}
