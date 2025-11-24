<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Ui;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use Illuminate\Support\Facades\Storage;
use ProgressiveStudios\GraphMail\Support\Tables\OutboundMailTable;

class OutboundMailController extends Controller
{
    public function __construct(
        protected OutboundMailTable $table
    ) {}

    public function index(Request $request)
    {
        $mails = $this->table->paginated($request);

        return view('graph-mail::graph-mail.mails.index', [
            'mails'            => $mails,
            'mailTableColumns' => $this->table->columns(),
        ]);
    }

    public function show(OutboundMail $mail)
    {
        // Status badge classes (view-specific, not table-related)
        $statusBadge = [
            'queued' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-100',
            'sent'   => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100',
            'failed' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-100',
        ];

        $badgeClass = $statusBadge[$mail->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100';

        // Normalize attachments to array and decorate with human size
        $rawAttachments = is_array($mail->attachments)
            ? $mail->attachments
            : (json_decode($mail->attachments ?? '[]', true) ?? []);

        $attachments = collect($rawAttachments)->map(function ($att) {
            $size = $att['size'] ?? 0;

            return [
                'filename'   => $att['filename'] ?? 'attachment',
                'mime'       => $att['mime'] ?? null,
                'path'       => $att['path'] ?? null,
                'size'       => $size,
                'size_human' => $this->formatBytes($size),
                'url'        => Storage::disk(config('graph-mail.attachments_disk', 'local'))->url($att['path']),
            ];
        })->all();

        // Group recipients for the view
        $recipientGroups = [
            'To'  => $mail->to_recipients ?? [],
            'Cc'  => $mail->cc_recipients ?? [],
            'Bcc' => $mail->bcc_recipients ?? [],
        ];

        return view('graph-mail::graph-mail.mails.show', [
            'mail'            => $mail,
            'badgeClass'      => $badgeClass,
            'attachments'     => $attachments,
            'recipientGroups' => $recipientGroups,
        ]);
    }

    public function downloadAttachment(OutboundMail $mail, int $index)
    {
        // Normalize attachments just like in show()
        $rawAttachments = is_array($mail->attachments)
            ? $mail->attachments
            : (json_decode($mail->attachments ?? '[]', true) ?? []);

        if (! isset($rawAttachments[$index])) {
            abort(404, 'Attachment not found.');
        }

        $att = $rawAttachments[$index];

        $disk     = config('graph-mail.attachments_disk', 'local');
        $path     = $att['path'] ?? null;
        $filename = $att['filename'] ?? 'attachment';

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            abort(404, 'File not found.');
        }

        // Let Laravel handle headers + streaming
        return Storage::disk($disk)->download($path, $filename);
    }

    /**
     * Human-readable file sizes.
     */
    protected function formatBytes(?int $bytes): string
    {
        if (!$bytes || $bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = (int) floor(log($bytes, 1024));
        $value = $bytes / pow(1024, $i);

        return number_format($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}
