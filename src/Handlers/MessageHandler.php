<?php

namespace ProgressiveStudios\GraphMail\Handlers;

use ProgressiveStudios\GraphMail\Services\OutboundMailService;
use ProgressiveStudios\GraphMail\Support\MailPayloadValidator;
use Illuminate\Validation\ValidationException;
use PhpAmqpLib\Message\AMQPMessage;
use PDOException;
use Throwable;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class MessageHandler
{
    protected OutboundMailService $mailService;

    public function __construct(OutboundMailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function handleMessage(AMQPMessage $msg): void
    {
        $rawBody = $msg->getBody();
        $payload = json_decode($rawBody, true);
        $deliveryTag = $msg->getDeliveryTag();

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            graph_mail_logger()->error('rabbit.invalid_json', [
                'error'       => json_last_error_msg(),
                'body'        => $rawBody,
                'deliveryTag' => $deliveryTag,
            ]);
            $msg->nack(false, false);
            return;
        }

        try {
            $data = MailPayloadValidator::validate($payload);
        } catch (ValidationException $e) {
            graph_mail_logger()->error('rabbit.invalid_payload', [
                'errors'      => $e->errors(),
                'payload'     => $payload,
                'deliveryTag' => $deliveryTag,
            ]);
            $msg->nack(false, false);
            return;
        }

        // Normalize scalar → array
        if (isset($data['to']) && is_string($data['to'])) {
            $data['to'] = [$data['to']];
        }
        foreach (['cc', 'bcc'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = [$data[$field]];
            }
        }

        // Extract raw attachment payloads → descriptors
        $attachmentPayloads = $data['attachments'] ?? [];
        unset($data['attachments']);

        $attachmentDescriptors = [];
        foreach ($attachmentPayloads as $att) {
            if (!isset($att['filename'], $att['content'])) {
                graph_mail_logger()->warning('rabbit.attachment_missing_fields', [
                    'attachment' => $att,
                ]);
                continue;
            }

            $attachmentDescriptors[] = [
                'filename'       => $att['filename'],
                'mime'           => $att['mime'] ?? null,
                'content_base64' => $att['content'],
                'path'           => $att['path'] ?? null,
            ];
        }

        try {
            $mail = $this->mailService->queueMail($data, $attachmentDescriptors);

            graph_mail_logger()->info('rabbit.accept', [
                'mail_id'     => $mail->id,
                'deliveryTag' => $deliveryTag,
            ]);
            $msg->ack();

        } catch (PDOException $e) {
            graph_mail_logger()->warning('rabbit.db_error_requeue', [
                'error'       => $e->getMessage(),
                'deliveryTag' => $deliveryTag,
            ]);
            $msg->nack(false, true);

        } catch (Throwable $e) {
            graph_mail_logger()->error('rabbit.error', [
                'error'       => $e->getMessage(),
                'payload'     => $data,
                'deliveryTag' => $deliveryTag,
            ]);
            $msg->nack(false, false);
        }
    }
}
