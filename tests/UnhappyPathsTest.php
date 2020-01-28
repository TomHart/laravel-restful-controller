<?php

namespace TomHart\Restful\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\Classes\Models\ModelWithoutLinksTest;

class UnhappyPathsTest extends TestCase
{

    /**
     *
     */
    public function testNoRouteFromRequest(): void
    {
        $controller = $this->getMockForAbstractClass(AbstractRestfulController::class);
        $controller->method('getModelClass')->willReturn(ModelWithoutLinksTest::class);

        $request = new Request([], [
            'name' => 'Test'
        ]);

        $response = $controller->store($request);
        $this->assertStringStartsWith(
            'application/json',
            (string)$response->headers->get('Content-Type')
        );
    }

    /**
     *
     */
    public function testNoRouteNameFromRoute(): void
    {
        $controller = $this->getMockForAbstractClass(AbstractRestfulController::class);
        $controller->method('getModelClass')->willReturn(ModelWithoutLinksTest::class);

        $request = new Request([], [
            'name' => 'Test'
        ]);

        $route = new Route(['POST'], '/store', static function () {
        });
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $controller->store($request);
        $this->assertStringStartsWith(
            'application/json',
            (string)$response->headers->get('Content-Type')
        );
    }

    /**
     *
     */
    public function testSingleLevelRouteName(): void
    {
        $controller = $this->getMockForAbstractClass(AbstractRestfulController::class);
        $controller->method('getModelClass')->willReturn(ModelWithoutLinksTest::class);

        $request = new Request([], [
            'name' => 'Test'
        ]);

        $route = new Route(['POST'], '/store', static function () {
        });
        $route->name('test');
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        $response = $controller->store($request);
        $this->assertStringStartsWith(
            'application/json',
            (string)$response->headers->get('Content-Type')
        );
    }
}
