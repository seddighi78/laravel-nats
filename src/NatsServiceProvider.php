<?php

namespace Seddighi78\LaravelNats;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Seddighi78\LaravelNats\Console\Commands\NatsSubscriberWork;
use Seddighi78\LaravelNats\Factories\NatsClientFactory;
use Seddighi78\LaravelNats\Factories\NatsClientFactoryInterface;

class NatsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/nats.php', 'nats'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/nats.php' => config_path('nats.php'),
        ]);

        App::singleton(NatsClientFactoryInterface::class, NatsClientFactory::class);

        if ($this->app->runningInConsole()) {
            $this->commands([NatsSubscriberWork::class]);
        }
    }
}
