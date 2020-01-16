<?php

namespace TomHart\Restful\Tests\Builder;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use TomHart\Restful\Builder;
use TomHart\Restful\Exceptions\UndefinedIndexException;
use TomHart\Restful\Tests\Classes\Models\ModelTest;
use TomHart\Restful\Tests\TestCase;

class LoggingTest extends TestCase
{


    /**
     * @throws UndefinedIndexException
     */
    public function testSimpleLoggingWorks(): void
    {
        $mockGuzzle = $this
            ->mock(Client::class)
            ->makePartial();

        $mockGuzzle
            ->shouldReceive('request')
            ->andReturn($this->buildOptionsResponse());

        $mockLogger = $this->mock(LoggerInterface::class);

        $mockLogger
            ->shouldReceive('info')
            ->withArgs(['REST-CALL: options to http://localhost/model-tests']);

        $mockLogger
            ->shouldReceive('info')
            ->withArgs(['REST-CALL: get to https://api.example.com/model-tests']);

        Builder::model(ModelTest::class)->get();
    }

    /**
     * @throws UndefinedIndexException
     */
    public function testNoLoggingDoneIfConfigFalse(): void
    {
        $mockGuzzle = $this
            ->mock(Client::class)
            ->makePartial();
        $mockGuzzle
            ->shouldReceive('request')
            ->andReturn($this->buildOptionsResponse());

        $this->mock(LoggerInterface::class);

        $this->app['config']->set('restful.logging', false);

        Builder::model(ModelTest::class)->get();
    }
}
