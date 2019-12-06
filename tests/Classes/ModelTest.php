<?php

namespace TomHart\Restful\Tests\Classes;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 */
class ModelTest extends Model
{

    public function getRouteKey()
    {
        return 'model_test';
    }

    public function getRouteKeyName()
    {
        return 'model_test';
    }
}