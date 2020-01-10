<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use TomHart\Restful\Tests\Classes\Models\ModelParentTest;
use TomHart\Restful\Tests\Classes\Models\ModelTest;

class AccessingChildrenTest extends TestCase
{

    /**
     * Test we can access a models children through a route.
     */
    public function testIteratingThroughChildren(): void
    {

        $child1 = new ModelTest();
        $child1->name = 'Child 1';
        $child1->save();

        $child2 = new ModelTest();
        $child2->name = 'Child 2';
        $child2->save();

        $parent = new ModelParentTest();
        $parent->name = 'Parent';
        $parent->save();
        $parent->children()->save($child1);
        $parent->children()->save($child2);

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-parent.show.extra', [
            'model_parent' => $parent->id,
            'extra' => 'children'
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = (array)$response->getData();

        $this->responseIsJson($response1);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(2, $data['data']);

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-parent.show.extra', [
            'model_parent' => $parent->id,
            'extra' => 'children[1]'
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = (array)$response->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertEquals($child2->id, $data['id']);
        $this->assertEquals($child2->name, $data['name']);
    }

    /**
     * Test we can access a models single relationship through a route.
     */
    public function testSingleRelationship(): void
    {

        $child1 = new ModelTest();
        $child1->name = 'Child 1';
        $child1->save();

        $parent = new ModelParentTest();
        $parent->name = 'Parent';
        $parent->child()->associate($child1);
        $parent->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-parent.show.extra', [
            'model_parent' => $parent->id,
            'extra' => 'child'
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->responseIsJson($response1);
        $this->assertEquals($child1->id, $data->id);
    }

    /**
     * Test accessing a property throws an error
     */
    public function testAccessingPropertyThrowsError(): void
    {

        $parent = new ModelParentTest();
        $parent->name = 'Parent';
        $parent->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-parent.show.extra', [
            'model_parent' => $parent->id,
            'extra' => 'name'
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->assertEquals('BadMethodCallException', $data->exception);
    }
}
