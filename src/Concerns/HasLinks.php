<?php

namespace TomHart\Restful\Concerns;

use Illuminate\Support\Collection;

interface HasLinks
{

    /**
     * Returns the _links for the REST responses.
     *
     * @return mixed[]
     */
    public function buildLinks(): array;

    /**
     * Builds the links to create the relationship resources.
     *
     * @return mixed[]
     */
    public function buildRelationshipLinks(): array;

    /**
     * Return the name for the resource route this model
     * @return string|null
     */
    public function getRouteName(): ?string;
}
