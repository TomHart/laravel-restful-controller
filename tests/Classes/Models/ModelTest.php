<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\Concerns\Restful;
use TomHart\Restful\Traits\HasLinksTrait;
use TomHart\Restful\Traits\InteractsWithRest;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class ModelTest extends Model implements HasLinks, Restful
{
    use HasLinksTrait;
    use InteractsWithRest;

    public function parent(): HasOne
    {
        return $this->hasOne(ModelTest::class);
    }
}
