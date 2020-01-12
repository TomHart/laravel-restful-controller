<?php

namespace TomHart\Restful;

use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Routing\Router;
use TomHart\Restful\Concerns\HasLinks;

class LinkBuilder
{
    /**
     * Builds a link if possible
     *
     * @param HasLinks $model
     * @param string $routePart
     * @param Router $router
     * @param string|null $method
     * @return mixed[]|bool
     */
    public static function buildLink(HasLinks $model, string $routePart, Router $router, string $method = null)
    {
        $routeStub = $model->getRouteName();

        if ($routeStub === null) {
            return false;
        }

        if (!$model->getKeyName()) {
            return false;
        }

        // Make the route name, and check if it exists.
        $routeName = "$routeStub.$routePart";

        if (!($route = $router->getRoutes()->getByName($routeName))) {
            return false;
        }

        // Get any params needed to build the URL.
        $params = [];
        switch ($routePart) {
            case 'destroy':
            case 'update':
            case 'show':
                $params = [$model->getRouteKey() => $model->getAttribute((string)$model->getKeyName())];
                break;
            case 'show.extra':
                $params = [
                    $model->getRouteKey() => $model->getAttribute((string)$model->getKeyName()),
                    'extra' => $method
                ];
                break;
        }

        // Get the methods applicable to the route, ignoring HEAD and PATCH.
        $methods = collect($route->methods());
        $methods = $methods->filter(static function ($item) {
            return !in_array($item, ['HEAD', 'PATCH']);
        })->map(static function ($str) {
            return strtolower($str);
        });

        // If there's only 1, return just that, otherwise, return an array.
        if ($methods->count() === 1) {
            $methods = $methods->first();
        }

        // Add!
        try {
            return [
                'method' => $methods,
                'href' => [
                    'relative' => route($routeName, $params, false),
                    'absolute' => route($routeName, $params, true)
                ]
            ];
        } catch (UrlGenerationException $ex) {
            return false;
        }
    }
}
