<?php

namespace TomHart\Restful\Tests\Classes\Controllers;

use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\Models\Post;

class PostsController extends AbstractRestfulController
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
        return Post::class;
    }
}
