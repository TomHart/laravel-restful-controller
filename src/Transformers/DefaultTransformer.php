<?php

namespace TomHart\Restful\Transformers;

use TomHart\Restful\Builder;
use TomHart\Restful\Concerns\Transformer;

class DefaultTransformer implements Transformer
{

    /**
     * Assemble the query string from the builder.
     * @param Builder $builder
     * @return mixed[]
     */
    public function buildQueryString(Builder $builder): array
    {
        $queryString = [];

        // Add the where clauses to the query string.
        foreach ($builder->getWheres() as $where) {
            $queryString = $this->addWhereClause($where, $queryString);
        }

        // Add the order.
        if ($builder->getOrder()) {
            $queryString[config('api-database.query_string_keys.order', 'order')] = $builder->getOrder();
        }

        // Add the limit.
        if ($builder->getLimit()) {
            $queryString[config('api-database.query_string_keys.limit', 'limit')] = $builder->getLimit();
        }

        return $queryString;
    }

    /**
     * Adds a where clause to the query string.
     * @param string[] $where
     * @param mixed[] $queryString
     * @return string[]
     */
    private function addWhereClause(array $where, array $queryString): array
    {
        switch ($where['type']) {
            default:
                $queryString[$where['column']] = $where['value'];
                break;
            case 'In':
                $queryString[$where['column']] = $where['values'];
                break;
        }

        return $queryString;
    }
}