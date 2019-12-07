<?php

namespace TomHart\Restful\Tests\Classes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
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