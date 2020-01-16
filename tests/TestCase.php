<?php

namespace TomHart\Restful\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TomHart\Restful\RestfulServiceProvider;

abstract class TestCase extends OrchestraTestCase
{

    /**
     * Define environment setup.
     *
     * @param Application $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set(
            'database.connections.testbench',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ]
        );

        $app['config']->set(
            'view.paths',
            [
                __DIR__ . '/views'
            ]
        );
    }

    /**
     * Get all the providers needed for the tests.
     * @param Application $app
     * @return string[]
     */
    public function getPackageProviders($app): array
    {
        return [RestfulServiceProvider::class];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        Route::resource('model-tests', 'TomHart\Restful\Tests\Classes\Controllers\RestfulController');
        Route::resource('model-has-links-tests', 'TomHart\Restful\Tests\Classes\Controllers\HasLinksController');
        Route::resource('model-test2', 'TomHart\Restful\Tests\Classes\Controllers\RestfulNoViewsController');
        Route::resource('model-parent-tests', 'TomHart\Restful\Tests\Classes\Controllers\RestfulParentController');
        Route::resource(
            'model-without-links-tests',
            'TomHart\Restful\Tests\Classes\Controllers\WithoutHasLinksController'
        );

        Route::resource('comments', 'TomHart\Restful\Tests\Classes\Controllers\Controller');
        Route::resource('posts', 'TomHart\Restful\Tests\Classes\Controllers\PostsController');
    }

    /**
     * Test that a response is JSON.
     * @param TestResponse $response
     */
    protected function responseIsJson(TestResponse $response): void
    {
        $this->assertStringStartsWith(
            'application/json',
            (string)$response->baseResponse->headers->get('Content-Type')
        );
    }

    /**
     * Test that a response is HTML.
     * @param TestResponse $response
     */
    protected function responseIsHtml(TestResponse $response): void
    {
        $this->assertStringStartsWith(
            'text/html',
            (string)$response->baseResponse->headers->get('Content-Type')
        );
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array $structure
     * @param array $responseData
     * @return $this
     */
    public function assertJsonStructure(array $structure, array $responseData)
    {
        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                $this->assertIsArray($responseData);
                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                $this->assertArrayHasKey($key, $responseData);
                $this->assertJsonStructure($structure[$key], $responseData[$key]);
            } else {
                $this->assertArrayHasKey($value, $responseData);
            }
        }
        return $this;
    }

    /**
     * Get the strings we need to search for when examining the JSON.
     *
     * @param string $key
     * @param string $value
     * @return array
     */
    protected function jsonSearchStrings($key, $value)
    {
        $needle = substr(json_encode([$key => $value]), 1, -1);
        return [
            $needle . ']',
            $needle . '}',
            $needle . ',',
        ];
    }

    /**
     * Is it a pagination response?
     * @param TestResponse $response
     */
    protected function assertIsPagination(TestResponse $response)
    {
        $this->assertJsonStructure(
            [
                'current_page',
                'data',
                'first_page_url',
                'from',
                'last_page',
                'last_page_url',
                'next_page_url',
                'path',
                'per_page',
                'prev_page_url',
                'to',
                'total'
            ],
            $response->json()
        );
    }

    /**
     * Response has the _links
     * @param TestResponse $response
     */
    protected function assertHasLinks(TestResponse $response)
    {
        $this->assertJsonStructure(
            [
                '_links' => [
                    'index' => [
                        'method',
                        'href' => [
                            'relative',
                            'absolute'
                        ]
                    ],
                    'create' => [
                        'method',
                        'href' => [
                            'relative',
                            'absolute'
                        ]
                    ],
                    'store' => [
                        'method',
                        'href' => [
                            'relative',
                            'absolute'
                        ]
                    ],
                    'show' => [
                        'method',
                        'href' => [
                            'relative',
                            'absolute'
                        ]
                    ],
                    'update' => [
                        'method',
                        'href' => [
                            'relative',
                            'absolute'
                        ]
                    ],
                    'destroy' => [
                        'method',
                        'href' => [
                            'relative',
                            'absolute'
                        ]
                    ]
                ]
            ],
            $response->json()
        );
    }

    /**
     * Mock a paginated response.
     * @param array $responseData
     * @param int $perPage
     * @return MockInterface
     */
    protected function setPaginatedResponse(array $responseData = [], $perPage = 15): MockInterface
    {
        //$mock = $this->mock(Client::class);
        $mock = $this->mock(Client::class);

        $responses = [
            $this->buildOptionsResponse()
        ];
        $responses = array_merge($responses, $this->buildPaginatedResponses($responseData, $perPage));
        $mock
            ->shouldReceive('request')
            ->times(count($responses))
            ->andReturn(...$responses);
        return $mock;
    }

    /**
     * Build some paginated responses.
     * @param array $responseData
     * @param int $perPage
     * @return array
     */
    protected function buildPaginatedResponses(array $responseData, int $perPage)
    {
        $chunked = array_chunk($responseData, $perPage);
        $responses = [];
        foreach ($chunked as $index => $chunk) {
            $responses[] = new Response(
                200,
                [],
                json_encode(
                    [
                        'current_page' => ($index + 1),
                        'data' => $chunk,
                        'first_page_url' => '?page=1',
                        'from' => 1 + ($perPage * $index),
                        'last_page' => ceil(count($responseData) / $perPage),
                        'last_page_url' => '?page=' . ceil(count($responseData) / $perPage),
                        'next_page_url' => '?page=' . ($index + 2),
                        'path' => '',
                        'per_page' => $index * $perPage,
                        'prev_page_url' => '?page=' . $index,
                        'to' => $perPage + ($perPage * $index),
                        'total' => count($chunked)
                    ]
                )
            );
        }

        return $responses;
    }

    protected function buildOptionsResponse(): Response
    {
        return new Response(
            200,
            [],
            json_encode(
                [
                    'index' => [
                        'method' => 'get',
                        'href' => [
                            'relative' => '/model-tests',
                            'absolute' => 'https://api.example.com/model-tests'
                        ]
                    ],
                    'store' => [
                        'method' => 'post',
                        'href' => [
                            'relative' => '/model-tests',
                            'absolute' => 'https://api.example.com/model-tests'
                        ]
                    ],
                    'update' => [
                        'method' => 'put',
                        'href' => [
                            'relative' => '/model-tests/1',
                            'absolute' => 'https://api.example.com/model-tests/1'
                        ]
                    ],
                    'destroy' => [
                        'method' => 'delete',
                        'href' => [
                            'relative' => '/model-tests/1',
                            'absolute' => 'https://api.example.com/model-tests/1'
                        ]
                    ]
                ]
            )
        );
    }

    /**
     * @return MockInterface
     */
    protected function mockOptions(): MockInterface
    {
        $mock = $this->mock(Client::class);
        $mock
            ->shouldReceive('request')
            ->once()
            ->withSomeOfArgs('options', '/model-tests')
            ->andReturn(
                new Response(
                    200,
                    [],
                    json_encode(
                        [
                            'index' => [
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

        return $mock;
    }
}
