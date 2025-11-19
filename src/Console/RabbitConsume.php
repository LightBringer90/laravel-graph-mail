<?php

namespace ProgressiveStudios\GraphMail\Console;

use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use ProgressiveStudios\GraphMail\Models\OutboundMail;
use ProgressiveStudios\GraphMail\Jobs\SendGraphMailJob;
use ProgressiveStudios\GraphMail\Services\RabbitService;
use function ProgressiveStudios\GraphMail\graph_mail_logger;

class RabbitConsume extends Command
{
    protected $signature = 'graph-mail:rabbit:consume {--once} {--memory=128}';
    protected $description = 'Consume outbound mail messages from RabbitMQ and dispatch send jobs';

    public function handle(RabbitService $rabbit)
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

        $callback = function (AMQPMessage $msg) {
            $payload = json_decode($msg->getBody(), true);
            try {
                $sender = $payload['sender'] ?? config('graph-mail.default_sender');
                $m = OutboundMail::create([
                    'sender_upn'     => $sender,
                    'subject'        => $payload['subject'] ?? null,
                    'template_key'   => $payload['template_key'] ?? null,
                    'template_data'  => $payload['data'] ?? [],
                    'to_recipients'  => $payload['to'] ?? [],
                    'cc_recipients'  => $payload['cc'] ?? [],
                    'bcc_recipients' => $payload['bcc'] ?? [],
                    'html_body'      => $payload['html'] ?? null,
                    'status'         => 'queued',
                ]);
                SendGraphMailJob::dispatch($m->id);
                graph_mail_logger()->info('rabbit.accept', ['mail_id' => $m->id]);
                $msg->ack();
            } catch (\Throwable $e) {
                graph_mail_logger()->error('rabbit.error',
                    ['error' => $e->getMessage(), 'payload' => $payload ?? null]);
                $msg->nack(false, false);
            }
        };

        $channel->basic_consume($cfg['queue'], '', false, false, false, false, $callback);

        $this->info('Listening on RabbitMQ queue: '.$cfg['queue']);

        while ($channel->is_open()) {
            $channel->wait(null, false, 5);
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
