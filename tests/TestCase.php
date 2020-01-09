<?php

namespace TomHart\Restful\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

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
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        Route::resource('model-test', 'TomHart\Restful\Tests\Classes\RestfulController');
        Route::resource('model-test2', 'TomHart\Restful\Tests\Classes\RestfulNoViewsController');
        Route::resource('model-parent', 'TomHart\Restful\Tests\Classes\RestfulParentController');
    }

    /**
     * Test that a response is JSON.
     * @param TestResponse $response
     */
    protected function responseIsJson(TestResponse $response): void
    {
        $this->assertStringStartsWith('application/json', (string)$response->baseResponse->headers->get('Content-Type'));
    }

    /**
     * Test that a response is HTML.
     * @param TestResponse $response
     */
    protected function responseIsHtml(TestResponse $response): void
    {
        $this->assertStringStartsWith('text/html', (string)$response->baseResponse->headers->get('Content-Type'));
    }
}
