<?php

namespace ProgressiveStudios\GraphMail\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use ProgressiveStudios\GraphMail\Jobs\SendGraphMailJob;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class OutboundMailService
{
    /**
     * @param  array  $data  normalized mail data:
     *     - sender (nullable|string)
     *     - subject (nullable|string)
     *     - template_key (nullable|string)
     *     - data (array)
     *     - to (array)
     *     - cc (array)
     *     - bcc (array)
     *     - html (nullable|string)
     * @param  array  $attachments  array of:
     *     ['path' => ..., 'filename' => ..., 'mime' => ..., 'size' => ...]
     *
     * @throws \Throwable
     */
    public function queueMail(array $data, array $attachments = []): OutboundMail
    {
        return DB::transaction(function () use ($data, $attachments) {
            $sender = $data['sender'] ?? config('graph-mail.default_sender');

            $mail = OutboundMail::create([
                'sender_upn'     => $sender,
                'subject'        => $data['subject'] ?? null,
                'template_key'   => $data['template_key'] ?? null,
                'template_data'  => $data['data'] ?? [],
                'to_recipients'  => $data['to'] ?? [],
                'cc_recipients'  => $data['cc'] ?? [],
                'bcc_recipients' => $data['bcc'] ?? [],
                'html_body'      => $data['html'] ?? null,
                'status'         => 'queued',
            ]);

            if (!empty($attachments)) {
                $mail->attachments = $attachments;
                $mail->save();
            }

            SendGraphMailJob::dispatch($mail->id);

            graph_mail_logger()->info('graph-mail.queued', ['mail_id' => $mail->id]);

            return $mail;
        });
    }
}
