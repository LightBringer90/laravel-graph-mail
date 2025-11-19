<?php

namespace ProgressiveStudios\GraphMail\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;

class RabbitService
{
    public function connect(): AMQPStreamConnection|AMQPSSLConnection
    {
        $cfg = config('graph-mail.rabbitmq');
        if ($cfg['ssl']) {
            return new AMQPSSLConnection(
                $cfg['host'], (int) $cfg['port'], $cfg['user'], $cfg['password'], $cfg['vhost'], [
                    'verify_peer'      => true,
                    'verify_peer_name' => true,
                ]
            );
        }
        return new AMQPStreamConnection($cfg['host'], (int) $cfg['port'], $cfg['user'], $cfg['password'],
            $cfg['vhost']);
    }
}
