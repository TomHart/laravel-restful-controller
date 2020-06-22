<?php

namespace TomHart\Restful\Tests\Classes\Controllers;

use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\Models\ModelParentTest;

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
