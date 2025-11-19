<?php

namespace Tests\Feature;

use Orchestra\Testbench\TestCase;
use ProgressiveStudios\GraphMail\GraphMailServiceProvider;

class RoutesTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [GraphMailServiceProvider::class];
    }

    public function test_api_routes_register()
    {
        $this->get('/api/graph-mail/health')->assertStatus(200);
    }
}
