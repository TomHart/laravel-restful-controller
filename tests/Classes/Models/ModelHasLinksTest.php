<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\Traits\HasLinksTrait;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class ModelHasLinksTest extends Model implements HasLinks
{
    use HasLinksTrait;

    public function without(): HasOne
    {
        return $this->hasOne(ModelWithoutLinksTest::class);
    }
}
