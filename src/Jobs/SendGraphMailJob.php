<?php

namespace ProgressiveStudios\GraphMail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use ProgressiveStudios\GraphMail\Services\GraphMailService;
use ProgressiveStudios\GraphMail\Services\MailRenderService;
use Throwable;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class SendGraphMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Max attempts for this job (can be overridden via config).
     */
    public int $tries;

    public function __construct(
        public int $outboundMailId,
        protected ?Filesystem $disk = null,
        protected ?string $diskName = null,
    ) {
        $this->tries = (int) config('graph-mail.rate.max_retries', 10);
        $this->diskName = $this->diskName ?? config('graph-mail.attachments_disk', 'local');
        $this->disk     = $this->disk ?? Storage::disk($this->diskName);
    }

    /**
     * Backoff intervals (seconds) for retry attempts.
     */
    public function backoff(): array
    {
        return [10, 30, 60, 120, 300, 600];
    }

    public function handle(GraphMailService $graph, MailRenderService $render): void
    {
        $mail = OutboundMail::find($this->outboundMailId);

        // Mail was deleted or not found – nothing to do
        if (!$mail) {
            graph_mail_logger()->warning('mail.missing', [
                'id' => $this->outboundMailId,
            ]);

            return;
        }

        //No re-sending for already sent mail if the job is re-run
        if ($mail->status === 'sent') {
            graph_mail_logger()->info('mail.already_sent', ['id' => $mail->id]);
            return;
        }

        [$subjectFromTpl, $html] = $render->render(
            $mail->template_key,
            $mail->template_data,
            $mail->html_body
        );

        $subject = $mail->subject ?: $subjectFromTpl ?: 'No subject';

        try {
            $attachments = $this->prepareAttachments($mail);

            $message = [
                'subject'                    => $subject,
                'body'                       => [
                    'contentType' => 'HTML',
                    'content'     => $html,
                ],
                'toRecipients'               => $this->mapRecipients($mail->to_recipients ?? []),
                'ccRecipients'               => $this->mapRecipients($mail->cc_recipients ?? []),
                'bccRecipients'              => $this->mapRecipients($mail->bcc_recipients ?? []),
                'isDeliveryReceiptRequested' => true,
                'isReadReceiptRequested'     => false,
                'attachments'                => $attachments,
            ];

            $payload = [
                'message'         => $message,
                'saveToSentItems' => true,
            ];

            // If this does not throw, we consider the mail successfully sent
            $graph->send($mail->sender_upn, $payload);

            $mail->status = 'sent';
            $mail->sent_at = now(); // assuming cast to datetime on model
            $mail->save();

            graph_mail_logger()->info('mail.sent', ['id' => $mail->id]);

        } catch (RequestException $e) {
            $resp = $e->response;
            $status = $resp?->status() ?? 0;
            $retryAfter = (int) ($resp?->header('Retry-After') ?? 0);

            graph_mail_logger()->warning('mail.send.error', [
                'id'          => $mail->id,
                'status'      => $status,
                'retry_after' => $retryAfter,
                'msg'         => $e->getMessage(),
            ]);

            // 429 / 503 with Retry-After => transient, requeue instead of failing permanently
            if (in_array($status, [429, 503], true) && $retryAfter > 0) {
                graph_mail_logger()->info('mail.send.retry_scheduled', [
                    'id'          => $mail->id,
                    'status'      => $status,
                    'retry_after' => $retryAfter,
                ]);

                $this->release($retryAfter);
                return;
            }

            // Permanent failure
            $mail->status = 'failed';
            $mail->save();

            // Let the queue mark this job as failed / retry if appropriate
            throw $e;

        } catch (Throwable $e) {
            $mail->status = 'failed';
            $mail->save();

            // Any other exception (network, render issues, etc.)
            graph_mail_logger()->error('mail.send.exception', [
                'id'    => $mail->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Map a list of email strings to the Graph API recipient format.
     */
    protected function mapRecipients(array $emails): array
    {
        return collect($emails)
            ->filter() // remove null/empty
            ->map(fn(string $email) => [
                'emailAddress' => ['address' => $email],
            ])
            ->values()
            ->all();
    }

    /**
     * Prepare Graph fileAttachment payloads from the OutboundMail attachments.
     */
    protected function prepareAttachments(OutboundMail $mail): array
    {
        $data = [];
        $attachments = $mail->attachments ?? [];

        foreach ((array) $attachments as $attachment) {
            // $attachment can be a string path, or an array with `path` and maybe `filename`
            $path = is_array($attachment) ? ($attachment['path'] ?? null) : $attachment;
            $filename = is_array($attachment)
                ? ($attachment['filename'] ?? null)
                : null;

            if (!$path) {
                graph_mail_logger()->warning('mail.attachment.missing_path', [
                    'attachment' => $attachment,
                    'mail_id'    => $mail->id,
                ]);
                continue;
            }

            if (!$this->disk->exists($path)) {
                graph_mail_logger()->warning('mail.attachment.unreadable', [
                    'path'    => $path,
                    'name'    => $filename ?? basename($path),
                    'mail_id' => $mail->id,
                ]);
                continue;
            }

            // raw file contents
            $fileContent = $this->disk->get($path);

            // derive filename
            $filename = $filename ?: basename($path);

            // mime type
            $mimeType = $this->disk->mimeType($path) ?? 'application/octet-stream';

            $data[] = [
                '@odata.type'  => '#microsoft.graph.fileAttachment',
                'name'         => $filename,
                'contentType'  => $mimeType,
                'contentBytes' => base64_encode($fileContent),
            ];
        }

        return $data;
    }
}
