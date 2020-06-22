<?php

namespace TomHart\Restful\Tests\Controller;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\Tests\Classes\ModelTest;
use TomHart\Restful\Tests\TestCase;

class StoreTest extends TestCase
{


    /**
     * Test a single record can be saved and returned as JSON.
     */
    public function testStoreReturnsJson(): void
    {

        /** @var TestResponse $response1 */
        $response1 = $this->post(route('model-tests.store'), [
            'name' => 'Test 1'
        ], [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = (array)$response->getData();

        $response1->assertStatus(SymResponse::HTTP_CREATED);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('number', $data);
        $this->assertEquals(1, $data['id']);
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testStoreRendersView(): void
    {

        $response = $this->post(route('model-tests.store'), [
            'name' => 'Test 1'
        ]);

        $this->assertEquals(Response::class, get_class($response->baseResponse));
        $this->assertEquals(SymResponse::HTTP_CREATED, $response->baseResponse->getStatusCode());
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testStoreRedirectsToShow(): void
    {

        $response1 = $this->post(route('model-test2.store'), [
            'name' => 'Test 1'
        ]);

        /** @var RedirectResponse $response */
        $response = $response1->baseResponse;

        $this->assertEquals(RedirectResponse::class, get_class($response));
        $this->assertEquals(SymResponse::HTTP_FOUND, $response->getStatusCode());

        $this->assertEquals(route('model-test2.show', ['model_test2' => 1]), $response->headers->get('location'));
    }
}
