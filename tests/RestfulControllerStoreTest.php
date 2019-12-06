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

class RestfulControllerStoreTest extends TestCase
{


    /**
     * Test a single record can be saved and returned as JSON.
     */
    public function testStoreReturnsJson()
    {

        $response = $this->post(route('model-test.store'), [
            'name' => 'Test 1'
        ], [
            'Accept' => 'application/json'
        ]);

        $data = (array)$response->getData();

        $response->assertStatus(SymResponse::HTTP_CREATED);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertEquals(1, $data['id']);
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testStoreRendersView()
    {

        $response = $this->post(route('model-test.store'), [
            'name' => 'Test 1'
        ]);

        $this->assertEquals(Response::class, get_class($response->baseResponse));
        $this->assertEquals(SymResponse::HTTP_CREATED, $response->getStatusCode());
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testStoreRedirectsToShow()
    {

        $response = $this->post(route('model-test2.store'), [
            'name' => 'Test 1'
        ]);

        /** @var RedirectResponse $r */
        $response = $response->baseResponse;

        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertEquals(SymResponse::HTTP_FOUND, $response->getStatusCode());

        $this->assertEquals(route('model-test2.show', ['model_test2' => 1]), $response->headers->get('location'));
    }


}