<?php

namespace TomHart\Restful\Tests\Consumer;

use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use TomHart\Restful\Builder;
use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class SelectingTest extends TestCase
{

    /**
     * Test we can select using the builder.
     * @throws UndefinedIndexException
     */
    public function testSimpleSelect(): void
    {
        $this->setPaginatedResponse(
            [
                [
                    'id' => 1
                ]
            ],
            15
        );

        $model = new ModelTest();
        $model->save();
        $models = Builder::model(ModelTest::class)->get();

        $this->assertInstanceOf(Collection::class, $models);
        $this->assertCount(1, $models);
        $this->assertInstanceOf(ModelTest::class, $models[0]);
        $this->assertEquals(1, $models[0]->id);
    }

    /**
     * Test we can select using the builder over multiple pages.
     * @throws UndefinedIndexException
     */
    public function testMultiPageSelect(): void
    {
        $data = [];
        for ($i = 1; $i <= 30; $i++) {
            $data[] = ['id' => $i];
        }

        $this->setPaginatedResponse($data);

        $model = new ModelTest();
        $model->save();
        $models = Builder::model(ModelTest::class)->get();

        $this->assertInstanceOf(Collection::class, $models);
        $this->assertCount(30, $models);
        $this->assertInstanceOf(ModelTest::class, $models[0]);
        $this->assertEquals(30, $models[29]->id);
    }

    /**
     * Test adding a where gets added to the query string
     * @throws UndefinedIndexException
     */
    public function testWhereAddsToQueryString(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS', 'http://localhost/model-tests')
            ->andReturn($this->buildOptionsResponse());

        $mock
            ->shouldReceive('request')
            ->withArgs(
                static function ($method, $url, $headers = []) {
                    if ($method !== 'get') {
                        return false;
                    }

                    if (!Str::endsWith($url, '?name=test')) {
                        return false;
                    }

                    return true;
                }
            );

        $model = new ModelTest();
        $model->save();
        $models = Builder::model(ModelTest::class)
            ->where('name', 'test')
            ->get();

        $this->assertInstanceOf(Collection::class, $models);
    }

    /**
     * Test adding a whereIn gets added to the query string
     * @throws UndefinedIndexException
     */
    public function testWhereInAddsToQueryString(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS', 'http://localhost/model-tests')
            ->andReturn($this->buildOptionsResponse());

        $mock
            ->shouldReceive('request')
            ->withArgs(
                static function ($method, $url, $headers) {
                    if ($method !== 'get') {
                        return false;
                    }

                    if (!Str::endsWith(
                        $url,
                        '?' . http_build_query(
                            [
                                'name' => [
                                    'test',
                                    'test2'
                                ]
                            ]
                        )
                    )) {
                        return false;
                    }

                    return true;
                }
            );

        $model = new ModelTest();
        $model->save();
        $models = Builder::model(ModelTest::class)
            ->whereIn('name', ['test', 'test2'])
            ->get();

        $this->assertInstanceOf(Collection::class, $models);
    }

    /**
     * Test adding an order gets added to the query string
     * @throws UndefinedIndexException
     */
    public function testOrderAddsToQueryString(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS', 'http://localhost/model-tests')
            ->andReturn($this->buildOptionsResponse());

        $mock
            ->shouldReceive('request')
            ->withArgs(
                static function ($method, $url, $headers) {
                    if ($method !== 'get') {
                        return false;
                    }

                    if (!Str::endsWith(
                        $url,
                        '?' . http_build_query(
                            [
                                'order' => [
                                    'column' => 'name',
                                    'direction' => 'desc'
                                ]
                            ]
                        )
                    )) {
                        return false;
                    }

                    return true;
                }
            );

        $model = new ModelTest();
        $model->save();
        $models = Builder::model(ModelTest::class)
            ->order('name', 'desc')
            ->get();

        $this->assertInstanceOf(Collection::class, $models);
    }

    /**
     * Test adding a limit gets added to the query string
     * @throws UndefinedIndexException
     */
    public function testLimitAddsToQueryString(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS', 'http://localhost/model-tests')
            ->andReturn($this->buildOptionsResponse());

        $mock
            ->shouldReceive('request')
            ->withArgs(
                static function ($method, $url, $headers) {
                    if ($method !== 'get') {
                        return false;
                    }

                    if (!Str::endsWith(
                        $url,
                        '?' . http_build_query(
                            [
                                'limit' => 15
                            ]
                        )
                    )) {
                        return false;
                    }

                    return true;
                }
            );

        $model = new ModelTest();
        $model->save();
        $models = Builder::model(ModelTest::class)
            ->limit(15)
            ->get();

        $this->assertInstanceOf(Collection::class, $models);
    }
}
