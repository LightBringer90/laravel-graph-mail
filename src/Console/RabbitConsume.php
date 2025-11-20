<?php

namespace ProgressiveStudios\GraphMail\Console;

use Illuminate\Console\Command;
use ProgressiveStudios\GraphMail\Services\RabbitService;
use ProgressiveStudios\GraphMail\Handlers\MessageHandler; // Import nou
use PhpAmqpLib\Exception\AMQPTimeoutException;

class RabbitConsume extends Command
{
    protected $signature = 'graph-mail:rabbit:consume {--once} {--memory=128}';
    protected $description = 'Consume outbound mail messages from RabbitMQ and dispatch send jobs';

    // Injectam doar serviciul de RabbitService in constructor (sau handle, cum ai facut initial)
    public function handle(RabbitService $rabbit, MessageHandler $messageHandler)
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

        // Callback-ul devine mult mai simplu, doar paseaza mesajul catre handler
        $callback = function ($msg) use ($messageHandler) {
            $messageHandler->handleMessage($msg);
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
