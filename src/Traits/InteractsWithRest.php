<?php


namespace TomHart\Restful\Traits;

trait InteractsWithRest
{
    /**
     * @inheritDoc
     */
    public function getOptionsUrl(): string
    {
        return route($this->getRouteName() . '.options');
    }
}
