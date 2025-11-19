<?php

namespace ProgressiveStudios\GraphMail\Facades;

use Illuminate\Support\Facades\Facade;
use ProgressiveStudios\GraphMail\Services\GraphMailService;

class GraphMail extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GraphMailService::class;
    }
}
