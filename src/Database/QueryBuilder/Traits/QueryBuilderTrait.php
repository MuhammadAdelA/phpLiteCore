<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder\Traits;
trait QueryBuilderTrait
{
    /**
     * Add an OR WHERE clause for column starting with given value(s).
     *
     * @param string $column Column name to filter
     * @param array|string $values One or multiple values to search
     * @return self
     */
    public function whereStarts(string $column, array|string $values): self
    {

    }

    /**
     * Add an OR WHERE clause for column containing given value(s).
     *
     * @param string $column Column name to filter
     * @param array|string $values One or multiple values to search
     * @return self
     */
    public function where_has(string $column, array|string $values): self
    {

    }

    /**
     * Add an OR WHERE clause for column ending with given value(s).
     *
     * @param string $column Column name to filter
     * @param array|string $values One or multiple values to search
     * @return self
     */
    public function where_ends(string $column, array|string $values): self
    {

    }

    /**
     * Add an OR WHERE clause for column between two values.
     *
     * @param string $column Column name to filter
     * @param string $value1 Lower bound value
     * @param string $value2 Upper bound value
     * @return self
     */
    public function where_between(string $column, string $value1, string $value2): self
    {

    }
}
