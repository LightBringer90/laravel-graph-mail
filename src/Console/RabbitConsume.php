<?php

namespace ProgressiveStudios\GraphMail\Console;

use Illuminate\Console\Command;
use ProgressiveStudios\GraphMail\Services\OutboundMailService;
use ProgressiveStudios\GraphMail\Services\RabbitService;
use ProgressiveStudios\GraphMail\Support\MailPayloadValidator;
use Illuminate\Validation\ValidationException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class RabbitConsume extends Command
{
    protected $signature = 'graph-mail:rabbit:consume {--once} {--memory=128}';
    protected $description = 'Consume outbound mail messages from RabbitMQ and dispatch send jobs';

    public function handle(RabbitService $rabbit, OutboundMailService $mailService)
    {
        if (!config('graph-mail.rabbitmq.enabled')) {
            $this->error('RabbitMQ disabled. Enable GRAPH_RABBIT_ENABLED=true');
            return self::FAILURE;
        }

        $cfg = config('graph-mail.rabbitmq');
        $connection = $rabbit->connect();
        $channel = $connection->channel();
        $channel->basic_qos(null, (int) $cfg['prefetch'], null);
        $channel->queue_declare($cfg['queue'], false, true, false, false);

        $callback = function (AMQPMessage $msg) use ($mailService) {
            $rawBody = $msg->getBody();
            $payload = json_decode($rawBody, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
                graph_mail_logger()->error('rabbit.invalid_json', [
                    'error' => json_last_error_msg(),
                    'body'  => $rawBody,
                ]);
                $msg->nack(false, false);
                return;
            }

            try {
                // 🔹 shared validation logic
                $data = MailPayloadValidator::validate($payload);
            } catch (ValidationException $e) {
                graph_mail_logger()->error('rabbit.invalid_payload', [
                    'errors'  => $e->errors(),
                    'payload' => $payload,
                ]);
                $msg->nack(false, false);
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
                $mail = $mailService->queueMail($data, $attachments);

                graph_mail_logger()->info('rabbit.accept', ['mail_id' => $mail->id]);
                $msg->ack();
            } catch (\Throwable $e) {
                graph_mail_logger()->error('rabbit.error', [
                    'error'   => $e->getMessage(),
                    'payload' => $data,
                ]);
                $msg->nack(false, false);
            }
        };

        $channel->basic_consume($cfg['queue'], '', false, false, false, false, $callback);

        $this->info('Listening on RabbitMQ queue: '.$cfg['queue']);

        while ($channel->is_open()) {
            try {
                $channel->wait(null, false, 5);
            } catch (AMQPTimeoutException $e) {
                // just idle
            }

            if ($this->option('once')) {
                break;
            }

            if (memory_get_usage(true) / 1024 / 1024 > (int) $this->option('memory')) {
                break;
            }
        }

        $channel->close();
        $connection->close();

        return self::SUCCESS;
    }
}
