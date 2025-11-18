<?php

namespace PhpLiteCore\Database\QueryBuilder\Traits;

use PhpLiteCore\Database\QueryBuilder\BaseQueryBuilder;

/**
 * Provides convenience methods for adding LIKE-based WHERE clauses.
 */
trait QueryBuilderLikeTraits
{
    /**
     * Add a WHERE clause with a "starts with" LIKE condition.
     *
     * @param string $column The column to filter.
     * @param string|array $values The value or values that the column should start with.
     * @param string $boolean 'AND' or 'OR'
     * @return self
     */
    public function whereStarts(string $column, array|string $values, string $boolean = 'AND'): self
    {
        if (is_array($values)) {
            return $this->whereGroup(function ($query) use ($column, $values) {
                foreach ($values as $value) {
                    $query->orWhere($column, 'LIKE', $value . '%');
                }
            }, $boolean);
        }

        return $this->where($column, 'LIKE', $values . '%', $boolean);
    }

    /**
     * Add an OR WHERE clause with a "starts with" LIKE condition.
     *
     * @param string $column The column to filter.
     * @param string|array $values The value or values that the column should start with.
     * @return self
     */
    public function orWhereStarts(string $column, array|string $values): self
    {
        return $this->whereStarts($column, $values, 'OR');
    }

    /**
     * Add a WHERE clause with a "contains" LIKE condition.
     *
     * @param string $column The column to filter.
     * @param string|array $values The value or values to search within the column.
     * @param string $boolean 'AND' or 'OR'
     * @return self
     */
    public function whereHas(string $column, array|string $values, string $boolean = 'AND'): self
    {
        if (is_array($values)) {
            return $this->whereGroup(function ($query) use ($column, $values) {
                foreach ($values as $value) {
                    $query->orWhere($column, 'LIKE', '%' . $value . '%');
                }
            }, $boolean);
        }

        return $this->where($column, 'LIKE', '%' . $values . '%', $boolean);
    }

    /**
     * Add an OR WHERE clause with a "contains" LIKE condition.
     *
     * @param string $column The column to filter.
     * @param string|array $values The value or values to search within the column.
     * @return BaseQueryBuilder|QueryBuilderLikeTraits
     */
    public function orWhereHas(string $column, array|string $values): self
    {
        return $this->whereHas($column, $values, 'OR');
    }

    /**
     * Add a WHERE clause with an "ends with" LIKE condition.
     *
     * @param string $column The column to filter.
     * @param string|array $values The value or values that the column should end with.
     * @return BaseQueryBuilder|QueryBuilderLikeTraits The current query builder instance.
     */
    public function whereEnds(string $column, array|string $values): self
    {
        return $this->where($column, 'LIKE', '%' . $values);
    }

    /**
     * Add an OR WHERE clause with an "ends with" LIKE condition.
     *
     * @param string $column The column to filter.
     * @param string|array $values The value or values that the column should end with.
     * @return BaseQueryBuilder|QueryBuilderLikeTraits The current query builder instance.
     */
    public function orWhereEnds(string $column, array|string $values): self
    {
        return $this->orWhere($column, 'LIKE', '%' . $values);
    }
}
