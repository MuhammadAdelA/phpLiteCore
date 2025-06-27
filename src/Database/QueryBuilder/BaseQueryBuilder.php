<?php

declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

use PhpLiteCore\Database\Grammar\GrammarInterface;

/**
 * BaseQueryBuilder implements the core functionality for building SQL queries,
 * including nested where groups.
 */
class BaseQueryBuilder implements QueryBuilderInterface
{
    protected GrammarInterface $grammar;
    protected \PDO $connection;

    protected array $bindings    = [];
    protected array $columns     = ['*'];
    protected string $table;
    protected ?string $alias      = null;
    protected array $wheres      = [];
    protected array $joins       = [];
    protected array $groups      = [];
    protected array $orders      = [];
    protected ?int $limit        = null;
    protected ?int $offset       = null;
    protected array $insertData  = [];
    protected array $updateData  = [];
    protected string $type;
    protected ?int $rowCount = null;

    public function __construct(\PDO $connection, GrammarInterface $grammar)
    {
        $this->connection = $connection;
        $this->grammar    = $grammar;
    }

    public function select(string ...$columns): static
    {
        $this->type    = 'select';
        $this->columns = $columns ?: ['*'];
        return $this;
    }

    public function from(string $table, ?string $alias = null): static
    {
        $this->table = $table;
        $this->alias = $alias;
        return $this;
    }

    public function insert(string $table, array $data): static
    {
        $this->type       = 'insert';
        $this->table      = $table;
        $this->insertData = $data;
        $this->bindings   = array_values($data);
        return $this;
    }

    public function update(string $table, array $data): static
    {
        $this->type       = 'update';
        $this->table      = $table;
        $this->updateData = $data;
        $this->bindings   = array_values($data);
        return $this;
    }

    public function delete(): static
    {
        $this->type = 'delete';
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): static
    {
        $this->wheres[]   = [
            'type'     => 'Basic',
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
            'boolean'  => 'AND'
        ];
        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): static
    {
        $this->wheres[]   = [
            'type'     => 'Basic',
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
            'boolean'  => 'OR'
        ];
        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Adds a nested where group with AND boolean.
     */
    public function whereGroup(callable $callback): static
    {
        return $this->addNestedGroup($callback, 'AND');
    }

    /**
     * Adds a nested where group with OR boolean.
     */
    public function orWhereGroup(callable $callback): static
    {
        return $this->addNestedGroup($callback, 'OR');
    }

    /**
     * Internal helper to add nested groups.
     */
    protected function addNestedGroup(callable $callback, string $boolean): static
    {
        $nested = new static($this->connection, $this->grammar);
        $callback($nested);

        $this->wheres[]   = [
            'type'    => 'Nested',
            'query'   => $nested,
            'boolean' => $boolean
        ];
        $this->bindings   = array_merge($this->bindings, $nested->getBindings());

        return $this;
    }

    public function whereIn(string $column, array $values): static
    {
        $this->wheres[]    = [
            'type'     => 'In',
            'column'   => $column,
            'values'   => $values,
            'boolean'  => 'AND'
        ];
        $this->bindings    = array_merge($this->bindings, array_values($values));
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): static
    {
        $this->joins[] = compact('type', 'table', 'first', 'operator', 'second');
        return $this;
    }

    public function groupBy(string ...$columns): static
    {
        $this->groups = $columns;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    public function toSql(): string
    {
        return match ($this->type) {
            'select' => $this->grammar->compileSelect($this),
            'insert' => $this->grammar->compileInsert($this),
            'update' => $this->grammar->compileUpdate($this),
            'delete' => $this->grammar->compileDelete($this),
            default  => throw new \LogicException('Unknown query type: ' . $this->type),
        };
    }

    public function getBindings(): array
    {
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
    public function getInsertData(): array  { return $this->insertData; }
    public function getUpdateData(): array  { return $this->updateData; }
}