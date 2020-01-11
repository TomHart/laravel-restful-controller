<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use ReflectionException;
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

        $response = $this->get(route('model-has-links-tests.show', [
            $model->getRouteKey() => $model->id
        ]), [
            'Accept' => 'application/json'
        ]);

        $this->assertHasLinks($response);
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

        $response = $this->get(route('model-parent-tests.show', [
            'model_parent_test' => $parent->id
        ]), [
            'Accept' => 'application/json',
            'X-Load-Relationship' => 'children'
        ]);

        $this->assertHasLinks($response);
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
        $response1 = $this->get(route('model-parent-tests.show', [
            'model_parent_test' => $parent->id
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData(true);

        $this->assertJsonStructure([
            '_links' => [
                'relationships' => [
                    'child' => [
                        'create',
                        'store'
                    ],
                    'children' => [
                        'create',
                        'store'
                    ]
                ]
            ]
        ], $data);
    }

    /**
     * @throws ReflectionException
     */
    public function testRelationshipHasChildView()
    {
        $parent = new ModelParentTest();
        $parent->save();

        $relationshipLinks = $parent->buildRelationshipLinks();

        $this->assertJsonStructure([
            'child' => [
                'create',
                'store',
                'view' => [
                    'method',
                    'href' => [
                        'relative',
                        'absolute'
                    ]
                ]
            ],
            'children' => [
                'create',
                'store',
                'view' => [
                    'method',
                    'href' => [
                        'relative',
                        'absolute'
                    ]
                ]
            ]
        ], $relationshipLinks);
    }
}
