<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\Tests\Classes\Models\ModelTest;

class RestfulControllerUpdateTest extends TestCase
{


    /**
     * Test a single record can be saved and returned as JSON.
     */
    public function testUpdateReturnsJson(): void
    {

        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->put(route('model-tests.update', [
            'model_test' => $model->id
        ]), [
            'name' => 'Test 2'
        ], [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = (array)$response->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Test 2', $data['name']);
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testUpdateRedirectsToShow(): void
    {

        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response1 = $this->put(route('model-tests.update', [
            'model_test' => $model->id
        ]), [
            'name' => 'Test 2'
        ]);

        /** @var RedirectResponse $response */
        $response = $response1->baseResponse;

        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertEquals(SymResponse::HTTP_FOUND, $response->getStatusCode());

        $this->assertEquals(route('model-tests.show', ['model_test' => 1]), $response->headers->get('location'));
    }


}