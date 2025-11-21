<?php

namespace ProgressiveStudios\GraphMail\Support\Tables;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use ProgressiveStudios\GraphMail\Models\OutboundMail;

class OutboundMailTable
{
    /**
     * Base query + all filters for the mails index.
     */
    public function query(Request $request): Builder
    {
        $q = OutboundMail::query();

        if ($status = $request->string('status')->toString()) {
            $q->where('status', $status);
        }

        if ($sender = $request->string('sender')->toString()) {
            $q->where('sender_upn', $sender);
        }

        if ($to = $request->string('to')->toString()) {
            $q->where('to_recipients', 'like', "%{$to}%");
        }

        if ($subject = $request->string('subject')->toString()) {
            $q->where('subject', 'like', "%{$subject}%");
        }

        if ($from = $request->date('from_date')) {
            $q->where('created_at', '>=', $from->startOfDay());
        }

        if ($toDt = $request->date('to_date')) {
            $q->where('created_at', '<=', $toDt->endOfDay());
        }

        return $q;
    }

    /**
     * Paginated dataset for the table.
     */
    public function paginated(Request $request)
    {
        return $this->query($request)
            ->orderByDesc('id')
            ->paginate($this->perPage($request))
            ->withQueryString();
    }

    /**
     * Per-page resolution (with sane default).
     */
    public function perPage(Request $request): int
    {
        $perPage = (int) $request->integer('per_page', 10);

        return $perPage > 0 ? $perPage : 10;
    }

    /**
     * Column definitions for <x-graph-mail::table> + <x-graph-mail::table-filters>.
     */
    public function columns(): array
    {
        return [
            [
                'key'          => 'id',
                'label'        => 'ID',
                'header_class' => 'whitespace-nowrap',
                'cell_view'    => 'graph-mail::components.table.cells.id',
                // generic ID cell understands ['route-name', paramKey]
                // if paramKey is null, passes the whole model (route model binding)
                'route'        => ['graphmail.mails.show', null],
                'prefix'       => '#',
            ],
            [
                'key'        => 'subject',
                'label'      => 'Subject',
                'cell_class' => 'align-top',
                'filter'     => [
                    'type'        => 'text',
                    'label'       => 'Subject',
                    'placeholder' => 'Subject contains…',
                    'name'        => 'subject',
                    'col_span'    => 2,
                ],
            ],
            [
                'key'        => 'template_key',
                'label'      => 'Template',
                'cell_class' => 'text-[11px] font-mono text-gray-500 dark:text-gray-400 whitespace-nowrap',
            ],
            [
                'key'        => 'sender_upn',
                'label'      => 'Sender',
                'cell_class' => 'text-xs text-gray-700 dark:text-gray-200 whitespace-nowrap',
                'filter'     => [
                    'type'        => 'text',
                    'label'       => 'Sender UPN',
                    'placeholder' => 'user@tenant…',
                    'name'        => 'sender',
                ],
            ],
            [
                'key'       => 'to_recipients',
                'label'     => 'To',
                'cell_view' => 'graph-mail::components.table.cells.recipients_chips',
                'filter'    => [
                    'type'        => 'text',
                    'label'       => 'Recipient',
                    'placeholder' => 'Recipient contains…',
                    'name'        => 'to',
                    'col_span'    => 2,
                ],
            ],
            [
                'key'       => 'status',
                'label'     => 'Status',
                'cell_view' => 'graph-mail::components.table.cells.status_map',
                'status_map'=> [
                    'queued' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-100',
                    'sent'   => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100',
                    'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-100',
                ],
                'filter'    => [
                    'type'        => 'select',
                    'label'       => 'Status',
                    'name'        => 'status',
                    'placeholder' => 'Any status',
                    'options'     => [
                        ['value' => 'queued', 'label' => 'Queued'],
                        ['value' => 'sent',   'label' => 'Sent'],
                        ['value' => 'failed', 'label' => 'Failed'],
                    ],
                ],
            ],
            [
                'key'        => 'created_at',
                'label'      => 'Created',
                // table component knows how to format Carbon with this
                'date_format'=> 'Y-m-d H:i',
            ],
            // hidden filter-only “columns” for date range
            [
                'key'    => 'from_date',
                'hidden' => true,
                'filter' => [
                    'type'     => 'date',
                    'label'    => 'From date',
                    'name'     => 'from_date',
                    'col_span' => 1,
                ],
            ],
            [
                'key'    => 'to_date',
                'hidden' => true,
                'filter' => [
                    'type'     => 'date',
                    'label'    => 'To date',
                    'name'     => 'to_date',
                    'col_span' => 1,
                ],
            ],
        ];
    }
}
