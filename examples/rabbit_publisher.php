<?php
require __DIR__.'/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
$conn = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest', '/');
$ch = $conn->channel();
$ch->queue_declare('graph-mail.outbound', false, true, false, false);
$payload = json_encode([
  'template_key'=>'welcome.user',
  'data'=>['name'=>'Alice'],
  'to'=>['alice@example.com'],
  'subject'=>'Optional override'
]);
$ch->basic_publish(new AMQPMessage($payload, ['content_type'=>'application/json']), '', 'graph-mail.outbound');
echo "Published\n";
$ch->close(); $conn->close();
