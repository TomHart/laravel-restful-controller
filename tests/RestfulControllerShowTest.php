<?php

namespace TomHart\Restful\Tests;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\ModelTest;

class RestfulControllerShowTest extends TestCase
{


    /**
     * Test a single record can be shown..
     */
    public function testShowReturnsJson()
    {
        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->get(route('model-test.show', [
            'model_test' => $model->id
        ]), [
            'Accept' => 'application/json'
        ]);

        $data = (array)$response->getData();

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertEquals($model->id, $data['id']);
    }

    /**
     * Test a single record can be shown..
     */
    public function testShowReturnsView()
    {
        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->get(route('model-test.show', [
            'model_test' => $model->id
        ]));

        $this->assertEquals(Response::class, get_class($response->baseResponse));
        $this->assertEquals(SymResponse::HTTP_OK, $response->getStatusCode());
    }

}