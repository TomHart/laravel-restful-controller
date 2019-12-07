<?php


namespace TomHart\Restful\Tests\Classes;


use TomHart\Restful\AbstractRestfulController;

class RestfulController extends AbstractRestfulController
{

    /**
     * The views to render.
     * @var string[]
     */
    protected $views = [
        'index' => 'index',
        'show' => 'show',
        'store' => 'store'
    ];

    /**
     * What Model class to search for entities.
     * @return string
     */
    protected function getModelClass(): string
    {
        return ModelTest::class;
    }
}