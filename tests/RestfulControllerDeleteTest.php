<?php

namespace TomHart\Restful\Tests;

use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\Tests\Classes\ModelTest;

class RestfulControllerDeleteTest extends TestCase
{


    /**
     * Test a single record can be deleted.
     */
    public function testDeletingAModel(): void
    {

        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->delete(route('model-test.destroy', [
            'model_test' => $model->id
        ]));

        $response->assertStatus(SymResponse::HTTP_NO_CONTENT);
        $this->assertTrue($response->baseResponse->isEmpty());
        $this->assertNull((new ModelTest())->find($model->id));
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testDeletingNonExistentReturns404(): void
    {
        $response = $this->delete(route('model-test.destroy', [
            'model_test' => 1
        ]));

        $response->assertStatus(SymResponse::HTTP_NOT_FOUND);
    }
}
