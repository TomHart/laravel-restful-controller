<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use TomHart\Restful\Tests\Classes\Models\ModelParentTest;
use TomHart\Restful\Tests\Classes\Models\ModelTest;

class LoadingRelationshipsTest extends TestCase
{

    /**
     * Show should be able to load and return relationships from the model.
     */
    public function testRelationshipsCanBeReturned(): void
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
        $data = (array)$response->getData();

        $this->responseIsJson($response1);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertArrayHasKey('children', $data);
        $this->assertCount(1, $data['children']);
        $this->assertEquals($parent->id, $data['id']);
    }

}
