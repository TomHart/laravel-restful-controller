<?php

namespace TomHart\Restful\Concerns;

use TomHart\Restful\Builder;

interface Transformer
{

    /**
     * @param Builder $builder
     * @return mixed[]
     */
    public function buildQueryString(Builder $builder): array;
}
