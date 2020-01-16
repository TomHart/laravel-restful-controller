<?php


namespace TomHart\Restful\Traits;

use Illuminate\Routing\Router;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\LinkBuilder;
use TomHart\Restful\Routing\Route;

trait InteractsWithRest
{
    /**
     * @inheritDoc
     */
    public function getOptionsRoute(): Route
    {
        /** @var HasLinks $this */
        $links = LinkBuilder::buildLink($this, 'options', app(Router::class));
        return Route::fromArray($links);
    }
}
