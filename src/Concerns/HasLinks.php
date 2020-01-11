<?php

namespace TomHart\Restful\Concerns;

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

    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey();

    /**
     * Get the primary key for the model.
     *
     * @return string|null
     */
    public function getKeyName();

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key);
}
