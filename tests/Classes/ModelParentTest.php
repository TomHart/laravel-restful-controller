<?php

namespace TomHart\Restful\Tests\Classes;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class ModelParentTest extends Model
{

    public function getRouteKey()
    {
        return 'model_parent';
    }

    public function getRouteKeyName()
    {
        return 'model_parent';
    }

    public function child()
    {
        return $this->belongsTo(ModelTest::class);
    }

}