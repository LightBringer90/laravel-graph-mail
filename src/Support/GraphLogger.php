<?php

namespace ProgressiveStudios\GraphMail\Support;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\Log;

class GraphLogger
{
    public static function channel(): LoggerInterface
    {
        return Log::channel(config('graph-mail.log_channel', 'graph-mail'));
    }
}
