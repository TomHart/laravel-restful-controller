<?php

namespace TomHart\Restful\Tests;

use Illuminate\Support\Facades\DB;
use TomHart\Restful\Tests\Classes\Models\ModelTest;

class PaginationTest extends TestCase
{

    /**
     * Fix issue 4
     */
    public function testPaginationWorks(): void
    {
        DB::beginTransaction();
        for ($i = 0; $i < 20; $i++) {
            (new ModelTest())->save();
        }
        DB::commit();

        $response = $this->get(route('model-test.index'), [
            'Accept' => 'application/json'
        ]);

        $data = $response->json();

        $response = $this->get($data['next_page_url'], [
            'Accept' => 'application/json'
        ]);

        $data1 = $response->json();

        $this->assertEquals(20, $data1['total']);
    }
}
