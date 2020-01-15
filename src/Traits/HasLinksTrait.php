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
use TomHart\Restful\LinkBuilder;

trait HasLinksTrait
{

    /**
     * Append attributes to query when building a query.
     *
     * @param string[]|string $attributes
     * @return $this
     */
    abstract public function append($attributes);

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
        $routes = ['index', 'create', 'store', 'show', 'update', 'destroy'];
        $links = [];

        $router = app(Router::class);

        foreach ($routes as $routePart) {
            /** @var HasLinks $this */
            $link = LinkBuilder::buildLink($this, $routePart, $router);

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

            $createLink = LinkBuilder::buildLink($targetClass, 'create', $router);
            $storeLink = LinkBuilder::buildLink($targetClass, 'store', $router);
            /** @var HasLinks $this */
            $viewLink = LinkBuilder::buildLink($this, 'show.extra', $router, $method);

            $links[$method] = [
                'create' => $createLink,
                'store' => $storeLink,
                'view' => $viewLink
            ];
        }

        return $links;
    }

    /**
     * Return the name for the resource route this model
     * @return string|null
     */
    public function getRouteName(): ?string
    {
        $name = class_basename($this);
        return Str::plural(Str::kebab(Str::studly($name)));
    }


    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        $name = class_basename($this);
        return Str::singular(Str::snake(Str::studly($name)));
    }
}
