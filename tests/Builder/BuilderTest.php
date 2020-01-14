<?php

namespace TomHart\Restful\Tests\Builder;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use TomHart\Restful\Builder;
use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class BuilderTest extends TestCase
{

    /**
     * model() should return a builder instance.
     */
    public function testModelReturnsBuilder(): void
    {
        $builder = Builder::model(ModelTest::class);
        $this->assertInstanceOf(Builder::class, $builder);
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testExtractErrorsIfNoData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$json doesn\'t contain a data key');
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS')
            ->andReturn($this->buildOptionsResponse());

        $mock->shouldReceive('request')
            ->withSomeOfArgs('get')
            ->andReturn(new Response(200, ['X-Abc'], json_encode(['abc' => 123]), '1.2'));

        Builder::model(ModelTest::class)->get();
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testNoIndexRouteErrors(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('Cannot find link to index route');
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS')
            ->andReturn(
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'create' => [
                                'method' => 'get',
                                'href' => [
                                    'relative' => '/model-tests',
                                    'absolute' => 'https://api.example.com/model-tests'
                                ]
                            ]
                        ]
                    )
                )
            );

        Builder::model(ModelTest::class)->get();
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testBuilderHandlesNoBodyBeingReturned(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('OPTIONS')
            ->andReturn($this->buildOptionsResponse());

        $body = $this->mock(stdClass::class);
        $body->shouldReceive('getContents')
            ->andReturnNull();

        $responseMock = $this->mock(Response::class);
        $responseMock->shouldReceive('getBody')
            ->andReturn($body);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('get')
            ->andReturn($responseMock);

        Builder::model(ModelTest::class)->get();
    }
}
