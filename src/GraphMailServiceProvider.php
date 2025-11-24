<?php

namespace ProgressiveStudios\GraphMail;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class GraphMailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/graph-mail.php', 'graph-mail');

        $this->app->singleton(Services\GraphToken::class, fn() => new Services\GraphToken());
        $this->app->singleton(Services\GraphMailService::class, fn() => new Services\GraphMailService());
        $this->app->singleton(Services\RabbitService::class, fn() => new Services\RabbitService());
        $this->app->singleton(Services\MailRenderService::class, fn() => new Services\MailRenderService());
    }

    public function boot(): void
    {
        $this->publishes([__DIR__.'/../config/graph-mail.php' => config_path('graph-mail.php')], 'graph-mail-config');
        $this->publishes([__DIR__.'/../database/migrations/' => database_path('migrations')], 'graph-mail-migrations');
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/graph-mail/js'),
            __DIR__.'/../public' => public_path('vendor/graph-mail'),
        ], 'graph-mail-assets');
        $this->publishes([__DIR__.'/../resources/views' => resource_path('views/vendor/graph-mail')],
            'graph-mail-views');

        $this->loadRoutesFrom(__DIR__.'/routes-api.php');
        $this->loadRoutesFrom(__DIR__.'/routes-ui.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'graph-mail');

        // Register components namespace
        Blade::componentNamespace(
            'ProgressiveStudios\\GraphMail\\View\\Components\\Table\\',
            'ProgressiveStudios\\GraphMail\\View\\Components\\Mail\\',
            'graph-mail'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\RabbitConsume::class,
            ]);
        }
    }
}
