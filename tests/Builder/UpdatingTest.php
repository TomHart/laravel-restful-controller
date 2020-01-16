<?php


namespace TomHart\Restful\Tests\Builder;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use TomHart\Restful\Builder;
use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class UpdatingTest extends TestCase
{
    /**
     * @throws UndefinedIndexException
     */
    public function testCorrectParamsPassedWhenUpdating(): void
    {
        $mock = $this->mock(Client::class);

        $mock->shouldReceive('request')
            ->withArgs(
                static function ($method, $url, $params) {
                    if ($method === 'options' && Str::endsWith($url, '?id=1')) {
                        return true;
                    }
                    if ($method === 'put' &&
                        Str::endsWith(
                            $url,
                            '/1'
                        ) &&
                        $params['form_params'] === ['name' => 'test']) {
                        return true;
                    }

                    return false;
                }
            )
            ->andReturn($this->buildOptionsResponse());

        Builder::model(ModelTest::class)
            ->update(
                1,
                [
                    'name' => 'test'
                ]
            );
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testSuccessfullyUpdatingDataReturnsTrue(): void
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
            ->update(
                1,
                [
                    'name' => 'test'
                ]
            );

        $this->assertTrue($builder);
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testUpdatingReturnsFalseWithout2XXResponse(): void
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
            ->update(
                1,
                [
                    'name' => 'test'
                ]
            );

        $this->assertFalse($builder);
    }
}
