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

        Route::resource('model-test', 'TomHart\Restful\Tests\Classes\Controllers\RestfulController');
        Route::resource('has-links-test', 'TomHart\Restful\Tests\Classes\Controllers\HasLinksController');
        Route::resource('model-test2', 'TomHart\Restful\Tests\Classes\Controllers\RestfulNoViewsController');
        Route::resource('model-parent', 'TomHart\Restful\Tests\Classes\Controllers\RestfulParentController');
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
}
