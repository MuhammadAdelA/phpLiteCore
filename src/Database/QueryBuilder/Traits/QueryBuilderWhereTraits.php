<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder\Traits;

trait QueryBuilderWhereTraits
{
    /**
     * {@inheritDoc}
     */
    public function where(
        callable|string|array $column,
        string|array|null $operator = null,
        mixed $value = null,
        string $boolean = 'AND'
    ): static
    {
        // 1) Array of triples [[col, op, val], …] ⇒ group each triple
        if (is_array($column)
            && array_keys($column) === range(0, count($column) - 1)
            && !empty($column)
            && is_array($column[0])
            && count($column[0]) === 3
        ) {
            return $this->whereGroup(function($query) use ($column) {
                foreach ($column as [$col, $op, $val]) {
                    $query->where($col, $op, $val);
                }
            }, $boolean);
        }

        // 2) Nested group via closure
        if ($column instanceof \Closure) {
            return $this->whereGroup($column, $boolean);
        }

        // 3) Column is an associative array ⇒ group of Basic = for each pair
        if (is_array($column)
            && array_keys($column) !== range(0, count($column) - 1)
        ) {
            return $this->whereGroup(function($query) use ($column) {
                foreach ($column as $col => $val) {
                    $query->where($col, '=', $val);
                }
            }, $boolean);
        }

        // 4) Operator is array ⇒ either WHERE IN or associative group
        if (is_array($operator)) {
            // indexed array ⇒ WHERE IN
            if (array_keys($operator) === range(0, count($operator) - 1)) {
                return $this->whereIn($column, $operator, 'In', $boolean);
            }
            // associative array ⇒ group of Basic =
            return $this->whereGroup(function($query) use ($operator) {
                foreach ($operator as $col => $val) {
                    $query->where($col, '=', $val);
                }
            }, $boolean);
        }

        // 5) Operator is string AND value is array ⇒ group of same operators
        if (is_string($operator) && is_array($value)) {
            return $this->whereGroup(function($query) use ($column, $operator, $value) {
                foreach ($value as $val) {
                    $query->where($column, $operator, $val);
                }
            }, $boolean);
        }

        // 6) Two-argument case ⇒ default operator '='
        if ($value === null) {
            $value    = $operator;
            $operator = '=';
        }

        // 7) Basic single condition
        $this->wheres[] = [
            'type'     => 'Basic',
            'column'   => $column,
            'operator' => $operator,
            'boolean'  => $boolean,
        ];
        $this->bindings[] = $value;

        return $this;
    }


    /**
     * {@inheritDoc}
     */
    public function whereGroup(callable $callback, string $boolean = 'AND'): static
    {
        // Create a nested builder for the group
        $nested = new static($this->pdo, $this->grammar);
        $callback($nested);
        // Add a nested group with AND/OR boolean
        $this->wheres[] = [
            'type'   => 'Nested',
            'wheres' => $nested->wheres,
            'boolean'=> $boolean,
        ];
        // Merge nested bindings
        $this->bindings = array_merge($this->bindings, $nested->bindings);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function whereIn(string $column, array $values, string $type, string $boolean): static
    {
        // Add WHERE IN clause with AND boolean
        $this->wheres[] = [
            'type'    => $type,
            'column'  => $column,
            'values'  => $values,
            'boolean' => $boolean,
        ];
        // Merge bindings for all values
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function whereBetween(string $column, mixed $start, mixed $end, string $boolean = 'AND'): static
    {
        // Add a WHERE BETWEEN clause with AND boolean
        $this->wheres[] = [
            'type'    => 'Between',
            'column'  => $column,
            'values'  => [$start, $end],
            'boolean' => $boolean,
        ];
        // Append both bounds to bindings
        $this->bindings = array_merge($this->bindings, [$start, $end]);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereGroup(callable $callback, string $boolean = 'OR'): static
    {
        return static::whereGroup($callback, $boolean);
    }

    /**
     * {@inheritDoc}
     */
    public function orWhere(
        callable|string|array $column,
        string|array|null $operator = null,
        mixed $value = null,
    ): static
    {
        return static::where($column, $operator, $value, 'OR');
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereIn(string $column, array $values): static
    {
        return static::whereIn($column, $values, 'In', 'OR');
    }

    /**
     * {@inheritDoc}
     */
    public function whereNotIn(string $column, array $values): static
    {
        return static::whereIn($column, $values, 'NotIn', 'AND');
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereNotIn(string $column, array $values): static
    {
        return static::whereIn($column, $values, 'NotIn', 'OR');
    }


    /**
     * {@inheritDoc}
     */
    public function orWhereBetween(string $column, mixed $start, mixed $end): static
    {
        return static::whereBetween($column, $start, $end, 'OR');
    }

}
