<?php

namespace PhpLiteCore\Database\QueryBuilder\Traits;

trait QueryBuilderGetTraits
{
    /**
     * {@inheritDoc}
     */
    public function get(): array
    {
        $sql      = $this->toSql();
        $bindings = $this->getBindings();

        $stmt = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            // Numeric keys are 1-based positional parameters
            $param = is_int($key) ? $key + 1 : $key;
            $stmt->bindValue($param, $value);
        }

        $stmt->execute();

        // Fetch all results
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Store row count
        $this->rowCount = count($results);

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function first(): ?array
    {
        // Clone builder so the original state stays intact
        $clone = clone $this;
        $clone->limit(1);

        $results = $clone->get();

        // Return the first element or null
        return $results[0] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(): bool
    {
        // Clone builder to apply limit without side effects
        $clone = clone $this;
        $clone->limit(1);

        $clone->get();

        // If rowCount > 0, at least one record exists
        return $clone->rowCount > 0;
    }

    /**
     * Count how many records match the query.
     *
     * @return int
     */
    public function found(): int
    {
        // Clone builder so that the original SELECT and limit remain unchanged
        $clone = clone $this;

        $clone->get();

        return $clone->rowCount;
    }

    /**
     * {@inheritDoc}
     */
    public function getBindings(): array
    {
        // Return accumulated bindings
        return $this->bindings;
    }
    // Getter methods for Grammar access
    public function getColumns(): array      { return $this->columns; }
    public function getTable(): string      { return $this->table; }
    public function getAlias(): ?string     { return $this->alias; }
    public function getWheres(): array      { return $this->wheres; }
    public function getJoins(): array       { return $this->joins; }
    public function getGroups(): array      { return $this->groups; }
    public function getOrders(): array      { return $this->orders; }
    public function getLimit(): ?int        { return $this->limit; }
    public function getOffset(): ?int       { return $this->offset; }
}