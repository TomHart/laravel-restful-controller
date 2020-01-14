<?php


namespace TomHart\Restful\Tests\Builder;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use TomHart\Restful\Builder;
use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class InsertingTest extends TestCase
{
    /**
     * @throws UndefinedIndexException
     */
    public function testCorrectParamsPassedWhenInserting(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS')
            ->andReturn($this->buildOptionsResponse());

        $mock->shouldReceive('request')
            ->withArgs(
                static function ($method, $url, $params) {
                    if ($method !== 'post') {
                        return false;
                    }

                    if ($params['form_params'] !== ['name' => 'test']) {
                        return false;
                    }

                    return true;
                }
            );

        Builder::model(ModelTest::class)
            ->insert(
                [
                    'name' => 'test'
                ]
            );
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testSuccessfullyInsertDataReturnsTrue(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS')
            ->andReturn($this->buildOptionsResponse());

        $mock->shouldReceive('request')
            ->andReturn(
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'id' => 1,
                            'name' => 'test'
                        ]
                    )
                )
            );

        $builder = Builder::model(ModelTest::class)
            ->insert(
                [
                    'name' => 'test'
                ]
            );

        $this->assertTrue($builder);
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testInsertingIsFalseWithout2XXResponse(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS')
            ->andReturn($this->buildOptionsResponse());

        $mock->shouldReceive('request')
            ->andReturn(
                new Response(
                    500,
                    [],
                    '{}'
                )
            );

        $builder = Builder::model(ModelTest::class)
            ->insert(
                [
                    'name' => 'test'
                ]
            );

        $this->assertFalse($builder);
    }
}
