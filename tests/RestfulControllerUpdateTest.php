<?php

namespace TomHart\Restful\Tests;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\ModelTest;

class RestfulControllerUpdateTest extends TestCase
{


    /**
     * Test a single record can be saved and returned as JSON.
     */
    public function testUpdateReturnsJson()
    {

        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->put(route('model-test.update', [
            'model_test' => $model->id
        ]), [
            'name' => 'Test 2'
        ], [
            'Accept' => 'application/json'
        ]);

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
    public function testUpdateRedirectsToShow()
    {

        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->put(route('model-test.update', [
            'model_test' => $model->id
        ]), [
            'name' => 'Test 2'
        ]);

        /** @var RedirectResponse $r */
        $response = $response->baseResponse;

        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertEquals(SymResponse::HTTP_FOUND, $response->getStatusCode());

        $this->assertEquals(route('model-test.show', ['model_test' => 1]), $response->headers->get('location'));
    }


}