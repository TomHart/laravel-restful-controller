<?php

namespace TomHart\Restful\Tests\Classes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use TomHart\Restful\Concerns\HasLinks;
use TomHart\Restful\Traits\HasLinksTrait;

/**
 * @property int $id
 * @property string $name
 * @method find(int $id)
 */
class ModelParentTest extends Model implements HasLinks
{
    use HasLinksTrait;

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(ModelTest::class);
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(ModelTest::class);
    }
}
