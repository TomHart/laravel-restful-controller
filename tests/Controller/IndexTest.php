<?php

namespace TomHart\Restful\Tests\Controller;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\Mock;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class IndexTest extends TestCase
{


    /**
     * Index should return a view providing
     * ones there and we're not asking for JSON.
     * We don't need to test what data is returned
     * as the JSON portion tests that as it's easier.
     */
    public function testIndexReturnsView(): void
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response */
        $response = $this->get(route('model-tests.index'));

        $this->responseIsHtml($response);

        $this->assertEquals(SymResponse::HTTP_OK, $response->baseResponse->getStatusCode());
    }


    /**
     * Index should return JSON if header is present.
     */
    public function testIndexReturnsJsonIfAskedFor(): void
    {
        $model = new ModelTest();
        $model->save();

        /** @var TestResponse $response1 */
        $response1 = $this->get(
            route('model-tests.index'),
            [
                'Accept' => 'application/json'
            ]
        );

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->responseIsJson($response1);

        $this->assertEquals(1, $data->total);
        $this->assertEquals(1, count($data->data));
    }

    /**
     * Index should return JSON if no view is defined.
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

        $this->responseIsJson($response1);

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
        $response1 = $this->get(
            route('model-tests.index') . '?' . http_build_query(
                [
                    'name' => 'Test 1'
                ]
            ),
            [
                'Accept' => 'application/json'
            ]
        );

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->responseIsJson($response1);

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
        $response1 = $this->get(
            route('model-tests.index'),
            [
                'Accept' => 'application/json'
            ]
        );

        /** @var JsonResponse $response */
        $response = $response1->baseResponse;
        $data = $response->getData();

        $this->responseIsJson($response1);

        $this->assertEquals(20, $data->total);
        $this->assertEquals(15, count($data->data));
        $this->assertNotNull($data->next_page_url);
    }

    /**
     * Test index supports limiting.
     */
    public function testIndexCanBeLimited(): void
    {
        /** @var Request|MockInterface $mockRequest */
        $mockRequest = $this->mock(Request::class);
        $mockRequest->shouldReceive('input')
            ->withNoArgs()
            ->andReturn([]);

        $mockRequest->shouldReceive('input')
            ->withArgs(['limit', null])
            ->andReturn(1);

        $mockRequest->shouldReceive('wantsJson')
            ->andReturn(true);

        /** @var Mock|AbstractRestfulController $mockController */
        $mockController = $this
            ->mock(AbstractRestfulController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mockBuilder = $this->mock(Builder::class);
        $mockBuilder->shouldReceive('paginate')
            ->withSomeOfArgs(1)
            ->andReturn(new LengthAwarePaginator([], 0, 1));

        $mockController
            ->shouldReceive('createModelQueryBuilder')
            ->andReturn($mockBuilder);

        $mockController->index($mockRequest);
    }
}
