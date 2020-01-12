<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Route;
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
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('view.paths', [
            __DIR__ . '/views'
        ]);
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
        Route::resource('model-without-links-tests', 'TomHart\Restful\Tests\Classes\Controllers\WithoutHasLinksController');

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

        $this->assertJsonStructure([
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
        ], $response->json());
    }

    /**
     * Response has the _links
     * @param TestResponse $response
     */
    protected function assertHasLinks(TestResponse $response)
    {
        $this->assertJsonStructure([
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
        ], $response->json());
    }
}
