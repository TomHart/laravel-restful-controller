<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\Traits\HasLinksTrait;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class Post extends Model implements HasLinks
{
    use HasLinksTrait;



    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
