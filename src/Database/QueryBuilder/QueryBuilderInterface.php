<?php
declare(strict_types=1);

namespace PhpLiteCore\Database\QueryBuilder;

/**
 * QueryBuilderInterface defines methods for building SQL queries with a fluent API.
 */
interface QueryBuilderInterface
{
    /**
     * Add SELECT clause.
     *
     * @param string ...$columns Columns to select.
     * @return static
     */
    public function select(string ...$columns): static;

    /**
     * Add FROM clause.
     *
     * @param string      $table Table name.
     * @param string|null $alias Optional table alias.
     * @return static
     */
    public function from(string $table, ?string $alias = null): static;

    /**
     * Add INSERT clause.
     *
     * @param string $table Table name.
     * @param array  $data  Column-value pairs to insert.
     * @return static
     */
    public function insert(string $table, array $data): static;

    /**
     * Add UPDATE clause.
     *
     * @param string $table Table name.
     * @param array  $data  Column-value pairs to update.
     * @return static
     */
    public function update(string $table, array $data): static;

    /**
     * Add a DELETE clause.
     *
     * @return static
     */
    public function delete(): static;

    /**
     * Add a basic WHERE clause, a WHERE IN for an indexed array,
     * multiple Basic conditions for an associative array,
     * multiple conditions for string operator + array value,
     * associative array of column=>value pairs,
     * or nested group via closure.
     *
     * @param string|callable|array  $column   Column name, closure, or array of [col => val].
     * @param string|array|null      $operator Operator string, or array of values/pairs.
     * @param mixed|null             $value    Value to bind when the operator is string.
     * @param string                 $boolean  'AND' or 'OR'.
     * @return static
     */
    public function where(
        callable|string|array $column,
        string|array|null $operator = null,
        mixed $value = null,
        string $boolean = 'AND'
    )
    : static;

    /**
     * Add a basic WHERE clause, a WHERE IN for an indexed array,
     * multiple Basic conditions for an associative array,
     * multiple conditions for string operator + array value,
     * associative array of column=>value pairs,
     * or nested group via closure.
     *
     * @param string|callable|array  $column   Column name, closure, or array of [col => val].
     * @param string|array|null      $operator Operator string, or array of values/pairs.
     * @param mixed|null             $value    Value to bind when the operator is string.
     * @return static
     */
    public function orWhere(
        callable|string|array $column,
        string|array|null $operator = null,
        mixed $value = null,
    )
    : static;

    /**
     * Add a WHERE ... IN (...) clause with AND boolean.
     *
     * @param string $column Column name.
     * @param array $values List of values.
     * @param string $type
     * @param string $boolean
     * @return static
     */
    public function whereIn(string $column, array $values, string $type, string $boolean): static;

    /**
     * Add a WHERE ... IN (...) clause with OR boolean.
     *
     * @param string $column Column name.
     * @param array  $values List of values.
     * @return static
     */
    public function orWhereIn(string $column, array $values): static;

    /**
     * Add a WHERE ... NOT IN (...) clause with AND boolean.
     *
     * @param string $column Column name.
     * @param array  $values List of values.
     * @return static
     */
    public function whereNotIn(string $column, array $values): static;

    /**
     * Add a WHERE ... NOT IN (...) clause with OR boolean.
     *
     * @param string $column Column name.
     * @param array  $values List of values.
     * @return static
     */
    public function orWhereNotIn(string $column, array $values): static;

    /**
     * Add a WHERE ... BETWEEN ... AND ... clause with AND boolean.
     *
     * @param string $column Column name.
     * @param mixed $start Lower bound.
     * @param mixed $end Upper bound.
     * @param string $boolean
     * @return static
     */
    public function whereBetween(string $column, mixed $start, mixed $end, string $boolean): static;

    /**
     * Add a WHERE ... BETWEEN ... AND ... clause with OR boolean.
     *
     * @param string $column Column name.
     * @param mixed  $start  Lower bound.
     * @param mixed  $end    Upper bound.
     * @return static
     */
    public function orWhereBetween(string $column, mixed $start, mixed $end): static;

    /**
     * Add a nested WHERE group.
     *
     * @param callable $callback Callback that receives a new query builder instance.
     * @param string   $boolean  Boolean connector for the group, 'AND' or 'OR'.
     * @return static
     */
    public function whereGroup(callable $callback, string $boolean): static;

    /**
     * Add a nested WHERE group.
     *
     * @param callable $callback Callback that receives a new query builder instance.
     * @param string   $boolean  Boolean connector for the group, 'AND' or 'OR'.
     * @return static
     */
    public function orWhereGroup(callable $callback, string $boolean): static;

    /**
     * Add a GROUP BY clause.
     *
     * @param string ...$columns Columns to group by.
     * @return static
     */
    public function groupBy(string ...$columns): static;

    /**
     * Add ORDER BY clause.
     *
     * @param string $column    Column to order by.
     * @param string $direction Direction (ASC or DESC).
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC'): static;

    /**
     * Set the LIMIT clause.
     *
     * @param int $limit Number of records to limit.
     * @return static
     */
    public function limit(int $limit): static;

    /**
     * Set the OFFSET clause.
     *
     * @param int $offset Number of records to skip.
     * @return static
     */
    public function offset(int $offset): static;

    /**
     * Get the compiled SQL query.
     *
     * @return string
     */
    public function toSql(): string;

    /**
     * Get the bindings for the prepared statement.
     *
     * @return array
     */
    public function getBindings(): array;
}
