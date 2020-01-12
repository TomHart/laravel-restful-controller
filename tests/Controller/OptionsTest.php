<?php

namespace TomHart\Restful\Tests\Controller;

use TomHart\Restful\Tests\TestCase;

class OptionsTest extends TestCase
{


    /**
     * Test OPTIONS returns _links
     */
    public function testOptionsReturnsJSON(): void
    {
        $response = $this->json('OPTIONS', route('model-tests.options'));

        $data = $response->json();
        $this->assertArrayHasKey('index', $data);
        $this->assertArrayHasKey('create', $data);
        $this->assertArrayHasKey('store', $data);
    }

    /**
     * Test OPTIONS without HasLinks errors
     */
    public function testOptionsWithoutHasLinks(): void
    {
        $response = $this->json('OPTIONS', route('model-without-links-tests.options'));
        $this->assertEquals(
            'OPTIONS only works for models implementing HasLinks',
            $response->exception->getMessage()
        );
    }
}
