<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
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

    /**
     * Return the name for the resource route this model
     * @return string|null
     */
    public function getRouteName(): ?string
    {
        return Str::kebab(Str::studly($this->getRouteKey()));
    }
}
