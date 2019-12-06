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

class RestfulControllerTest extends TestCase
{


    /**
     * Test index returns a view.
     */
    public function testIndexReturnsView()
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response */
        $response = $this->get(route('model-test.index'));

        $this->assertEquals(Response::class, get_class($response->baseResponse));
        $this->assertEquals(SymResponse::HTTP_OK, $response->getStatusCode());
    }


    /**
     * Test index returns JSON if header present.
     */
    public function testIndexReturnsJsonIfAskedFor()
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response */
        $response = $this->get(route('model-test.index'), [
            'Accept' => 'application/json'
        ]);

        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response->baseResponse));
        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Test index returns JSON if header present.
     */
    public function testIndexReturnsJsonIfNoViewAvailable()
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response */
        $response = $this->get(route('model-test2.index'));

        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response->baseResponse));
        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Test index can be filtered.
     */
    public function testIndexCanFilterData()
    {
        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $model = new ModelTest();
        $model->name = 'Test 2';
        $model->save();

        $response = $this->get(route('model-test.index') . '?' . http_build_query([
                'name' => 'Test 1'
            ]), [
            'Accept' => 'application/json'
        ]);

        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response->baseResponse));
        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Test index can be paginated.
     */
    public function testIndexCanBePaginated()
    {
        foreach (range(1, 20) as $num) {
            $model = new ModelTest();
            $model->name = "Test $num";
            $model->save();
        }

        $response = $this->get(route('model-test.index'), [
            'Accept' => 'application/json'
        ]);

        $data = $response->getData();

        $this->assertEquals(JsonResponse::class, get_class($response->baseResponse));
        $this->assertEquals(20, $data->total);
        $this->assertEquals(15, count($data->data));
        $this->assertNotNull($data->next_page_url);
    }

}
