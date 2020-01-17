<?php

namespace TomHart\Restful;

use DebugBar\DataCollector\MessagesCollector;
use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Support\ServiceProvider;
use TomHart\Restful\Concerns\Transformer;
use TomHart\Restful\Routing\RestfulResourceRegistrar;
use TomHart\Restful\Transformers\DefaultTransformer;

class RestfulServiceProvider extends ServiceProvider
{

    /**
     * Boot the service.
     */
    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/restful.php' => config_path('restful.php'),
            ],
            'config'
        );
    }

    /**
     * Bind the resource registrar
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(ResourceRegistrar::class, RestfulResourceRegistrar::class);

        $this->app->bind(Transformer::class, DefaultTransformer::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/restful.php', 'restful');

        $debugBar = app('debugbar');
        $debugBar->addCollector(new MessagesCollector('restful_calls'));
    }
}
