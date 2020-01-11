<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\Traits\HasLinksTrait;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class ModelTest extends Model implements HasLinks
{
    use HasLinksTrait;

    public function getRouteKey()
    {
        return 'model_test';
    }

    public function getRouteKeyName()
    {
        return 'model_test';
    }

    public function parent(): HasOne
    {
        return $this->hasOne(ModelTest::class);
    }
}
