<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder\Traits;

trait QueryBuilderWhereTraits
{
    /**
     * {@inheritDoc}
     */
    public function where(callable|string|array $column, string|array|null $operator = null, mixed $value = null, string $boolean = 'AND'): static
    {
        // Handle array of conditions: where(['status' => 'active', 'type' => 'post'])
        if (is_array($column)) {
            return $this->whereGroup(function ($query) use ($column) {
                foreach ($column as $key => $val) {
                    $query->where($key, '=', $val);
                }
            }, $boolean);
        }

        // Handle nested group via closure: where(function($query) { ... })
        if ($column instanceof \Closure) {
            return $this->whereGroup($column, $boolean);
        }

        // Handle two-argument case: where('id', 1)
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        // Handle WHERE IN: where('id', [1, 2, 3])
        if (is_array($value)) {
            return $this->whereIn($column, $value, 'In', $boolean);
        }

        // Add a basic single condition to the wheres array.
        // We only store the data, we do not touch the global bindings array.
        $this->wheres[] = [
            'type' => 'Basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value, // Store the value here
            'boolean' => $boolean,
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orWhere(callable|string|array $column, string|array|null $operator = null, mixed $value = null): static
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * {@inheritDoc}
     */
    public function whereGroup(callable $callback, string $boolean = 'AND'): static
    {
        // IMPORTANT: The constructor for BaseQueryBuilder requires all 3 arguments.
        $nested = new static($this->pdo, $this->grammar, $this->db);
        $callback($nested);

        // Only add the nested group if it actually has where clauses.
        if (! empty($nested->getWheres())) {
            $this->wheres[] = [
                'type' => 'Nested',
                'wheres' => $nested->getWheres(),
                'boolean' => $boolean,
            ];
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereGroup(callable $callback, string $boolean = 'OR'): static
    {
        return $this->whereGroup($callback, $boolean);
    }

    /**
     * {@inheritDoc}
     */
    public function whereIn(string $column, array $values, string $type = 'In', string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'values' => $values, // Store values here
            'boolean' => $boolean,
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereIn(string $column, array $values): static
    {
        return $this->whereIn($column, $values, 'In', 'OR');
    }

    /**
     * {@inheritDoc}
     */
    public function whereNotIn(string $column, array $values): static
    {
        return $this->whereIn($column, $values, 'NotIn', 'AND');
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereNotIn(string $column, array $values): static
    {
        return $this->whereIn($column, $values, 'NotIn', 'OR');
    }

    /**
     * {@inheritDoc}
     */
    public function whereBetween(string $column, mixed $start, mixed $end, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'Between',
            'column' => $column,
            'values' => [$start, $end], // Store values here
            'boolean' => $boolean,
        ];

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function orWhereBetween(string $column, mixed $start, mixed $end): static
    {
        return $this->whereBetween($column, $start, $end, 'OR');
    }
}
