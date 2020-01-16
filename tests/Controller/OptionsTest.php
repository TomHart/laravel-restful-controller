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
        $response = $this->json('options', route('model-tests.options'));

        $data = $response->json();
        $this->assertArrayHasKey('index', $data);
        $this->assertArrayHasKey('create', $data);
        $this->assertArrayHasKey('store', $data);
    }
    /**
     * Test OPTIONS returns show, update, and delete if an ID is supplied.
     */
    public function testOptionsWithIdReturnsShowUpdateDelete(): void
    {
        $response = $this->options(route('model-tests.options'), ['id' => 1]);

        $this->assertJsonStructure([
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

        ], $response->json());
    }

    /**
     * Test OPTIONS without HasLinks errors
     */
    public function testOptionsWithoutHasLinks(): void
    {
        $response = $this->json('options', route('model-without-links-tests.options'));
        $this->assertEquals(
            'OPTIONS only works for models implementing HasLinks',
            $response->exception->getMessage()
        );
    }
}
