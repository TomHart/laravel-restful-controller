<?php

namespace TomHart\Restful\Tests\Classes;

use TomHart\Restful\AbstractRestfulController;

class RestfulParentController extends AbstractRestfulController
{

    /**
     * What Model class to search for entities.
     * @return string
     */
    protected function getModelClass(): string
    {
        return ModelParentTest::class;
    }
}