<?php

namespace PhpLiteCore\Database\QueryBuilder\Traits;

trait QueryBuilderGetTraits
{
    /** @var string|null The model class to hydrate results into. */
    protected ?string $modelClass = null;

    /**
     * Set the model class to be used for hydrating results.
     *
     * @param string $class The fully qualified class name of the model.
     * @return static
     */
    public function setModel(string $class): static
    {
        $this->modelClass = $class;
        return $this;
    }

    /**
     * Execute the query and return a collection of model instances or raw arrays.
     *
     * @return array
     */
    public function get(): array
    {
        $results = $this->runQuery();
        if (!$this->modelClass) { return $results; }

        $models = [];
        foreach ($results as $record) {
            $models[] = new $this->modelClass($record);
        }
        return $models;
    }

    /**
     * Get the first record matching the query as a model instance or object.
     *
     * @return object|null
     */
    public function first(): ?object
    {
        $results = (clone $this)->limit(1)->runQuery();
        if (empty($results)) { return null; }

        return $this->modelClass ? new $this->modelClass($results[0]) : (object)$results[0];
    }

    /**
     * Determine if any record exists matching the query.
     *
     * @return bool
     */
    public function exists(): bool
    {
        $clone = clone $this;
        $clone->columns = ['1'];

        $result = $clone->limit(1)->runQuery();

        return !empty($result);
    }

    /**
     * Count how many records match the query.
     * This is an alias for the count() method.
     *
     * @return int
     */
    public function found(): int
    {
        return $this->count();
    }

    /**
     * A protected helper to run the actual SQL query.
     *
     * @return array
     */
    protected function runQuery(): array
    {
        $sql = $this->toSql();
        $bindings = $this->getWhereBindings();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get only the bindings for the WHERE clauses.
     *
     * @return array
     */
    public function getWhereBindings(): array
    {
        return $this->collectBindingsFromWheres($this->wheres);
    }

    /**
     * Recursively collect bindings from a WHERE array.
     *
     * @param array $wheres
     * @return array
     */
    private function collectBindingsFromWheres(array $wheres): array
    {
        $bindings = [];
        foreach ($wheres as $where) {
            if ($where['type'] === 'Nested') {
                $bindings = array_merge($bindings, $this->collectBindingsFromWheres($where['wheres']));
            } elseif (isset($where['values'])) { // For In, Between
                $bindings = array_merge($bindings, $where['values']);
            } elseif (array_key_exists('value', $where)) { // For Basic
                $bindings[] = $where['value'];
            }
        }
        return $bindings;
    }

    // --- Getters for Grammar Access ---

    /** @return array The bindings for the SET part of an insert/update. */
    public function getBindings(): array { return $this->bindings; }

    /** @return array The selected columns. */
    public function getColumns(): array { return $this->columns; }

    /** @return string The target table name. */
    public function getTable(): string { return $this->table ?? ''; }

    /** @return string|null The table alias. */
    public function getAlias(): ?string { return $this->alias; }

    /** @return array The WHERE clauses data. */
    public function getWheres(): array { return $this->wheres; }

    /** @return array The JOIN clauses. */
    public function getJoins(): array { return $this->joins; }

    /** @return array The GROUP BY columns. */
    public function getGroups(): array { return $this->groups; }

    /** @return array The ORDER BY clauses. */
    public function getOrders(): array { return $this->orders; }

    /** @return int|null The query limit. */
    public function getLimit(): ?int { return $this->limit; }

    /** @return int|null The query offset. */
    public function getOffset(): ?int { return $this->offset; }
}