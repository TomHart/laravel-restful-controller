<?php

namespace TomHart\Restful;

use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Support\ServiceProvider;
use TomHart\Restful\Routing\RestfulResourceRegistrar;

class RestfulServiceProvider extends ServiceProvider
{

    /**
     * Bind the resource registrar
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(ResourceRegistrar::class, RestfulResourceRegistrar::class);
    }
}
