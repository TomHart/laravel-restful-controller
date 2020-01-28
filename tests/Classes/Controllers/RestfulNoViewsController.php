<?php


namespace TomHart\Restful\Tests\Classes\Controllers;

use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\Models\ModelTest2;

class RestfulNoViewsController extends AbstractRestfulController
{

    /**
     * What Model class to search for entities.
     * @return string
     */
    protected function getModelClass(): string
    {
        return ModelTest2::class;
    }
}