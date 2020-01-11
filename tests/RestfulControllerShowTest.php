<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\Tests\Classes\Models\ModelTest;

class RestfulControllerShowTest extends TestCase
{

    /**
     * Test a single record can be shown..
     */
    public function testShowReturnsJson(): void
    {
        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-tests.show', [
            'model_test' => $model->id
        ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = (array)$response->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertEquals($model->id, $data['id']);
    }

    /**
     * Test a single record can be shown..
     */
    public function testShowReturnsView(): void
    {
        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->get(route('model-tests.show', [
            'model_test' => $model->id
        ]));

        $this->responseIsHtml($response);
        $this->assertEquals(SymResponse::HTTP_OK, $response->baseResponse->getStatusCode());
    }
}
