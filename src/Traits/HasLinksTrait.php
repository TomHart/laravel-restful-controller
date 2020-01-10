<?php
/**
 * Created by PhpStorm.
 * User: tom
 * Date: 28/03/19
 * Time: 12:47
 */

namespace TomHart\Restful\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Router;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionMethod;
use TomHart\Restful\Concerns\HasLinks;

trait HasLinksTrait
{

    /**
     * Add the links attribute to the model.
     */
    public function initializeHasLinksTrait(): void
    {
        $this->append('_links');
    }

    /**
     * Get the links for this model.
     * @return mixed[]
     * @throws ReflectionException
     */
    public function getLinksAttribute(): array
    {
        $links = $this->buildLinks();
        $relationships = $this->buildRelationshipLinks();
        if (!empty($relationships)) {
            $links['relationships'] = $relationships;
        }

        return $links;
    }

    /**
     * Returns the _links for the REST responses.
     *
     * @return mixed[]
     */
    public function buildLinks(): array
    {
        $routes = ['create', 'store', 'show', 'update', 'destroy'];
        $links = [];

        $router = app(Router::class);

        foreach ($routes as $routePart) {
            $link = $this->buildLink($this, $routePart, $router);

            if ($link) {
                $links[$routePart] = $link;
            }
        }

        return $links;
    }


    /**
     * Builds the links to create the relationship resources.
     *
     * @return mixed[]
     * @throws ReflectionException
     */
    public function buildRelationshipLinks(): array
    {
        $methods = get_class_methods($this);

        $links = [];
        $router = app(Router::class);

        foreach ($methods as $method) {
            $method2 = new ReflectionMethod($this, $method);
            $return = (string)$method2->getReturnType();

            if (empty($return)) {
                continue;
            }

            $isRelationship = is_subclass_of($return, Relation::class);

            if (!$isRelationship) {
                continue;
            }

            /** @var Relation $relationship */
            $relationship = $this->$method();

            $targetClass = $relationship->getRelated();

            if (!($targetClass instanceof HasLinks)) {
                continue;
            }

            $createLink = $this->buildLink($targetClass, 'create', $router);
            $storeLink = $this->buildLink($targetClass, 'store', $router);

            if ($createLink || $storeLink) {
                $links[$method] = [
                    'create' => $createLink,
                    'store' => $storeLink
                ];
            }
        }

        return $links;
    }


    /**
     * Builds a link if possible
     *
     * @param HasLinks $model
     * @param string $routePart
     * @param Router $router
     * @return mixed[]|bool
     */
    private function buildLink(HasLinks $model, string $routePart, Router $router)
    {
        $routeStub = $model->getRouteName();

        if (!$routeStub) {
            return false;
        }

        // Make the route name, and check if it exists.
        $routeName = "$routeStub.$routePart";

        if (!$router->has($routeName)) {
            return false;
        }

        // Get any params needed to build the URL.
        $params = [];
        switch ($routePart) {
            case 'destroy':
            case 'update':
            case 'show':
                $params = [$this->getRouteKey() => $this->id];
                break;
        }

        // Get the route.
        $route = $router->getRoutes()->getByName($routeName);

        if (!$route) {
            return false;
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
        return [
            'method' => $methods,
            'href' => route($routeName, $params, false)
        ];
    }

    /**
     * Return the name for the resource route this model
     * @return string|null
     */
    public function getRouteName(): ?string
    {
        $name = $this->getRouteKey();
        if (!$name) {
            return null;
        }
        return Str::kebab(Str::studly($name));
    }
}
