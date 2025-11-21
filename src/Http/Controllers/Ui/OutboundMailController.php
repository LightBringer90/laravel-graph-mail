<?php

namespace ProgressiveStudios\GraphMail\Http\Controllers\Ui;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use Illuminate\Support\Facades\Storage;

class OutboundMailController extends Controller
{
    public function index(Request $r)
    {
        $q = OutboundMail::query();

        if ($s = $r->string('status')->toString()) {
            $q->where('status', $s);
        }
        if ($sender = $r->string('sender')->toString()) {
            $q->where('sender_upn', $sender);
        }
        if ($to = $r->string('to')->toString()) {
            $q->where('to_recipients', 'like', "%{$to}%");
        }
        if ($subj = $r->string('subject')->toString()) {
            $q->where('subject', 'like', "%{$subj}%");
        }
        if ($from = $r->date('from_date')) {
            $q->where('created_at', '>=', $from->startOfDay());
        }
        if ($toDt = $r->date('to_date')) {
            $q->where('created_at', '<=', $toDt->endOfDay());
        }

        // NEW: pagination size
        $perPage = $r->integer('per_page', 10);   // default 10

        $mails = $q->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('graph-mail::graph-mail.mails.index', compact('mails'));
    }

    public function show(OutboundMail $mail)
    {
        // Status badge classes
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

    /**
     * Human-readable file sizes.
     */
    protected function formatBytes(?int $bytes): string
    {
        if (! $bytes || $bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i     = (int) floor(log($bytes, 1024));
        $value = $bytes / pow(1024, $i);

        return number_format($value, $i === 0 ? 0 : 1) . ' ' . $units[$i];
    }
}
