<?php


namespace TomHart\Restful\Tests\Builder;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use TomHart\Restful\Builder;
use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class DeletingTest extends TestCase
{
    /**
     * @throws UndefinedIndexException
     */
    public function testCorrectParamsPassedWhenDeleting(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('options')
            ->andReturn($this->buildOptionsResponse());

        $mock->shouldReceive('request')
            ->withArgs(
                static function ($method, $url) {
                    if ($method !== 'delete') {
                        return false;
                    }

                    if (!Str::endsWith($url, '/1')) {
                        return false;
                    }

                    return true;
                }
            );

        Builder::model(ModelTest::class)
            ->delete(1);
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testSuccessfullyDeletingDataReturnsTrue(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('options')
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
            ->delete(1);

        $this->assertTrue($builder);
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testDeleteReturnsFalseWithout2XXResponse(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withSomeOfArgs('options')
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
            ->delete(1);

        $this->assertFalse($builder);
    }
}
