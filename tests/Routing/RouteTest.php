<?php


namespace TomHart\Restful\Tests\Routing;

use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Routing\Route;
use TomHart\Restful\Tests\TestCase;

class RouteTest extends TestCase
{

    /**
     * @throws UndefinedIndexException
     */
    public function testGetHrefsReturnsAll(): void
    {
        $links = ['a' => 1, 'b' => 2];
        $route = new Route('get', $links);

        $this->assertEquals($links, $route->getHrefs());
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testGetHrefsErrorsIfNoIndex(): void
    {
        $this->expectException(UndefinedIndexException::class);
        $route = new Route('get', []);
        $route->getHrefs('abc');
    }
}
