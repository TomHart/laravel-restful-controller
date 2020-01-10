<?php

namespace TomHart\Restful\Routing;

use Illuminate\Routing\ResourceRegistrar;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

class RestfulResourceRegistrar extends ResourceRegistrar
{


    public function __construct(Router $router)
    {
        parent::__construct($router);

        $this->resourceDefaults[] = 'showExtra';
    }

    /**
     * Add the show-extra method for a resourceful route.
     *
     * @param string $name
     * @param string $base
     * @param string $controller
     * @param mixed[] $options
     * @return Route
     */
    protected function addResourceShowExtra($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name) . '/{' . $base . '}/{extra}';

        $action = $this->getResourceAction($name, $controller, 'show', $options);

        $action['as'] .= '.extra';

        return $this->router->get($uri, $action)->where('extra', '.*');
    }
}
