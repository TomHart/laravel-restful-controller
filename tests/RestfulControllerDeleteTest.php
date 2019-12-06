<?php

namespace TomHart\Restful\Tests;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as SymResponse;
use TomHart\Restful\AbstractRestfulController;
use TomHart\Restful\Tests\Classes\ModelTest;

class RestfulControllerDeleteTest extends TestCase
{


    /**
     * Test a single record can be deleted.
     */
    public function testDeletingAModel()
    {

        $model = new ModelTest();
        $model->name = 'Test 1';
        $model->save();

        $response = $this->delete(route('model-test.destroy', [
            'model_test' => $model->id
        ]));

        $response->assertStatus(SymResponse::HTTP_NO_CONTENT);
        $this->assertTrue($response->isEmpty());
        $this->assertNull(ModelTest::find($model->id));
    }


    /**
     * Test a single record can be saved and redirected to the show page..
     */
    public function testDeletingNonExistentReturns404()
    {
        $response = $this->delete(route('model-test.destroy', [
            'model_test' => 1
        ]));

        $response->assertStatus(SymResponse::HTTP_NOT_FOUND);
    }


}