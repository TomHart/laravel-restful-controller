<?php

namespace TomHart\Restful\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Traits\HasLinksTrait;

class UnhappyPathsTest extends TestCase
{

    /**
     * Make sure having no route key doesn't break anything.
     */
    public function testNoRouteKey(): void
    {
        $mock = $this->getMockForTrait(HasLinksTrait::class);

        $this->assertNull($mock->getRouteName());
    }

    /**
     *
     */
    public function testNoRouteFromRequest(): void
    {
        $controller = $this->getMockForAbstractClass(AbstractRestfulController::class);
        $controller->method('getModelClass')->willReturn(ModelTest::class);

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
        $controller->method('getModelClass')->willReturn(ModelTest::class);

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
        $controller->method('getModelClass')->willReturn(ModelTest::class);

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
