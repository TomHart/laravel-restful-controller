<?php


namespace TomHart\Restful\Tests\Classes;

use TomHart\Restful\AbstractRestfulController;

class RestfulNoViewsController extends AbstractRestfulController
{

    /**
     * What Model class to search for entities.
     * @return string
     */
    protected function getModelClass(): string
    {
        return ModelTest::class;
    }
}