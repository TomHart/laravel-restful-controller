<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use TomHart\Restful\Tests\Classes\Models\ModelHasLinksTest;
use TomHart\Restful\Tests\Classes\Models\ModelParentTest;
use TomHart\Restful\Tests\Classes\Models\ModelTest;

class HasLinksTest extends TestCase
{

    /**
     * Check _links in response
     */
    public function testModelHasLinks(): void
    {

        $model = new ModelHasLinksTest();
        $model->name = 'Test 1';
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('has-links-test.show', [
            $model->getRouteKey() => $model->id
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = (array)$response->getData();

        $this->assertArrayHasKey('_links', $data);

        // Check we have these combinations.
        $actions = ['create', 'store', 'show', 'update', 'destroy'];
        $keys = ['method', 'href'];

        foreach ($actions as $action) {
            $this->assertObjectHasAttribute($action, $data['_links']);
            foreach ($keys as $key) {
                $this->assertObjectHasAttribute($key, $data['_links']->$action);
            }
        }
    }

    /**
     * Check relationships has _links included
     */
    public function testRelationshipsHasLinks(): void
    {

        $child = new ModelTest();
        $child->name = 'Child';
        $child->save();

        $parent = new ModelParentTest();
        $parent->name = 'Parent';
        $parent->save();
        $parent->children()->save($child);

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-parent.show', [
            'model_parent' => $parent->id
        ]), [
            'Accept' => 'application/json',
            'X-Load-Relationship' => 'children'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $data = $data->children[0];
        $this->assertObjectHasAttribute('_links', $data);

        // Check we have these combinations.
        $actions = ['create', 'store', 'show', 'update', 'destroy'];
        $keys = ['method', 'href'];

        foreach ($actions as $action) {
            $this->assertObjectHasAttribute($action, $data->_links);
            foreach ($keys as $key) {
                $this->assertObjectHasAttribute($key, $data->_links->$action);
            }
        }
    }

    /**
     *
     */
    public function testCreateRelationshipsLinks(): void
    {

        $parent = new ModelParentTest();
        $parent->name = 'Parent';
        $parent->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-parent.show', [
            'model_parent' => $parent->id
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->assertObjectHasAttribute('_links', $data);
        $this->assertObjectHasAttribute('relationships', $data->_links);

        $relationships = ['child', 'children'];
        $methods = ['create', 'store'];

        foreach ($relationships as $relationship) {
            $this->assertObjectHasAttribute($relationship, $data->_links->relationships);
            foreach ($methods as $method) {
                $this->assertObjectHasAttribute($method, $data->_links->relationships->$relationship);
            }
        }
    }
}
