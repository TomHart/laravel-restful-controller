<?php

namespace TomHart\Restful\Concerns;

use TomHart\Restful\Routing\Route;

interface Restful
{

    /**
     * Return the name for the resource route this model
     * @return string|null
     */
    public function getRouteName(): ?string;

    /**
     * Get the URL to get the options for this model.
     * @return Route
     */
    public function getOptionsRoute(): Route;
}
