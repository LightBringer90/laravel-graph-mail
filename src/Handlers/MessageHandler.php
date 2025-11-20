<?php

namespace ProgressiveStudios\GraphMail\Handlers;

use ProgressiveStudios\GraphMail\Services\OutboundMailService;
use ProgressiveStudios\GraphMail\Support\MailPayloadValidator;
use Illuminate\Validation\ValidationException;
use PhpAmqpLib\Message\AMQPMessage;
use \PDOException; // Adaugat pentru a prinde erori temporare de DB
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class MessageHandler
{
    protected $mailService;

    // Injecteaza serviciul necesar prin constructor
    public function __construct(OutboundMailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function handleMessage(AMQPMessage $msg)
    {
        $rawBody = $msg->getBody();
        $payload = json_decode($rawBody, true);
        $deliveryTag = $msg->getDeliveryTag(); // Preluam tag-ul imediat

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            graph_mail_logger()->error('rabbit.invalid_json', [
                'error'       => json_last_error_msg(),
                'body'        => $rawBody,
                'deliveryTag' => $deliveryTag, // Inclus in log
            ]);
            $msg->nack(false, false); // Nack fara requeue
            return;
        }

        try {
            // 🔹 shared validation logic
            $data = MailPayloadValidator::validate($payload);
        } catch (ValidationException $e) {
            graph_mail_logger()->error('rabbit.invalid_payload', [
                'errors'      => $e->errors(),
                'payload'     => $payload,
                'deliveryTag' => $deliveryTag, // Inclus in log
            ]);
            $msg->nack(false, false); // Nack fara requeue
            return;
        }

        // normalize scalar → array, just in case some producer misbehaves
        if (isset($data['to']) && is_string($data['to'])) {
            $data['to'] = [$data['to']];
        }
        foreach (['cc', 'bcc'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = [$data[$field]];
            }
        }

        $attachments = $data['attachments'] ?? [];
        unset($data['attachments']);

        try {
            $mail = $this->mailService->queueMail($data, $attachments);

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

        } catch (\Throwable $e) {
            graph_mail_logger()->error('rabbit.error', [
                'error'       => $e->getMessage(),
                'payload'     => $data,
                'deliveryTag' => $deliveryTag, // Inclus in log
            ]);
            $msg->nack(false, false); // Nack fara requeue (sau muta in DLQ)
        }
    }
}
