<?php

namespace TomHart\Restful\Tests\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function child(): BelongsTo
    {
        return $this->belongsTo(ModelTest::class);
    }

}