<?php

use ProgressiveStudios\GraphMail\Support\DefaultNdrCorrelator;

return [
    'tenant_id'     => env('GRAPH_TENANT_ID'),
    'client_id'     => env('GRAPH_CLIENT_ID'),
    'client_secret' => env('GRAPH_CLIENT_SECRET'),

    'default_sender' => env('GRAPH_SENDER'),
    'base'           => env('GRAPH_BASE', 'https://graph.microsoft.com/v1.0'),

    'save_to_sent' => env('GRAPH_SAVE_TO_SENT', true),

    'rate' => [
        'per_minute'  => env('GRAPH_RATE_PER_MINUTE', 30),
        'max_retries' => env('GRAPH_SEND_MAX_RETRIES', 10),
    ],

    'log_channel' => env('GRAPH_MAIL_LOG_CHANNEL', 'stack'),

    'ui' => [
        'enabled'    => env('GRAPH_UI_ENABLED', true),
        'path'       => env('GRAPH_UI_PATH', 'graph-mail'),
        'middleware' => ['web', 'auth'],
    ],

    'api' => [
        'prefix'     => env('GRAPH_API_PREFIX', 'graph-mail'),
        'middleware' => ['api', 'auth:sanctum'],
    ],

    'rabbitmq' => [
        'enabled'  => env('GRAPH_RABBIT_ENABLED', false),
        'host'     => env('RABBITMQ_HOST', '127.0.0.1'),
        'port'     => env('RABBITMQ_PORT', 5672),
        'user'     => env('RABBITMQ_USER', 'guest'),
        'password' => env('RABBITMQ_PASSWORD', 'guest'),
        'vhost'    => env('RABBITMQ_VHOST', 'graph-mail'),
        'queue'    => env('RABBITMQ_QUEUE', 'graph-mail.outbound'),
        'exchange' => env('RABBITMQ_EXCHANGE', 'graph-mail'),
        'routing'  => env('RABBITMQ_ROUTING', 'outbound'),
        'prefetch' => env('RABBITMQ_PREFETCH', 10),
        'ssl'      => env('RABBITMQ_SSL', false),
    ],

    'attachments_disk' => env('GRAPH_ATTACHMENTS_DISK', 'local'),
];