<?php

namespace ProgressiveStudios\GraphMail;

use ProgressiveStudios\GraphMail\Support\GraphLogger;

if (!function_exists('graph_mail_logger')) {
    function graph_mail_logger()
    {
        return GraphLogger::channel();
    }
}
