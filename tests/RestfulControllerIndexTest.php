<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\Tests\Classes\ModelTest;

class RestfulControllerIndexTest extends TestCase
{


    /**
     * Test index returns a view.
     */
    public function testIndexReturnsView(): void
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response */
        $response = $this->get(route('model-test.index'));

        $this->assertEquals(Response::class, get_class($response->baseResponse));
        $this->assertEquals(SymResponse::HTTP_OK, $response->baseResponse->getStatusCode());
    }


    /**
     * Test index returns JSON if header present.
     */
    public function testIndexReturnsJsonIfAskedFor(): void
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-test.index'), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response));
        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Test index returns JSON if header present.
     */
    public function testIndexReturnsJsonIfNoViewAvailable(): void
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-test2.index'));

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response));
        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Test index can be filtered.
     */
    public function testIndexCanFilterData(): void
    {
        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $model = new ModelTest();
        $model->name = 'Test 2';
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-test.index') . '?' . http_build_query([
                'name' => 'Test 1'
            ]), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response));
        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Test index can be paginated.
     */
    public function testIndexCanBePaginated(): void
    {
        foreach (range(1, 20) as $num) {
            $model = new ModelTest();
            $model->name = "Test $num";
            $model->save();
        }

        /** @var TestResponse $response1 */
        $response1 = $this->get(route('model-test.index'), [
            'Accept' => 'application/json'
        ]);

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response));
        $this->assertEquals(20, $data->total);
        $this->assertEquals(15, count($data->data));
        $this->assertNotNull($data->next_page_url);
    }
}
