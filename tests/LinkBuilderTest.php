<?php

namespace TomHart\Restful\Tests;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\LinkBuilder;

class LinkBuilderTest extends TestCase
{

    /**
     * @var Router
     */
    private $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = $this->mock(Router::class);
    }

    /**
     * Test no getRouteName doesn't cause an error.
     */
    public function testNoRouteName()
    {
        $mock = $this->mock(HasLinks::class);
        $mock
            ->shouldReceive('getRouteName')
            ->once()
            ->andReturnNull();

        $link = LinkBuilder::buildLink($mock, 'part', $this->router);

        $this->assertFalse($link);
    }

    /**
     * Test no getKeyName doesn't cause an error.
     */
    public function testNoKeyName()
    {
        $mock = $this->mock(HasLinks::class);

        $mock
            ->shouldReceive('getRouteName')
            ->once()
            ->andReturn('abc');

        $mock
            ->shouldReceive('getKeyName')
            ->once()
            ->andReturnNull();


        $link = LinkBuilder::buildLink($mock, 'part', $this->router);

        $this->assertFalse($link);
    }

    /**
     * Test not having a route in the router doesn't cause an error.
     */
    public function testNoRouteInRouter()
    {
        $mock = $this->mock(HasLinks::class);

        $mock
            ->shouldReceive('getRouteName')
            ->once()
            ->andReturn('abc');

        $mock
            ->shouldReceive('getKeyName')
            ->once()
            ->andReturn('id');

        $routeCollection = new RouteCollection();

        $this->router->shouldReceive('getRoutes')
            ->once()
            ->andReturn($routeCollection);

        $link = LinkBuilder::buildLink($mock, 'part', $this->router);

        $this->assertFalse($link);
    }
}
