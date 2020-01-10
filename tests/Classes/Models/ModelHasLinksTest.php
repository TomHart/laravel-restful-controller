<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\Tests\Classes\ModelTest;
use TomHart\Restful\Traits\HasLinksTrait;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class ModelHasLinksTest extends Model implements HasLinks
{
    use HasLinksTrait;

    public function getRouteKey()
    {
        return 'has_links_test';
    }

    public function getRouteKeyName()
    {
        return 'has_links_test';
    }

    public function without(): HasOne
    {
        return $this->hasOne(ModelWithoutLinksTest::class);
    }
}
